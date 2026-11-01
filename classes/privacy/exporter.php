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
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != \core\context\module::LEVEL) {
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
                if (!empty($state->serverlastactivity)) {
                    $state->serverlastactivity = transform::datetime((int)floor($state->serverlastactivity / 1000));
                }
                $state->iscompleted  = transform::yesno((bool)$state->iscompleted);
                // Convert intervaljson from raw JSON to a human-readable string.
                // So exported data remains understandable without technical knowledge.
                // Example: "0:00-1:23, 2:45-3:10" instead of "[[0,83],[165,190]]".
                if (!empty($state->intervaljson)) {
                    $intervals = json_decode($state->intervaljson, true);
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
                        $state->intervaljson = $readable
                            ? implode(', ', $readable)
                            : get_string('privacy:intervals_none', 'mod_videotrack');
                    } else {
                        $state->intervaljson = get_string('privacy:intervals_unavailable', 'mod_videotrack');
                    }
                } else {
                    $state->intervaljson = get_string('privacy:intervals_none', 'mod_videotrack');
                }
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

            $eventrs = $DB->get_recordset('videotrack_reactev', [
                'cmid'   => $context->instanceid,
                'userid' => $userid,
            ], 'timecreated ASC');
            $active = [];
            $deleted = [];
            $notes = [];
            $deletednotes = [];
            $bookmarks = [];
            $deletedbookmarks = [];
            $activechunk = 1;
            $deletedchunk = 1;
            $noteschunk = 1;
            $deletednoteschunk = 1;
            $bookmarkschunk = 1;
            $deletedbookmarkschunk = 1;
            foreach ($eventrs as $reactionevent) {
                $reactionevent->timecreated  = transform::datetime($reactionevent->timecreated);
                $reactionevent->timemodified = transform::datetime($reactionevent->timemodified);
                $isdeleted = !empty($reactionevent->isdeleted);
                $reactionevent->isdeleted = transform::yesno($isdeleted);
                if (!empty($reactionevent->videoid)) {
                    $reactionevent->videoid = get_string('privacy:videoid_export_note', 'mod_videotrack', $reactionevent->videoid);
                }
                unset(
                    $reactionevent->id,
                    $reactionevent->videotrackid,
                    $reactionevent->courseid,
                    $reactionevent->cmid,
                    $reactionevent->userid,
                    $reactionevent->reactionid,
                    $reactionevent->sessionid
                );

                if (($reactionevent->notetype ?? '') === 'note') {
                    if ($isdeleted) {
                        $deletednotes[] = $reactionevent;
                        if (count($deletednotes) >= 500) {
                            $writer->export_data(
                                [
                                    get_string('studentnotes_title', 'mod_videotrack'),
                                    get_string('privacy:notesdeletedchunk', 'mod_videotrack', $deletednoteschunk),
                                ],
                                (object)['notes' => $deletednotes]
                            );
                            $deletednotes = [];
                            $deletednoteschunk++;
                        }
                    } else {
                        $notes[] = $reactionevent;
                        if (count($notes) >= 500) {
                            $writer->export_data(
                                [
                                    get_string('studentnotes_title', 'mod_videotrack'),
                                    get_string('privacy:notesactivechunk', 'mod_videotrack', $noteschunk),
                                ],
                                (object)['notes' => $notes]
                            );
                            $notes = [];
                            $noteschunk++;
                        }
                    }
                } else if (($reactionevent->notetype ?? '') === 'bookmark') {
                    if ($isdeleted) {
                        $deletedbookmarks[] = $reactionevent;
                        if (count($deletedbookmarks) >= 500) {
                            $writer->export_data(
                                [
                                    get_string('bookmarks_title', 'mod_videotrack'),
                                    get_string('privacy:bookmarksdeletedchunk', 'mod_videotrack', $deletedbookmarkschunk),
                                ],
                                (object)['bookmarks' => $deletedbookmarks]
                            );
                            $deletedbookmarks = [];
                            $deletedbookmarkschunk++;
                        }
                    } else {
                        $bookmarks[] = $reactionevent;
                        if (count($bookmarks) >= 500) {
                            $writer->export_data(
                                [
                                    get_string('bookmarks_title', 'mod_videotrack'),
                                    get_string('privacy:bookmarksactivechunk', 'mod_videotrack', $bookmarkschunk),
                                ],
                                (object)['bookmarks' => $bookmarks]
                            );
                            $bookmarks = [];
                            $bookmarkschunk++;
                        }
                    }
                } else if ($isdeleted) {
                    $deleted[] = $reactionevent;
                    if (count($deleted) >= 500) {
                        $writer->export_data(
                            [
                                get_string('reactionsheader', 'mod_videotrack'),
                                get_string('privacy:reactionsdeletedchunk', 'mod_videotrack', $deletedchunk),
                            ],
                            (object)['events' => $deleted]
                        );
                        $deleted = [];
                        $deletedchunk++;
                    }
                } else {
                    $active[] = $reactionevent;
                    if (count($active) >= 500) {
                        $writer->export_data(
                            [
                                get_string('reactionsheader', 'mod_videotrack'),
                                get_string('privacy:reactionsactivechunk', 'mod_videotrack', $activechunk),
                            ],
                            (object)['events' => $active]
                        );
                        $active = [];
                        $activechunk++;
                    }
                }
            }
            $eventrs->close();

            if (!empty($active)) {
                $writer->export_data(
                    [
                        get_string('reactionsheader', 'mod_videotrack'),
                        get_string('privacy:reactionsactivechunk', 'mod_videotrack', $activechunk),
                    ],
                    (object)['events' => $active]
                );
            }
            if (!empty($deleted)) {
                $writer->export_data(
                    [
                        get_string('reactionsheader', 'mod_videotrack'),
                        get_string('privacy:reactionsdeletedchunk', 'mod_videotrack', $deletedchunk),
                    ],
                    (object)['events' => $deleted]
                );
            }
            if (!empty($notes)) {
                $writer->export_data(
                    [
                        get_string('studentnotes_title', 'mod_videotrack'),
                        get_string('privacy:notesactivechunk', 'mod_videotrack', $noteschunk),
                    ],
                    (object)['notes' => $notes]
                );
            }
            if (!empty($deletednotes)) {
                $writer->export_data(
                    [
                        get_string('studentnotes_title', 'mod_videotrack'),
                        get_string('privacy:notesdeletedchunk', 'mod_videotrack', $deletednoteschunk),
                    ],
                    (object)['notes' => $deletednotes]
                );
            }
            if (!empty($bookmarks)) {
                $writer->export_data(
                    [
                        get_string('bookmarks_title', 'mod_videotrack'),
                        get_string('privacy:bookmarksactivechunk', 'mod_videotrack', $bookmarkschunk),
                    ],
                    (object)['bookmarks' => $bookmarks]
                );
            }
            if (!empty($deletedbookmarks)) {
                $writer->export_data(
                    [
                        get_string('bookmarks_title', 'mod_videotrack'),
                        get_string('privacy:bookmarksdeletedchunk', 'mod_videotrack', $deletedbookmarkschunk),
                    ],
                    (object)['bookmarks' => $deletedbookmarks]
                );
            }

            $integrityrs = $DB->get_recordset('videotrack_integrity', [
                'cmid' => $context->instanceid,
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

            $acknowledgements = $DB->get_records('videotrack_acknowledge', [
                'cmid' => $context->instanceid,
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
}
