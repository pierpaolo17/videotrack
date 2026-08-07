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
use stdClass;

/**
 * Canonical participant visibility rules for reports and tracking.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class learner_scope {
    /**
     * Returns SQL limiting a userid field to active enrolled learners visible to the report viewer.
     *
     * @param context_module $context Activity context.
     * @param stdClass $cm Course-module record.
     * @param stdClass $course Course record.
     * @param int $viewerid Report viewer id.
     * @param string $useridfield SQL userid field expression.
     * @param string $paramprefix Optional prefix used to make named SQL parameters unique.
     * @return array{0:string,1:array}
     */
    public static function sql(
        context_module $context,
        stdClass $cm,
        stdClass $course,
        int $viewerid,
        string $useridfield = 'userid',
        string $paramprefix = ''
    ): array {
        global $CFG;

        require_once($CFG->libdir . '/enrollib.php');
        require_once($CFG->libdir . '/grouplib.php');

        $instance = (object)[
            'course' => (int)$course->id,
            'cmid' => (int)$cm->id,
            'groupmode' => (int)$cm->groupmode,
            'groupingid' => (int)$cm->groupingid,
            'coursegroupmode' => (int)$course->groupmode,
            'groupmodeforce' => (int)$course->groupmodeforce,
        ];
        $groupids = analytics_scope::accessible_group_ids($instance, $viewerid);
        if (is_array($groupids) && !$groupids) {
            return ['1 = 0', []];
        }

        [$participantsql, $participantparams] = get_enrolled_sql(
            $context,
            'mod/videotrack:participate',
            $groupids ?? 0,
            true
        );
        if ($paramprefix !== '') {
            [$participantsql, $participantparams] = self::prefix_named_params(
                $participantsql,
                $participantparams,
                $paramprefix . 'participant'
            );
        }
        return [
            "{$useridfield} IN ({$participantsql})",
            $participantparams,
        ];
    }

    /**
     * Prefixes named SQL parameters returned by Moodle enrollment helpers.
     *
     * @param string $sql SQL containing named placeholders.
     * @param array $params Named SQL parameters.
     * @param string $prefix Unique alphanumeric prefix.
     * @return array{0:string,1:array}
     */
    private static function prefix_named_params(string $sql, array $params, string $prefix): array {
        $renamed = [];
        uksort($params, static fn(string $left, string $right): int => strlen($right) <=> strlen($left));
        foreach ($params as $name => $value) {
            $newname = $prefix . $name;
            $sql = preg_replace(
                '/:' . preg_quote($name, '/') . '(?![A-Za-z0-9_])/',
                ':' . $newname,
                $sql
            );
            $renamed[$newname] = $value;
        }
        return [$sql, $renamed];
    }

    /**
     * Checks whether one user belongs to the canonical learner scope visible to a viewer.
     *
     * @param context_module $context Activity context.
     * @param stdClass $cm Course-module record.
     * @param stdClass $course Course record.
     * @param int $viewerid Viewer id.
     * @param int $learnerid Candidate learner id.
     * @return bool
     */
    public static function user_is_visible(
        context_module $context,
        stdClass $cm,
        stdClass $course,
        int $viewerid,
        int $learnerid
    ): bool {
        global $CFG, $DB;

        require_once($CFG->libdir . '/enrollib.php');

        if ($learnerid <= 0 || !is_enrolled($context, $learnerid, 'mod/videotrack:participate', true)) {
            return false;
        }
        [$sql, $params] = self::sql($context, $cm, $course, $viewerid, 'u.id');
        $params['candidateid'] = $learnerid;
        return $DB->record_exists_sql("SELECT 1 FROM {user} u WHERE u.id = :candidateid AND {$sql}", $params);
    }
}
