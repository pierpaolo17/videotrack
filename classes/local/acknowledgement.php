<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace mod_videotrack\local;

use context_module;
use moodle_exception;
use stdClass;

/**
 * Versioned learner acknowledgement helpers.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class acknowledgement {
    /** Confirmation can be submitted at any point. */
    public const TIMING_ANYTIME = 0;

    /** Confirmation requires the final video second to have been reached. */
    public const TIMING_VIDEO_END = 1;

    /** Tolerance used when comparing tracked video time with the media duration. */
    private const END_TOLERANCE_SECONDS = 1.0;

    /**
     * Return whether the current activity contains an enabled acknowledgement statement.
     *
     * @param stdClass $instance Activity instance.
     * @return bool
     */
    public static function is_enabled(stdClass $instance): bool {
        return !empty($instance->acknowledgementenabled)
            && self::has_visible_text((string)($instance->acknowledgementtext ?? ''));
    }

    /**
     * Return whether formatted content contains visible non-spacing text.
     *
     * @param string $text Formatted statement text.
     * @return bool
     */
    public static function has_visible_text(string $text): bool {
        $plain = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = preg_replace('/[\p{Z}\s]+/u', '', $plain);
        return is_string($plain) && $plain !== '';
    }

    /**
     * Return the configured acknowledgement timing policy.
     *
     * @param stdClass $instance Activity instance.
     * @return int One of the TIMING_* constants.
     */
    public static function timing(stdClass $instance): int {
        $timing = (int)($instance->acknowledgementtiming ?? self::TIMING_ANYTIME);
        return in_array($timing, [self::TIMING_ANYTIME, self::TIMING_VIDEO_END], true)
            ? $timing
            : self::TIMING_ANYTIME;
    }

    /**
     * Return whether the statement requires the final video second.
     *
     * @param stdClass $instance Activity instance.
     * @return bool
     */
    public static function requires_video_end(stdClass $instance): bool {
        return self::timing($instance) === self::TIMING_VIDEO_END;
    }

    /**
     * Build the stable hash used to identify the current statement version.
     *
     * Confirmation timing is part of the policy and therefore part of the version identity.
     *
     * @param stdClass $instance Activity instance.
     * @return string SHA-256 hash.
     */
    public static function statement_hash(stdClass $instance): string {
        $format = (int)($instance->acknowledgementformat ?? FORMAT_HTML);
        $text = trim((string)($instance->acknowledgementtext ?? ''));
        $policy = self::timing($instance) === self::TIMING_VIDEO_END ? "videoend\n" : '';
        return hash('sha256', $policy . $format . "\n" . $text);
    }

    /**
     * Build the immutable viewing snapshot stored with a confirmation.
     *
     * @param stdClass $instance Activity instance.
     * @param stdClass|null $state Current aggregate tracking state.
     * @return array{viewedseconds: float, viewedpercent: float|null, duration: float, reachedend: bool}
     */
    public static function progress_snapshot(stdClass $instance, ?stdClass $state): array {
        $duration = max(0.0, (float)($instance->durationseconds ?? 0));
        $viewedseconds = max(0.0, (float)($state->uniquecoveredseconds ?? 0));
        if ($duration > 0) {
            $viewedseconds = min($duration, $viewedseconds);
            $viewedpercent = min(100.0, round(($viewedseconds / $duration) * 100, 2));
        } else {
            $viewedpercent = null;
        }

        return [
            'viewedseconds' => round($viewedseconds, 3),
            'viewedpercent' => $viewedpercent === null ? null : round($viewedpercent, 2),
            'duration' => round($duration, 3),
            'reachedend' => self::has_reached_video_end($instance, $state),
        ];
    }

    /**
     * Return whether persisted tracking proves that the final video second was reached.
     *
     * @param stdClass $instance Activity instance.
     * @param stdClass|null $state Current aggregate tracking state.
     * @return bool
     */
    public static function has_reached_video_end(stdClass $instance, ?stdClass $state): bool {
        if (!$state) {
            return false;
        }
        $duration = max(0.0, (float)($instance->durationseconds ?? 0));
        if ($duration <= 0) {
            return false;
        }
        $threshold = max(0.0, $duration - self::END_TOLERANCE_SECONDS);
        $furthest = max(0.0, (float)($state->lastposition ?? 0));
        foreach (tracker::decode_intervals((string)($state->intervaljson ?? '[]')) as $interval) {
            $furthest = max($furthest, (float)$interval[1]);
        }
        return $furthest >= $threshold;
    }

    /**
     * Return whether the user may submit the current statement now.
     *
     * @param stdClass $instance Activity instance.
     * @param stdClass|null $state Current aggregate tracking state.
     * @return bool
     */
    public static function can_confirm(stdClass $instance, ?stdClass $state): bool {
        return self::is_enabled($instance)
            && (!self::requires_video_end($instance) || self::has_reached_video_end($instance, $state));
    }

    /**
     * Build a privacy-safe Analytics summary from current confirmation records.
     *
     * Average viewing values use only records that contain the immutable progress
     * snapshot introduced in VideoTrack 1.6.20. Legacy records are counted
     * separately and are never treated as zero progress.
     *
     * @param iterable $records Current confirmation records.
     * @param int $minusers Minimum distinct users required for exact values.
     * @return array Aggregate confirmation metrics and suppression state.
     */
    public static function analytics_summary(iterable $records, int $minusers): array {
        $confirmationcount = 0;
        $userids = [];
        $progresscount = 0;
        $progressuserids = [];
        $viewedsecondssum = 0.0;
        $viewedpercentsum = 0.0;

        foreach ($records as $record) {
            $userid = (int)($record->userid ?? 0);
            if ($userid <= 0) {
                continue;
            }
            $confirmationcount++;
            $userids[$userid] = true;
            $viewedseconds = property_exists($record, 'viewedseconds') ? $record->viewedseconds : null;
            $viewedpercent = property_exists($record, 'viewedpercent') ? $record->viewedpercent : null;
            if ($viewedseconds === null || $viewedpercent === null) {
                continue;
            }
            $progresscount++;
            $progressuserids[$userid] = true;
            $viewedsecondssum += max(0.0, (float)$viewedseconds);
            $viewedpercentsum += min(100.0, max(0.0, (float)$viewedpercent));
        }

        $studentcount = count($userids);
        $progressstudentcount = count($progressuserids);
        $hasdata = $confirmationcount > 0;
        $minusers = max(1, $minusers);
        $suppressed = $hasdata && $studentcount < $minusers;
        $progresssuppressed = !$suppressed && $progresscount > 0 && $progressstudentcount < $minusers;
        $averageviewedseconds = $progresscount > 0
            ? round($viewedsecondssum / $progresscount, 3)
            : null;
        $averageviewedpercent = $progresscount > 0
            ? round($viewedpercentsum / $progresscount, 2)
            : null;

        return [
            'hasdata' => $hasdata,
            'confirmationcount' => $suppressed ? null : $confirmationcount,
            'studentcount' => $suppressed ? null : $studentcount,
            'progresscount' => $suppressed ? null : $progresscount,
            'progressstudentcount' => $suppressed ? null : $progressstudentcount,
            'progressmissing' => $suppressed ? null : $confirmationcount - $progresscount,
            'averageviewedseconds' => ($suppressed || $progresssuppressed) ? null : $averageviewedseconds,
            'averageviewedpercent' => ($suppressed || $progresssuppressed) ? null : $averageviewedpercent,
            'suppressed' => $suppressed,
            'progresssuppressed' => $progresssuppressed,
        ];
    }

    /**
     * Return the current confirmation record for a user, when present.
     *
     * @param stdClass $instance Activity instance.
     * @param int $userid User id.
     * @return stdClass|null
     */
    public static function current_record(stdClass $instance, int $userid): ?stdClass {
        global $DB;

        if (!self::is_enabled($instance) || $userid <= 0) {
            return null;
        }
        $record = $DB->get_record('videotrack_acknowledge', [
            'videotrackid' => (int)$instance->id,
            'userid' => $userid,
            'statementhash' => self::statement_hash($instance),
        ]);
        return $record ?: null;
    }

    /**
     * Record the user's explicit confirmation of the current statement.
     *
     * Repeated submissions are idempotent and return the existing record.
     *
     * @param stdClass $instance Activity instance.
     * @param int $cmid Course module id.
     * @param int $userid User id.
     * @return stdClass Confirmation record.
     */
    public static function confirm(stdClass $instance, int $cmid, int $userid): stdClass {
        global $DB;

        if (!self::is_enabled($instance) || $userid <= 0 || $cmid <= 0) {
            throw new moodle_exception('acknowledgement:unavailable', 'mod_videotrack');
        }
        $existing = self::current_record($instance, $userid);
        if ($existing) {
            return $existing;
        }

        $state = $DB->get_record('videotrack_state', [
            'videotrackid' => (int)$instance->id,
            'userid' => $userid,
        ]);
        if (!self::can_confirm($instance, $state ?: null)) {
            throw new moodle_exception('acknowledgement:videoendrequired', 'mod_videotrack');
        }
        $snapshot = self::progress_snapshot($instance, $state ?: null);
        $now = time();
        $record = (object)[
            'videotrackid' => (int)$instance->id,
            'courseid' => (int)$instance->course,
            'cmid' => $cmid,
            'userid' => $userid,
            'statementhash' => self::statement_hash($instance),
            'instanceversion' => (int)($instance->timemodified ?? 0),
            'viewedseconds' => $snapshot['viewedseconds'],
            'viewedpercent' => $snapshot['viewedpercent'],
            'timeconfirmed' => $now,
        ];
        try {
            $record->id = $DB->insert_record('videotrack_acknowledge', $record);
        } catch (\dml_write_exception $exception) {
            // A parallel submission may have inserted the same unique record first.
            $existing = self::current_record($instance, $userid);
            if (!$existing) {
                throw $exception;
            }
            return $existing;
        }

        $event = \mod_videotrack\event\acknowledgement_confirmed::create([
            'objectid' => (int)$record->id,
            'context' => context_module::instance($cmid),
            'userid' => $userid,
        ]);
        $event->add_record_snapshot('videotrack_acknowledge', $record);
        $event->trigger();

        return $record;
    }

    /**
     * Return current confirmations keyed by user id.
     *
     * @param stdClass $instance Activity instance.
     * @param int[] $userids Optional user restriction.
     * @return stdClass[]
     */
    public static function current_records(stdClass $instance, array $userids = []): array {
        global $DB;

        if (!self::is_enabled($instance)) {
            return [];
        }
        $params = [
            'videotrackid' => (int)$instance->id,
            'statementhash' => self::statement_hash($instance),
        ];
        $where = 'videotrackid = :videotrackid AND statementhash = :statementhash';
        if ($userids) {
            [$usersql, $userparams] = $DB->get_in_or_equal(
                array_map('intval', $userids),
                SQL_PARAMS_NAMED,
                'ackuser'
            );
            $where .= " AND userid {$usersql}";
            $params = array_merge($params, $userparams);
        }
        return $DB->get_records_select('videotrack_acknowledge', $where, $params, 'timeconfirmed ASC', '*');
    }
}
