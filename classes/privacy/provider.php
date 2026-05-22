<?php
namespace mod_videotrack\privacy;

defined('MOODLE_INTERNAL') || die();

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use mod_videotrack\local\privacy_manager;

/**
 * Privacy provider for video tracking, reactions and personal notes.
 *
 * @package    mod_videotrack
 * @copyright  2026
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('videotrack_seg', [
            'userid' => 'privacy:metadata:videotrack_seg:userid',
            'sessionid' => 'privacy:metadata:videotrack_seg:sessionid',
            'wallclockstart' => 'privacy:metadata:videotrack_seg:wallclockstart',
            'wallclockend' => 'privacy:metadata:videotrack_seg:wallclockend',
            'videotimestart' => 'privacy:metadata:videotrack_seg:videotimestart',
            'videotimeend' => 'privacy:metadata:videotrack_seg:videotimeend',
            'playbackrate' => 'privacy:metadata:videotrack_seg:playbackrate',
            'endreason' => 'privacy:metadata:videotrack_seg:endreason',
            'timecreated' => 'privacy:metadata:common:timecreated',
        ], 'privacy:metadata:videotrack_seg');

        $collection->add_database_table('videotrack_state', [
            'userid' => 'privacy:metadata:videotrack_state:userid',
            'lastposition' => 'privacy:metadata:videotrack_state:lastposition',
            'durationseconds' => 'privacy:metadata:videotrack_state:durationseconds',
            'uniquecoveredseconds' => 'privacy:metadata:videotrack_state:uniquecoveredseconds',
            'completionpercent' => 'privacy:metadata:videotrack_state:completionpercent',
            'intervaljson' => 'privacy:metadata:videotrack_state:intervaljson',
            'iscompleted' => 'privacy:metadata:videotrack_state:iscompleted',
            'timemodified' => 'privacy:metadata:common:timemodified',
            'timecreated' => 'privacy:metadata:common:timecreated',
        ], 'privacy:metadata:videotrack_state');

        $collection->add_database_table('videotrack_reactev', [
            'userid'        => 'privacy:metadata:videotrack_reactev:userid',
            'sessionid'     => 'privacy:metadata:videotrack_reactev:sessionid',
            'reactionkey'   => 'privacy:metadata:videotrack_reactev:reactionkey',
            'reactionlabel' => 'privacy:metadata:videotrack_reactev:reactionlabel',
            'reactiondesc'  => 'privacy:metadata:videotrack_reactev:reactiondesc',
            'notetext'      => 'privacy:metadata:videotrack_reactev:notetext',
            'notetype'      => 'privacy:metadata:videotrack_reactev:notetype',
            'videotime'     => 'privacy:metadata:videotrack_reactev:videotime',
            'playbackrate'  => 'privacy:metadata:videotrack_reactev:playbackrate',
            'isdeleted'     => 'privacy:metadata:videotrack_reactev:isdeleted',
            'timemodified'  => 'privacy:metadata:common:timemodified',
            'timecreated'   => 'privacy:metadata:common:timecreated',
        ], 'privacy:metadata:videotrack_reactev');

        $collection->add_external_location_link('youtube', [
            'videoid' => 'privacy:metadata:youtube:videoid',
            'url' => 'privacy:metadata:youtube:url',
        ], 'privacy:metadata:youtube');

        $collection->add_external_location_link('vimeo', [
            'videoid' => 'privacy:metadata:vimeo:videoid',
            'url' => 'privacy:metadata:vimeo:url',
        ], 'privacy:metadata:vimeo');

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        // Use a UNION of module ids instead of joining all tracking tables at once.
        // This gives database optimisers a simpler plan on large installations and
        // mirrors get_users_in_context().
        $params = [
            'contextmodule' => CONTEXT_MODULE,
            'userid1' => $userid,
            'userid2' => $userid,
            'userid3' => $userid,
        ];
        $sql = "SELECT DISTINCT c.id
                  FROM {context} c
                  JOIN (
                        SELECT cmid FROM {videotrack_state} WHERE userid = :userid1
                        UNION
                        SELECT cmid FROM {videotrack_seg} WHERE userid = :userid2
                        UNION
                        SELECT cmid FROM {videotrack_reactev} WHERE userid = :userid3
                       ) tracked ON tracked.cmid = c.instanceid
                 WHERE c.contextlevel = :contextmodule";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $params = ['cmid1' => $context->instanceid, 'cmid2' => $context->instanceid, 'cmid3' => $context->instanceid];
        $sql = "SELECT userid
                  FROM (
                        SELECT userid FROM {videotrack_state} WHERE cmid = :cmid1 AND userid > 0
                        UNION
                        SELECT userid FROM {videotrack_seg} WHERE cmid = :cmid2 AND userid > 0
                        UNION
                        SELECT userid FROM {videotrack_reactev} WHERE cmid = :cmid3 AND userid > 0
                       ) u";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $writer = writer::with_context($context);

            $state = $DB->get_record('videotrack_state', [
                'cmid'   => $context->instanceid,
                'userid' => $userid,
            ]);
            if ($state) {
                $state->timemodified = transform::datetime($state->timemodified);
                $state->timecreated  = transform::datetime($state->timecreated);
                $state->iscompleted  = transform::yesno((bool)$state->iscompleted);
                // G1 fix: convert intervaljson from raw JSON to a human-readable string
                // so the exported data is understandable without technical knowledge.
                // Format: "0:00-1:23, 2:45-3:10" instead of "[[0,83],[165,190]]".
                if (!empty($state->intervaljson)) {
                    $intervals = json_decode($state->intervaljson, true);
                    if (is_array($intervals)) {
                        $fmt = function($s) {
                            $s = (int)round(max(0, (float)$s));
                            return sprintf('%d:%02d', intdiv($s, 60), $s % 60);
                        };
                        $readable = [];
                        foreach ($intervals as $seg) {
                            if (!is_array($seg) || count($seg) < 2 || !is_numeric($seg[0]) || !is_numeric($seg[1])) {
                                continue;
                            }
                            $start = (float)$seg[0];
                            $end = (float)$seg[1];
                            if ($end <= $start) {
                                continue;
                            }
                            $readable[] = $fmt($start) . '-' . $fmt($end);
                        }
                        $state->intervaljson = implode(', ', $readable);
                    }
                }
                if (!empty($state->videoid)) {
                    $state->videoid = get_string('privacy:videoid_export_note', 'mod_videotrack', $state->videoid);
                }
                unset($state->id, $state->videotrackid, $state->courseid,
                      $state->cmid, $state->userid, $state->sessionid);
            }
            $writer->export_data([get_string('watch', 'mod_videotrack'), get_string('privacy:state', 'mod_videotrack')], (object)[
                'state' => $state,
            ]);

            $segmentrs = $DB->get_recordset('videotrack_seg', [
                'cmid'   => $context->instanceid,
                'userid' => $userid,
            ], 'timecreated ASC');
            $segments = [];
            $chunk = 1;
            foreach ($segmentrs as $segment) {
                $segment->timecreated    = transform::datetime($segment->timecreated);
                $segment->wallclockstart = transform::datetime($segment->wallclockstart);
                $segment->wallclockend   = transform::datetime($segment->wallclockend);
                if (!empty($segment->videoid)) {
                    $segment->videoid = get_string('privacy:videoid_export_note', 'mod_videotrack', $segment->videoid);
                }
                unset($segment->id, $segment->videotrackid, $segment->courseid,
                      $segment->cmid, $segment->userid, $segment->sessionid);
                $segments[] = $segment;
                if (count($segments) >= 500) {
                    $writer->export_data([get_string('watch', 'mod_videotrack'), get_string('privacy:segmentschunk', 'mod_videotrack', $chunk)], (object)[
                        'segments' => $segments,
                    ]);
                    $segments = [];
                    $chunk++;
                }
            }
            $segmentrs->close();
            if (!empty($segments)) {
                $writer->export_data([get_string('watch', 'mod_videotrack'), get_string('privacy:segmentschunk', 'mod_videotrack', $chunk)], (object)[
                    'segments' => $segments,
                ]);
            }

            $eventrs = $DB->get_recordset('videotrack_reactev', [
                'cmid'   => $context->instanceid,
                'userid' => $userid,
            ], 'timecreated ASC');
            $active = [];
            $deleted = [];
            $notes = [];
            $deletednotes = [];
            $activechunk = 1;
            $deletedchunk = 1;
            $noteschunk = 1;
            $deletednoteschunk = 1;
            foreach ($eventrs as $reactionevent) {
                $reactionevent->timecreated  = transform::datetime($reactionevent->timecreated);
                $reactionevent->timemodified = transform::datetime($reactionevent->timemodified);
                $isdeleted = !empty($reactionevent->isdeleted);
                $reactionevent->isdeleted = transform::yesno($isdeleted);
                if (!empty($reactionevent->videoid)) {
                    $reactionevent->videoid = get_string('privacy:videoid_export_note', 'mod_videotrack', $reactionevent->videoid);
                }
                unset($reactionevent->id, $reactionevent->videotrackid, $reactionevent->courseid,
                      $reactionevent->cmid, $reactionevent->userid, $reactionevent->reactionid,
                      $reactionevent->sessionid);

                if (($reactionevent->notetype ?? '') === 'note') {
                    if ($isdeleted) {
                        $deletednotes[] = $reactionevent;
                        if (count($deletednotes) >= 500) {
                            $writer->export_data([get_string('studentnotes_title', 'mod_videotrack'), get_string('privacy:notesdeletedchunk', 'mod_videotrack', $deletednoteschunk)], (object)[
                                'notes' => $deletednotes,
                            ]);
                            $deletednotes = [];
                            $deletednoteschunk++;
                        }
                    } else {
                        $notes[] = $reactionevent;
                        if (count($notes) >= 500) {
                            $writer->export_data([get_string('studentnotes_title', 'mod_videotrack'), get_string('privacy:notesactivechunk', 'mod_videotrack', $noteschunk)], (object)[
                                'notes' => $notes,
                            ]);
                            $notes = [];
                            $noteschunk++;
                        }
                    }
                } else if ($isdeleted) {
                    $deleted[] = $reactionevent;
                    if (count($deleted) >= 500) {
                        $writer->export_data([get_string('reactionsheader', 'mod_videotrack'), get_string('privacy:reactionsdeletedchunk', 'mod_videotrack', $deletedchunk)], (object)[
                            'events' => $deleted,
                        ]);
                        $deleted = [];
                        $deletedchunk++;
                    }
                } else {
                    $active[] = $reactionevent;
                    if (count($active) >= 500) {
                        $writer->export_data([get_string('reactionsheader', 'mod_videotrack'), get_string('privacy:reactionsactivechunk', 'mod_videotrack', $activechunk)], (object)[
                            'events' => $active,
                        ]);
                        $active = [];
                        $activechunk++;
                    }
                }
            }
            $eventrs->close();

            if (!empty($active)) {
                $writer->export_data([get_string('reactionsheader', 'mod_videotrack'), get_string('privacy:reactionsactivechunk', 'mod_videotrack', $activechunk)], (object)[
                    'events' => $active,
                ]);
            }
            if (!empty($deleted)) {
                $writer->export_data([get_string('reactionsheader', 'mod_videotrack'), get_string('privacy:reactionsdeletedchunk', 'mod_videotrack', $deletedchunk)], (object)[
                    'events' => $deleted,
                ]);
            }
            if (!empty($notes)) {
                $writer->export_data([get_string('studentnotes_title', 'mod_videotrack'), get_string('privacy:notesactivechunk', 'mod_videotrack', $noteschunk)], (object)[
                    'notes' => $notes,
                ]);
            }
            if (!empty($deletednotes)) {
                $writer->export_data([get_string('studentnotes_title', 'mod_videotrack'), get_string('privacy:notesdeletedchunk', 'mod_videotrack', $deletednoteschunk)], (object)[
                    'notes' => $deletednotes,
                ]);
            }
        }
    }

    public static function delete_data_for_all_users_in_context(context $context): void {
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        privacy_manager::delete_all_user_data_in_context($context);
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        $user = $contextlist->get_user();
        foreach ($contextlist->get_contexts() as $context) {
            self::delete_records_for_users_in_context($context, [$user->id]);
        }
    }

    public static function delete_data_for_users(approved_userlist $userlist): void {
        self::delete_records_for_users_in_context($userlist->get_context(), $userlist->get_userids());
    }

    /**
     * Deletes user records for GDPR erasure requests.
     *
     * The plugin removes personal tracking rows rather than pseudonymising them,
     * so Privacy API erasure matches the expected right-to-erasure semantics.
     *
     * @param context $context Moodle context.
     * @param array $userids User ids.
     */
    protected static function delete_records_for_users_in_context(context $context, array $userids): void {
        if ($context->contextlevel != CONTEXT_MODULE || empty($userids)) {
            return;
        }

        foreach ($userids as $userid) {
            privacy_manager::delete_user_data_in_context($context, (int)$userid);
        }
    }
}
