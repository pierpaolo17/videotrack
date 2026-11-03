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

namespace mod_videotrack\privacy;

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\content_writer;
use core_privacy\local\request\writer;

/**
 * Streams approved user records into the Moodle privacy export.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
final class exporter {
    /**
     * Formats a number of seconds for human-readable privacy exports.
     *
     * @param float $seconds Seconds from the beginning of the video.
     * @return string MM:SS formatted value.
     */
    private static function format_interval_second(float $seconds): string {
        $seconds = (int)round(max(0, $seconds));
        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }

    /**
     * Exports personal data for the approved context list.
     *
     * @param approved_contextlist $contextlist Approved context list.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != \core\context\module::LEVEL) {
                continue;
            }

            $cmid = $context->instanceid;
            $writer = writer::with_context($context);
            self::export_state($cmid, $userid, $writer);
            self::export_segments($cmid, $userid, $writer);
            event_exporter::export($cmid, $userid, $writer);
            self::export_integrity($cmid, $userid, $writer);
            self::export_acknowledgements($cmid, $userid, $writer);
        }
    }

    /**
     * Export the stored playback state and its readable watched intervals.
     *
     * @param int $cmid Course module ID.
     * @param int $userid User ID.
     * @param content_writer $writer Context-scoped privacy writer.
     */
    private static function export_state(int $cmid, int $userid, content_writer $writer): void {
        global $DB;

        $state = $DB->get_record('videotrack_state', [
            'cmid'   => $cmid,
            'userid' => $userid,
        ]);
        if ($state) {
            $state->timemodified = transform::datetime($state->timemodified);
            $state->timecreated  = transform::datetime($state->timecreated);
            if (!empty($state->serverlastactivity)) {
                $state->serverlastactivity = transform::datetime((int)floor($state->serverlastactivity / 1000));
            }
            $state->iscompleted  = transform::yesno((bool)$state->iscompleted);
            $state->intervaljson = self::format_intervals($state->intervaljson);
            if (!empty($state->videoid)) {
                $state->videoid = get_string('privacy:videoid_export_note', 'mod_videotrack', $state->videoid);
            }
            unset(
                $state->id,
                $state->videotrackid,
                $state->courseid,
                $state->cmid,
                $state->userid,
                $state->sessionid
            );
        }
        if ($state) {
            $writer->export_data(
                [
                    get_string('watch', 'mod_videotrack'),
                    get_string('privacy:state', 'mod_videotrack'),
                ],
                (object)['state' => $state]
            );
        }
    }

    /**
     * Render stored watched intervals with the same fallback labels as the existing export.
     *
     * @param string|null $intervaljson Raw stored intervals.
     * @return string Formatted intervals or a localised fallback.
     */
    private static function format_intervals(?string $intervaljson): string {
        if (!empty($intervaljson)) {
            $intervals = json_decode($intervaljson, true);
            if (is_array($intervals)) {
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
                    $readable[] = self::format_interval_second($start) . '-' . self::format_interval_second($end);
                }
                return $readable
                    ? implode(', ', $readable)
                    : get_string('privacy:intervals_none', 'mod_videotrack');
            } else {
                return get_string('privacy:intervals_unavailable', 'mod_videotrack');
            }
        } else {
            return get_string('privacy:intervals_none', 'mod_videotrack');
        }
    }

    /**
     * Stream watched segments in 500-row chunks.
     *
     * @param int $cmid Course module ID.
     * @param int $userid User ID.
     * @param content_writer $writer Context-scoped privacy writer.
     */
    private static function export_segments(int $cmid, int $userid, content_writer $writer): void {
        global $DB;

        $segmentrs = $DB->get_recordset('videotrack_seg', [
            'cmid'   => $cmid,
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
            unset(
                $segment->id,
                $segment->videotrackid,
                $segment->courseid,
                $segment->cmid,
                $segment->userid,
                $segment->sessionid
            );
            $segments[] = $segment;
            if (count($segments) >= 500) {
                $writer->export_data(
                    [
                        get_string('watch', 'mod_videotrack'),
                        get_string('privacy:segmentschunk', 'mod_videotrack', $chunk),
                    ],
                    (object)['segments' => $segments]
                );
                $segments = [];
                $chunk++;
            }
        }
        $segmentrs->close();
        if (!empty($segments)) {
            $writer->export_data(
                [
                    get_string('watch', 'mod_videotrack'),
                    get_string('privacy:segmentschunk', 'mod_videotrack', $chunk),
                ],
                (object)['segments' => $segments]
            );
        }
    }

    /**
     * Stream diagnostic signals in 500-row chunks.
     *
     * @param int $cmid Course module ID.
     * @param int $userid User ID.
     * @param content_writer $writer Context-scoped privacy writer.
     */
    private static function export_integrity(int $cmid, int $userid, content_writer $writer): void {
        global $DB;

        $integrityrs = $DB->get_recordset('videotrack_integrity', [
            'cmid' => $cmid,
            'userid' => $userid,
        ], 'timecreated ASC', 'id, eventtype, videotime, timecreated');
        $integrityevents = [];
        $integritychunk = 1;
        foreach ($integrityrs as $integrityevent) {
            $integrityevent->eventtype = get_string(
                \mod_videotrack\local\integrity::label_string((string)$integrityevent->eventtype),
                'mod_videotrack'
            );
            $integrityevent->videotime = self::format_interval_second((float)$integrityevent->videotime);
            $integrityevent->timecreated = transform::datetime((int)$integrityevent->timecreated);
            $integrityevents[] = $integrityevent;
            if (count($integrityevents) >= 500) {
                $writer->export_data(
                    [
                        get_string('integrity:reporttitle', 'mod_videotrack'),
                        get_string('privacy:integritychunk', 'mod_videotrack', $integritychunk),
                    ],
                    (object)['signals' => $integrityevents]
                );
                $integrityevents = [];
                $integritychunk++;
            }
        }
        $integrityrs->close();
        if ($integrityevents) {
            $writer->export_data(
                [
                    get_string('integrity:reporttitle', 'mod_videotrack'),
                    get_string('privacy:integritychunk', 'mod_videotrack', $integritychunk),
                ],
                (object)['signals' => $integrityevents]
            );
        }
    }

    /**
     * Export the learner acknowledgement history.
     *
     * @param int $cmid Course module ID.
     * @param int $userid User ID.
     * @param content_writer $writer Context-scoped privacy writer.
     */
    private static function export_acknowledgements(int $cmid, int $userid, content_writer $writer): void {
        global $DB;

        $acknowledgements = $DB->get_records('videotrack_acknowledge', [
            'cmid' => $cmid,
            'userid' => $userid,
        ], 'timeconfirmed ASC', 'id, statementhash, instanceversion, viewedseconds, viewedpercent, timeconfirmed');
        foreach ($acknowledgements as $acknowledgement) {
            $acknowledgement->timeconfirmed = transform::datetime((int)$acknowledgement->timeconfirmed);
        }
        if ($acknowledgements) {
            $writer->export_data(
                [get_string('acknowledgement:privacyexport', 'mod_videotrack')],
                (object)['confirmations' => array_values($acknowledgements)]
            );
        }
    }
}
