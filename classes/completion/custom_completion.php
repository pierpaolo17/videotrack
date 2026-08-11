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

namespace mod_videotrack\completion;

use context_module;
use core_completion\activity_custom_completion;
use mod_videotrack\local\completion_config;
use mod_videotrack\local\tracker;

/**
 * Composite custom completion rule for the VideoTrack activity module.
 *
 * VideoTrack allows teachers to combine its component conditions with either
 * AND or OR logic. Moodle's base activity_custom_completion class aggregates
 * multiple custom rules with AND semantics, so VideoTrack deliberately exposes
 * one composite rule whose state is calculated by the same tracker service used
 * by runtime writes. This keeps Moodle completion and VideoTrack state aligned.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {
    /** Canonical Moodle custom-rule key for the composite VideoTrack condition. */
    public const RULE = 'videotrackconditions';

    /**
     * Return the display order for custom completion rules.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [self::RULE];
    }

    /**
     * Return the list of custom completion rules implemented by the activity.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return [self::RULE];
    }

    /**
     * Return the completion state for the composite VideoTrack rule.
     *
     * @param string $rule The custom completion rule name.
     * @return int Completion state constant.
     */
    public function get_state(string $rule): int {
        global $DB;

        if ($rule !== self::RULE) {
            return COMPLETION_INCOMPLETE;
        }

        $instance = $DB->get_record('videotrack', ['id' => $this->cm->instance], '*', MUST_EXIST);
        $state = $DB->get_record('videotrack_state', [
            'videotrackid' => $this->cm->instance,
            'userid' => $this->userid,
        ]);
        if (!$state) {
            $state = (object)[
                'userid' => $this->userid,
                'completionpercent' => 0,
            ];
        }
        $summary = !empty($instance->reactionsenabled)
            ? tracker::reaction_counts($this->cm->instance, $this->userid)
            : ['uniquecount' => 0, 'uniqueids' => []];
        $requiredreactionids = !empty($instance->reactionsenabled)
            ? completion_config::required_reaction_ids((int)$instance->id)
            : [];

        return tracker::completion_satisfied($instance, $state, $summary, $requiredreactionids)
            ? COMPLETION_COMPLETE
            : COMPLETION_INCOMPLETE;
    }

    /**
     * Return a human-readable description for the composite custom rule.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        $instance = $this->cm->get_instance_record();
        $context = context_module::instance($this->cm->id);
        $conditions = completion_config::active_condition_descriptions($instance, $context);
        if (!$conditions) {
            return [];
        }

        $logic = ($instance->completionlogic ?? 'and') === 'or'
            ? get_string('completiondetail:logicor', 'mod_videotrack')
            : get_string('completiondetail:logicand', 'mod_videotrack');
        return [
            self::RULE => get_string('completiondetail:videotrackconditions', 'mod_videotrack', (object)[
                'logic' => $logic,
                'conditions' => implode('; ', $conditions),
            ]),
        ];
    }
}
