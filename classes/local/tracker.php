<?php
namespace mod_videotrack\local;

defined('MOODLE_INTERNAL') || die();

class tracker {
    /** Numero massimo di intervalli merged conservati in intervaljson.
     *  Impedisce la crescita illimitata del campo per utenti che guardano
     *  molti frammenti brevi e non sovrapposti. */
    const MAX_INTERVALS = 500;

    /**
     * Grace window in seconds for the OR branch of has_recent_playback().
     * Covers high-latency environments where the segment end timestamp may
     * lag actual playback end by up to this many seconds. Must be >= the
     * default $timetolerance parameter (8.0) to be meaningful.
     */
    const PLAYBACK_GRACE_SECONDS = 12.0;

    /** @var array Per-request cache for reaction_counts(). Keyed by "videotrackid:userid". */
    private static $reaction_counts_cache = [];


    /**
     * Returns the current persisted state, or a safe in-memory default when no
     * state row exists yet. Used as a non-fatal fallback when the per-user state
     * lock is temporarily contended.
     *
     * @param stdClass $videotrack Activity instance.
     * @param cm_info $cm Course module info.
     * @param int $userid User id.
     * @return stdClass
     */
    private static function current_state_snapshot(\stdClass $videotrack, \cm_info $cm, int $userid): \stdClass {
        global $DB;

        $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $userid]);
        if ($state) {
            return $state;
        }

        return self::create_default_state($videotrack, $cm, $userid);
    }

    public static function normalise_interval(float $start, float $end, float $duration = 0.0): ?array {
        if ($duration > 0) {
            $start = max(0.0, min($start, $duration));
            $end = max(0.0, min($end, $duration));
        } else {
            $start = max(0.0, $start);
            $end = max(0.0, $end);
        }
        if ($end <= $start) {
            return null;
        }
        return [round($start, 3), round($end, 3)];
    }

    public static function decode_intervals(?string $json): array {
        if (empty($json)) {
            return [];
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            return [];
        }

        $intervals = [];
        foreach ($data as $interval) {
            if (!is_array($interval) || count($interval) < 2 || !is_numeric($interval[0]) || !is_numeric($interval[1])) {
                continue;
            }
            $start = (float)$interval[0];
            $end = (float)$interval[1];
            if (!is_finite($start) || !is_finite($end)) {
                continue;
            }
            $normalised = self::normalise_interval($start, $end);
            if ($normalised !== null) {
                $intervals[] = $normalised;
            }
        }
        return $intervals;
    }

    public static function encode_intervals(array $intervals): string {
        return json_encode(array_values($intervals));
    }

    public static function merge_intervals(array $intervals): array {
        if (!$intervals) {
            return [];
        }
        usort($intervals, static function($a, $b) {
            return $a[0] <=> $b[0];
        });
        $merged = [];
        foreach ($intervals as $interval) {
            if (!$merged || $interval[0] > $merged[count($merged) - 1][1]) {
                $merged[] = $interval;
                continue;
            }
            $merged[count($merged) - 1][1] = max($merged[count($merged) - 1][1], $interval[1]);
        }
        return $merged;
    }

    /**
     * Se il numero di intervalli supera MAX_INTERVALS, semplifica conservando
     * gli intervalli piu' lunghi e scartando frammenti minori. Questo evita di
     * inventare copertura guardata fondendo gap non visti, al costo di una
     * perdita controllata di precisione nei casi estremi.
     */
    public static function cap_intervals(array $intervals): array {
        if (count($intervals) <= self::MAX_INTERVALS) {
            return $intervals;
        }
        return self::simplify_intervals($intervals, self::MAX_INTERVALS);
    }

    /**
     * Riduce l'array di intervalli al target count senza mai fondere gap non visti.
     *
     * Il problema del metodo precedente: univa le coppie con il gap minore,
     * il che significa che il gap (parte NON vista) veniva inglobato nell'intervallo
     * risultante, gonfiando artificialmente uniquecoveredseconds.
     *
     * Soluzione corretta: invece di unire gli intervalli (che aggiunge copertura falsa),
     * TRONCHIAMO l'array mantenendo i primi $target intervalli ordinati per lunghezza
     * decrescente. I frammenti brevi e marginali vengono scartati, ma la copertura
     * totale non viene mai sovrastimata.
     *
     * Nota: questa operazione comporta una perdita di precisione (piccoli frammenti
     * vengono ignorati), ma è semanticamente corretta: non inventa copertura.
     * La perdita è limitata: cap_intervals viene chiamata solo quando ci sono >500
     * intervalli distinti (scenario raro e solo con seek molto frequenti).
     *
     * @param array $intervals  Array di [start, end] già merged.
     * @param int   $target     Numero massimo di intervalli da mantenere.
     * @return array
     */
    public static function simplify_intervals(array $intervals, int $target): array {
        $n = count($intervals);
        if ($n <= $target) {
            return $intervals;
        }
        // Ordina per lunghezza decrescente e tieni i $target più lunghi.
        usort($intervals, function($a, $b) {
            return ($b[1] - $b[0]) <=> ($a[1] - $a[0]);
        });
        $kept = array_slice($intervals, 0, $target);
        // Ri-ordina per posizione temporale per coerenza.
        usort($kept, function($a, $b) { return $a[0] <=> $b[0]; });
        return $kept;
    }

    public static function covered_seconds(array $intervals): float {
        $total = 0.0;
        foreach ($intervals as $interval) {
            $total += max(0.0, $interval[1] - $interval[0]);
        }
        return round($total, 3);
    }

    public static function reaction_counts(int $videotrackid, int $userid): array {
        global $DB;
        // O1: per-request cache to avoid repeated identical DB queries within the same
        // HTTP request (e.g. save_reaction calls this once, then refresh_completion
        // calls it again internally). Invalidated via invalidate_reaction_counts_cache()
        // after any insert/delete on videotrack_reactev.
        // Uses a static class property (not a method-local static) so that
        // invalidate_reaction_counts_cache() can reliably clear the same variable.
        $key = $videotrackid . ':' . $userid;
        if (isset(self::$reaction_counts_cache[$key])) {
            return self::$reaction_counts_cache[$key];
        }
        $p = ['vtid' => $videotrackid, 'uid' => $userid];
        $where = "videotrackid = :vtid AND userid = :uid AND isdeleted = 0
                  AND reactionid > 0 AND (notetype = '' OR notetype IS NULL)";
        // Due query separate per evitare GROUP_CONCAT (troncato a 1024 chars su MySQL).
        $row = $DB->get_record_sql(
            "SELECT COUNT(*) AS eventcount, COUNT(DISTINCT reactionid) AS uniquecount
               FROM {videotrack_reactev} WHERE $where", $p);
        $ids = $DB->get_fieldset_sql(
            "SELECT DISTINCT reactionid FROM {videotrack_reactev} WHERE $where ORDER BY reactionid", $p);
        $result = [
            'eventcount'  => (int)($row->eventcount  ?? 0),
            'uniquecount' => (int)($row->uniquecount  ?? 0),
            'uniqueids'   => array_map('intval', $ids),
        ];
        self::$reaction_counts_cache[$key] = $result;
        return $result;
    }

    /**
     * Invalidates the per-request cache for reaction_counts.
     * Must be called after any insert or soft-delete on videotrack_reactev
     * to ensure subsequent calls within the same request see fresh data.
     *
     * @param int $videotrackid
     * @param int $userid
     */
    public static function invalidate_reaction_counts_cache(int $videotrackid, int $userid): void {
        $key = $videotrackid . ':' . $userid;
        unset(self::$reaction_counts_cache[$key]);
    }


    /**
     * Returns true when a reaction or note is backed by a recent playback heartbeat.
     *
     * The browser UI hides these controls outside PLAYING, but this server-side check
     * prevents direct AJAX calls from creating reactions/notes at arbitrary timestamps.
     */
    public static function has_recent_playback(int $videotrackid, int $userid, string $sessionid,
            float $videotime, int $recentseconds = 20, float $timetolerance = 8.0): bool {
        global $DB;
        // I parametri vtstart/vtend sono lo stesso valore ($videotime), e tolstart/tolend lo stesso.
        // Usare placeholder distinti evita problemi con driver adodb che non ammettono
        // lo stesso named param più di una volta nella stessa query.
        $vt  = max(0.0, $videotime);
        $tol = max(1.0, $timetolerance);
        $since = time() - max(5, $recentseconds);
        // S1 fix: replace the magic number 12.0 with a named constant.
        // This grace window covers high-latency environments where the segment
        // end timestamp may lag the actual end of playback by up to 12 seconds
        // (e.g. slow mobile connections or deferred heartbeat delivery).
        // The value is intentionally larger than $timetolerance to accept
        // reactions/notes triggered just after a segment has nominally ended.
        $graceseconds = max($tol, self::PLAYBACK_GRACE_SECONDS);
        $params = [
            'vtid'  => $videotrackid,
            'uid'   => $userid,
            'sid'   => $sessionid,
            'since' => $since,
            'vt'    => $vt,
            'vt2'   => $vt,
            'tol1'  => $tol,
            'tol2'  => $tol,
            'tolend' => $graceseconds,
        ];

        // A single query covers both the strict interval match and the grace
        // period used for clicks immediately after PLAYING/seek in high-latency
        // environments. This avoids duplicate DB checks for each reaction/note.
        $select = 'videotrackid = :vtid AND userid = :uid AND sessionid = :sid AND timecreated >= :since
             AND ((:vt >= (videotimestart - :tol1) AND :vt2 <= (videotimeend + :tol2))
                  OR ABS(videotimeend - :vt3) <= :tolend)';
        if ($DB->record_exists_select('videotrack_seg', $select, $params + ['vt3' => $vt])) {
            return true;
        }

        if ((int)get_config('mod_videotrack', 'strictsessionvalidation')) {
            return false;
        }

        // UX-friendly fallback: after refreshes, browser changes or a longer pause,
        // accept playback for the same user/activity even when the browser session id
        // changed or the heartbeat is no longer recent. The timestamp must still be
        // inside a watched interval, so direct calls cannot create notes/reactions on
        // unwatched positions.
        if ($DB->record_exists_select('videotrack_seg',
                'videotrackid = :vtid AND userid = :uid AND timecreated >= :since
                 AND ((:vt >= (videotimestart - :tol1) AND :vt2 <= (videotimeend + :tol2))
                      OR ABS(videotimeend - :vt3) <= :tolend)',
                [
                    'vtid' => $videotrackid,
                    'uid' => $userid,
                    'since' => $since,
                    'vt' => $vt,
                    'vt2' => $vt,
                    'vt3' => $vt,
                    'tol1' => $tol,
                    'tol2' => $tol,
                    'tolend' => $graceseconds,
                ])) {
            return true;
        }

        // Global helper from locallib.php; the leading backslash selects the global namespace.
        $fallbackdays = \videotrack_get_config_int('validationfallbackdays', 30, 0, 3650);
        $maxage = $fallbackdays > 0 ? $fallbackdays * DAYSECS : 0;

        return self::has_watched_videotime($videotrackid, $userid, $sessionid, $videotime, 2.0, $maxage);
    }

    /**
     * Returns true when the requested video time is inside a watched segment.
     *
     * This check is used for notes and reactions: the target timestamp must fall
     * inside a recorded watched segment. By default it accepts previous browser
     * sessions for the same user/activity to avoid false negatives after refreshes;
     * administrators can enable strict same-session validation in plugin settings.
     *
     * @param int $videotrackid Activity id.
     * @param int $userid User id.
     * @param string $sessionid Browser session id.
     * @param float $videotime Video timestamp in seconds.
     * @param float $timetolerance Small tolerance for heartbeat/network delay.
     * @return bool
     */
    public static function has_watched_videotime(int $videotrackid, int $userid, string $sessionid,
            float $videotime, float $timetolerance = 2.0, int $maxageseconds = 0): bool {
        global $DB;

        $vt = max(0.0, $videotime);
        $tol = max(0.5, $timetolerance);
        $params = [
            'vtid' => $videotrackid,
            'uid' => $userid,
            'sid' => $sessionid,
            'vt1' => $vt,
            'vt2' => $vt,
            'tol1' => $tol,
            'tol2' => $tol,
        ];

        $samesessionselect = 'videotrackid = :vtid AND userid = :uid AND sessionid = :sid
             AND :vt1 >= (videotimestart - :tol1)
             AND :vt2 <= (videotimeend + :tol2)';
        if ($DB->record_exists_select('videotrack_seg', $samesessionselect, $params)) {
            return true;
        }

        if ((int)get_config('mod_videotrack', 'strictsessionvalidation')) {
            return false;
        }

        // UX-friendly fallback: after refreshes or browser changes, allow notes and
        // reactions for timestamps already watched by the same user in this activity.
        // This still rejects unwatched positions because the timestamp must fall
        // inside a recorded segment. A configurable age limit prevents very old
        // playback from authorising new interactions indefinitely.
        $fallbackselect = 'videotrackid = :vtid AND userid = :uid
             AND :vt1 >= (videotimestart - :tol1)
             AND :vt2 <= (videotimeend + :tol2)';
        $fallbackparams = [
            'vtid' => $videotrackid,
            'uid' => $userid,
            'vt1' => $vt,
            'vt2' => $vt,
            'tol1' => $tol,
            'tol2' => $tol,
        ];
        if ($maxageseconds > 0) {
            $fallbackselect .= ' AND timecreated >= :fallbacksince';
            $fallbackparams['fallbacksince'] = time() - $maxageseconds;
        }

        return $DB->record_exists_select('videotrack_seg', $fallbackselect, $fallbackparams);
    }

    public static function completion_satisfied(\stdClass $videotrack, ?\stdClass $state, array $reactionsummary, array $requiredreactionids): bool {
        $checks = [];
        if (!empty($videotrack->completionpercent)) {
            $checks[] = !empty($state) && (float)$state->completionpercent >= (float)$videotrack->completionpercent;
        }
        if (!empty($videotrack->reactionsrequired) && !empty($videotrack->minreactions)) {
            $checks[] = $reactionsummary['uniquecount'] >= (int)$videotrack->minreactions;
        }
        foreach ($requiredreactionids as $reactionid) {
            $checks[] = in_array((int)$reactionid, $reactionsummary['uniqueids'], true);
        }
        if (!empty($videotrack->requireallreactiontypes)) {
            global $DB;
            $allreactionids = array_map('intval', array_keys((array)$DB->get_records_menu('videotrack_react', [
                'videotrackid' => $videotrack->id,
                'isdeleted' => 0,
            ], '', 'id,id')));
            if ($allreactionids) {
                $checks[] = count(array_intersect($allreactionids, array_map('intval', $reactionsummary['uniqueids']))) === count($allreactionids);
            }
        }
        if (!$checks) {
            // Returning false here is intentional: when no custom completion
            // rules are enabled, Moodle falls back to FEATURE_COMPLETION_TRACKS_VIEWS
            // and treats the page visit as the completion condition at framework level.
            return false;
        }
        $logic = $videotrack->completionlogic ?? 'and';
        if ($logic === 'or') {
            return in_array(true, $checks, true);
        }
        foreach ($checks as $check) {
            if (!$check) {
                return false;
            }
        }
        return true;
    }

    /**
     * Aggiorna lo stato aggregato di visione per un utente.
     * Inserisce il segmento grezzo e aggiorna videotrack_state in un'unica
     * transazione atomica: se qualcosa fallisce, nessun segmento orfano resta nel DB.
     *
     * @param stdClass  $videotrack   Istanza attività.
     * @param cm_info   $cm           Course module.
     * @param int       $userid       ID utente.
     * @param array     $interval     [start, end] normalizzato.
     * @param float     $lastposition Posizione per il resume.
     * @param stdClass|null $segment  Record segmento da inserire (null = nessun segmento).
     * @param int|null  &$segmentid   Viene impostato all'ID del segmento inserito.
     * @return stdClass               Stato aggiornato.
     */
    /**
     * Creates the default aggregate state record for a user/activity pair.
     *
     * @param \stdClass $videotrack Activity instance.
     * @param \cm_info $cm Course module info.
     * @param int $userid User id.
     * @return \stdClass Unsaved default state record.
     */
    private static function create_default_state(\stdClass $videotrack, \cm_info $cm, int $userid): \stdClass {
        return (object)[
            'videotrackid'         => $videotrack->id,
            'courseid'             => $videotrack->course,
            'cmid'                 => $cm->id,
            'userid'               => $userid,
            'videoid'              => $videotrack->videoid,
            'lastposition'         => 0,
            'durationseconds'      => (float)($videotrack->durationseconds ?? 0),
            'uniquecoveredseconds' => 0,
            'completionpercent'    => 0,
            'intervaljson'         => '[]',
            'iscompleted'          => 0,
            'timemodified'         => time(),
            'timecreated'          => time(),
        ];
    }

    public static function update_state(\stdClass $videotrack, \cm_info $cm, int $userid,
            array $interval, float $lastposition, ?\stdClass $segment = null, ?int &$segmentid = null): \stdClass {
        global $DB;

        // Serializza gli aggiornamenti dello stesso stato utente/attività. La
        // transazione protegge l'atomicità, ma il lock evita insert concorrenti
        // sul record unico videotrack_state quando arrivano heartbeat e pagehide
        // quasi simultanei.
        $lockfactory = \core\lock\lock_config::get_lock_factory('mod_videotrack');
        $lockkey = 'state:' . $videotrack->id . ':' . $userid;
        $lock = $lockfactory->get_lock($lockkey, 10);
        if (!$lock) {
            // Under very high concurrency another request is already updating
            // the same aggregate row. Avoid surfacing a lock timeout to the
            // student; return the last committed state and let the next
            // heartbeat/pagehide retry the write.
            if ($segment !== null) {
                $segmentid = 0;
            }
            return self::current_state_snapshot($videotrack, $cm, $userid);
        }

        // Transazione per serializzare scritture concorrenti (es. heartbeat + pagehide simultanei).
        $transaction = $DB->start_delegated_transaction();
        $state = null;
        try {
            // Inserisce il segmento grezzo DENTRO la transazione: atomico con update_state.
            if ($segment !== null) {
                $segmentid = $DB->insert_record('videotrack_seg', $segment);
            }
            $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $userid]);
            if (!$state) {
                $state = self::create_default_state($videotrack, $cm, $userid);
            }
            $intervals = self::decode_intervals($state->intervaljson);
            $intervals[] = $interval;
            $intervals = self::merge_intervals($intervals);
            $intervals = self::cap_intervals($intervals);
            $covered  = self::covered_seconds($intervals);
            $duration = max((float)$videotrack->durationseconds, (float)$state->durationseconds);
            $percent  = $duration > 0 ? min(100.0, round(($covered / $duration) * 100, 2)) : 0.0;

            $requiredreactionids = array_keys(array_filter((array)$DB->get_records_menu('videotrack_react', [
                'videotrackid'          => $videotrack->id,
                'requiredforcompletion' => 1,
                'isdeleted'             => 0,
            ], '', 'id,id')));
            $reactionsummary = self::reaction_counts($videotrack->id, $userid);

            // lastposition: posizione di fine del segmento corrente (per il resume automatico).
            // Usa il valore corrente se il nuovo è maggiore di 2s (evita resume da posizioni irrisorie).
            // Non usa max() storico: si vuole dove l'utente ha SMESSO di guardare, non il massimo raggiunto.
            if ($lastposition > 2.0) {
                $state->lastposition = $lastposition;
            }
            $state->durationseconds      = $duration;
            $state->uniquecoveredseconds = $covered;
            $state->completionpercent    = $percent;
            $state->intervaljson         = self::encode_intervals($intervals);
            $wasCompleted = !empty($state->id) ? (int)($state->iscompleted ?? 0) : 0;
            $state->iscompleted  = self::completion_satisfied($videotrack, $state, $reactionsummary, $requiredreactionids) ? 1 : 0;
            $state->timemodified = time();

            if (!empty($state->id)) {
                $DB->update_record('videotrack_state', $state);
            } else {
                $state->id = $DB->insert_record('videotrack_state', $state);
            }
            $transaction->allow_commit();
            $lock->release();
            $lock = null;

            // Emette activity_completed al primo passaggio 0→1.
            // Fuori dalla transazione: l'evento non è un dato critico.
            if (!$wasCompleted && $state->iscompleted) {
                $completedEvent = \mod_videotrack\event\activity_completed::create([
                    'objectid' => $state->id,
                    'context'  => \context_module::instance($cm->id),
                    'userid'   => $userid,
                    'other'    => [
                        'completionpercent'    => $state->completionpercent,
                        'uniquecoveredseconds' => $state->uniquecoveredseconds,
                    ],
                ]);
                $completedEvent->trigger();
            }
        } catch (\Throwable $e) {
            if ($lock) {
                $lock->release();
            }
            $transaction->rollback($e);
            // rollback() rilancia già l'eccezione in Moodle, ma rilanciamo
            // esplicitamente per garantire che il chiamante non riceva $state=null
            // silenziosamente in versioni future del framework.
            throw $e;
        }
        return $state;
    }

    public static function refresh_completion(\stdClass $videotrack, \cm_info $cm, int $userid): \stdClass {
        global $DB;

        // Usa lo stesso lock di update_state(): refresh_completion può essere chiamato
        // da endpoint diversi da save_segment e deve quindi evitare insert concorrenti
        // sul record unico videotrack_state.
        $lockfactory = \core\lock\lock_config::get_lock_factory('mod_videotrack');
        $lockkey = 'state:' . $videotrack->id . ':' . $userid;
        $lock = $lockfactory->get_lock($lockkey, 10);
        if (!$lock) {
            // Non-fatal fallback: completion will be refreshed by the next
            // successful state/reaction update. This prevents transient lock
            // contention from becoming a visible AJAX error for students.
            return self::current_state_snapshot($videotrack, $cm, $userid);
        }

        $transaction = $DB->start_delegated_transaction();
        try {
            $state = $DB->get_record('videotrack_state', ['videotrackid' => $videotrack->id, 'userid' => $userid]);
            if (!$state) {
                $state = self::create_default_state($videotrack, $cm, $userid);
                $state->id = $DB->insert_record('videotrack_state', $state);
            }

            $requiredreactionids = array_keys(array_filter((array)$DB->get_records_menu('videotrack_react', [
                'videotrackid' => $videotrack->id,
                'requiredforcompletion' => 1,
                'isdeleted' => 0,
            ], '', 'id,id')));
            $reactionsummary = self::reaction_counts($videotrack->id, $userid);
            $state->iscompleted = self::completion_satisfied($videotrack, $state, $reactionsummary, $requiredreactionids) ? 1 : 0;
            $state->timemodified = time();
            $DB->update_record('videotrack_state', $state);

            $transaction->allow_commit();
            $lock->release();
            $lock = null;
            return $state;
        } catch (\Throwable $e) {
            if ($lock) {
                $lock->release();
            }
            $transaction->rollback($e);
            throw $e;
        }
    }
}
