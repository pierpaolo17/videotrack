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

/**
 * Resolve the strict-focus accessibility exception for one course.
 *
 * The exception is represented by a hidden, non-participating Moodle core
 * group. VideoTrack stores no reason or additional learner attribute.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class focus_policy {
    /** Stable idnumber of the course-local accessibility exception group. */
    public const EXCEPTION_GROUP_IDNUMBER = 'mod_videotrack_focus_exception';

    /**
     * Return the course exception group when it already exists.
     *
     * @param int $courseid Course id.
     * @return \stdClass|null Group record or null.
     */
    public static function exception_group(int $courseid): ?\stdClass {
        global $DB;

        if ($courseid <= 0) {
            return null;
        }
        return $DB->get_record('groups', [
            'courseid' => $courseid,
            'idnumber' => self::EXCEPTION_GROUP_IDNUMBER,
        ]) ?: null;
    }

    /**
     * Ensure that the course has the hidden, non-participating exception group.
     *
     * Existing groups keep their administrator-visible name and description,
     * while the privacy and participation flags are repaired if necessary.
     *
     * @param int $courseid Course id.
     * @return int Group id.
     */
    public static function ensure_exception_group(int $courseid): int {
        global $CFG, $DB;

        $DB->get_record('course', ['id' => $courseid], 'id', MUST_EXIST);
        require_once($CFG->dirroot . '/group/lib.php');

        $group = self::exception_group($courseid);
        if ($group) {
            if ((int)$group->visibility !== GROUPS_VISIBILITY_NONE || (int)$group->participation !== 0) {
                $group->visibility = GROUPS_VISIBILITY_NONE;
                $group->participation = 0;
                groups_update_group($group, false, false);
            }
            return (int)$group->id;
        }

        return (int)groups_create_group((object)[
            'courseid' => $courseid,
            'name' => get_string('focus:exceptiongroupname', 'mod_videotrack'),
            'description' => get_string('focus:exceptiongroupdescription', 'mod_videotrack'),
            'descriptionformat' => FORMAT_PLAIN,
            'idnumber' => self::EXCEPTION_GROUP_IDNUMBER,
            'visibility' => GROUPS_VISIBILITY_NONE,
            'participation' => 0,
        ], false, false);
    }

    /**
     * Return whether a user is a direct member of the course exception group.
     *
     * The direct core relation is intentional. Visibility-aware group helpers
     * may hide membership even from the member when visibility is NONE.
     *
     * @param int $courseid Course id.
     * @param int $userid User id.
     * @return bool True when the membership grants the focus exception.
     */
    public static function user_has_exception(int $courseid, int $userid): bool {
        global $DB;

        if ($courseid <= 0 || $userid <= 0) {
            return false;
        }
        $group = self::exception_group($courseid);
        return $group !== null && $DB->record_exists('groups_members', [
            'groupid' => (int)$group->id,
            'userid' => $userid,
        ]);
    }

    /**
     * Resolve the effective focus-loss policy for one user in one course.
     *
     * Membership only downgrades strict window-blur handling to hidden-only.
     * Hidden-document pause and every server-side validation remain unchanged.
     *
     * @param int $courseid Course id.
     * @param int $userid User id.
     * @param string|null $sitepolicy Optional pre-resolved site policy.
     * @return string One of the integrity FOCUS_POLICY_* constants.
     */
    public static function effective_policy(
        int $courseid,
        int $userid,
        ?string $sitepolicy = null
    ): string {
        $sitepolicy = $sitepolicy ?? integrity::focus_loss_policy();
        if (
            $sitepolicy === integrity::FOCUS_POLICY_STRICT
            && self::user_has_exception($courseid, $userid)
        ) {
            return integrity::FOCUS_POLICY_HIDDEN_ONLY;
        }
        return $sitepolicy === integrity::FOCUS_POLICY_STRICT
            ? integrity::FOCUS_POLICY_STRICT
            : integrity::FOCUS_POLICY_HIDDEN_ONLY;
    }
}
