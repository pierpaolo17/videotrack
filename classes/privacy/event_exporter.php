<?php
// This file is part of Moodle - https://moodle.org/.
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

use core_privacy\local\request\transform;
use core_privacy\local\request\content_writer;
use core_privacy\local\request\writer;

/**
 * Streams the six separate privacy event categories in bounded chunks.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
final class event_exporter {
    /** Maximum rows retained in each event category. */
    private const CHUNK_SIZE = 500;

    /**
     * Export events for an approved user and course module.
     *
     * Completed chunks are written as they fill in database order. The remaining
     * chunks are written in the historical category order after closing the recordset.
     *
     * @param int $cmid Course module ID.
     * @param int $userid User ID.
     * @param content_writer $writer Context-scoped privacy writer.
     */
    public static function export(int $cmid, int $userid, content_writer $writer): void {
        global $DB;

        // Insertion order also determines the order of the final partial chunks.
        $categories = [
            'active' => ['reactionsheader', 'privacy:reactionsactivechunk', 'events'],
            'deleted' => ['reactionsheader', 'privacy:reactionsdeletedchunk', 'events'],
            'notes' => ['studentnotes_title', 'privacy:notesactivechunk', 'notes'],
            'deletednotes' => ['studentnotes_title', 'privacy:notesdeletedchunk', 'notes'],
            'bookmarks' => ['bookmarks_title', 'privacy:bookmarksactivechunk', 'bookmarks'],
            'deletedbookmarks' => ['bookmarks_title', 'privacy:bookmarksdeletedchunk', 'bookmarks'],
        ];
        $batches = array_fill_keys(array_keys($categories), []);
        $chunks = array_fill_keys(array_keys($categories), 1);
        $eventrs = $DB->get_recordset('videotrack_reactev', [
            'cmid' => $cmid,
            'userid' => $userid,
        ], 'timecreated ASC');
        foreach ($eventrs as $event) {
            $category = self::category($event);
            self::prepare_event($event);
            $batches[$category][] = $event;
            if (count($batches[$category]) >= self::CHUNK_SIZE) {
                self::write_chunk($writer, $categories[$category], $chunks[$category], $batches[$category]);
                $batches[$category] = [];
                $chunks[$category]++;
            }
        }
        $eventrs->close();
        foreach ($categories as $category => $details) {
            if ($batches[$category]) {
                self::write_chunk($writer, $details, $chunks[$category], $batches[$category]);
            }
        }
    }

    /**
     * Remove internal identifiers and make the recorded values readable.
     *
     * @param \stdClass $event Stored interaction row.
     */
    private static function prepare_event(\stdClass $event): void {
        $event->timecreated = transform::datetime($event->timecreated);
        $event->timemodified = transform::datetime($event->timemodified);
        $event->isdeleted = transform::yesno(!empty($event->isdeleted));
        if (!empty($event->videoid)) {
            $event->videoid = get_string('privacy:videoid_export_note', 'mod_videotrack', $event->videoid);
        }
        unset(
            $event->id,
            $event->videotrackid,
            $event->courseid,
            $event->cmid,
            $event->userid,
            $event->reactionid,
            $event->sessionid
        );
    }

    /**
     * Select the historical export category without relying on translated values.
     *
     * @param \stdClass $event Stored interaction row.
     * @return string Category key.
     */
    private static function category(\stdClass $event): string {
        $isdeleted = !empty($event->isdeleted);
        if (($event->notetype ?? '') === 'note') {
            return $isdeleted ? 'deletednotes' : 'notes';
        }
        if (($event->notetype ?? '') === 'bookmark') {
            return $isdeleted ? 'deletedbookmarks' : 'bookmarks';
        }
        return $isdeleted ? 'deleted' : 'active';
    }

    /**
     * Write one category chunk with its original path and payload name.
     *
     * @param content_writer $writer Context-scoped privacy writer.
     * @param array $details Translation keys and payload name.
     * @param int $chunk One-based chunk number.
     * @param array $events Exportable interaction rows.
     */
    private static function write_chunk(content_writer $writer, array $details, int $chunk, array $events): void {
        [$title, $label, $field] = $details;
        $writer->export_data(
            [get_string($title, 'mod_videotrack'), get_string($label, 'mod_videotrack', $chunk)],
            (object)[$field => $events]
        );
    }
}
