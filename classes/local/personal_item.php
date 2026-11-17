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

namespace mod_videotrack\local;

/**
 * Permanently erases personal study items after the endpoint validates access.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class personal_item {
    /**
     * Recognise personal items already removed in an older backup or database.
     *
     * @param \stdClass $event Stored or restored interaction.
     * @return bool Whether the personal item must not be exported or restored.
     */
    public static function is_deleted(\stdClass $event): bool {
        return !empty($event->isdeleted) && in_array($event->notetype ?? '', ['note', 'bookmark'], true);
    }

    /**
     * Erase an owned note/bookmark, keeping retries free of duplicate events.
     *
     * The caller must validate login, module context, participation and sesskey.
     * Missing ids are successful no-ops; an existing foreign or differently typed
     * row still fails ownership checks. No content or tombstone is retained.
     *
     * @param \stdClass $activity Validated activity.
     * @param \context_module $context Validated module context.
     * @param int $userid Current user.
     * @param int $eventid Personal interaction id.
     * @param string $type Either note or bookmark.
     */
    public static function erase(
        \stdClass $activity,
        \context_module $context,
        int $userid,
        int $eventid,
        string $type
    ): void {
        global $DB;

        if (!in_array($type, ['note', 'bookmark'], true)) {
            throw new \coding_exception('Only personal notes and bookmarks can be erased here.');
        }
        $factory = \core\lock\lock_config::get_lock_factory('mod_videotrack');
        $lock = $factory->get_lock('personalitem:' . $eventid, 10);
        if (!$lock) {
            throw new \moodle_exception('locktimeout', 'error');
        }
        try {
            $event = $DB->get_record('videotrack_reactev', ['id' => $eventid]);
            if (!$event) {
                return;
            }
            self::require_owner($event, $activity, $context, $userid, $type);
            $transaction = $DB->start_delegated_transaction();
            try {
                $DB->delete_records('videotrack_reactev', ['id' => $eventid]);
                self::trigger_deleted_event($event, $context, $type);
                $transaction->allow_commit();
            } catch (\Throwable $error) {
                $transaction->rollback($error);
            }
        } finally {
            $lock->release();
        }
    }

    /**
     * Require the stored owner, activity, module and personal item type.
     *
     * @param \stdClass $event Stored personal row.
     * @param \stdClass $activity Validated activity.
     * @param \context_module $context Validated module context.
     * @param int $userid Current learner.
     * @param string $type Expected personal type.
     */
    private static function require_owner(
        \stdClass $event,
        \stdClass $activity,
        \context_module $context,
        int $userid,
        string $type
    ): void {
        if (
            (int)$event->userid !== $userid
            || (int)$event->videotrackid !== (int)$activity->id
            || (int)$event->cmid !== (int)$context->instanceid
            || $event->notetype !== $type
        ) {
            throw new \dml_missing_record_exception('videotrack_reactev');
        }
    }

    /**
     * Log one removal without retaining private text or repeating an old event.
     *
     * @param \stdClass $event Removed personal row.
     * @param \context_module $context Validated module context.
     * @param string $type Personal item type.
     */
    private static function trigger_deleted_event(\stdClass $event, \context_module $context, string $type): void {
        if (!empty($event->isdeleted)) {
            return;
        }
        $details = ['objectid' => $event->id, 'context' => $context];
        if ($type === 'note') {
            $details['other'] = ['reactionlabel' => get_string('studentnotes_title', 'mod_videotrack')];
            \mod_videotrack\event\note_deleted::create($details)->trigger();
        } else {
            \mod_videotrack\event\bookmark_deleted::create($details)->trigger();
        }
    }
}
