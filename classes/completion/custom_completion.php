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

defined('MOODLE_INTERNAL') || die();

/**
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_videotrack\local\tracker;

class custom_completion extends \core_completion\activity_custom_completion {
    public static function get_defined_custom_rules(): array {
        return ['completionpercent', 'minreactions', 'requiredreactions', 'allreactiontypes'];
    }

    public function get_state(string $rule): int {
        global $DB;
        $instance = $DB->get_record('videotrack', ['id' => $this->cm->instance], '*', MUST_EXIST);
        $state = $DB->get_record('videotrack_state', [
            'videotrackid' => $this->cm->instance,
            'userid' => $this->userid,
        ]);

        if ($rule === 'completionpercent') {
            if (empty($instance->completionpercent)) {
                return COMPLETION_INCOMPLETE;
            }
            return (!empty($state) && (float)$state->completionpercent >= (float)$instance->completionpercent)
                ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
            // reaction_counts is NOT loaded here: completionpercent does not need it (B4 fix).
        }

        // Reaction-based rules: load summary only when reactions are enabled AND needed.
        // Moved inside the reaction rules block to avoid 2 unnecessary DB queries when
        // the rule being evaluated is 'completionpercent' (B4 fix).
        if (!empty($instance->reactionsenabled)) {
            $summary = tracker::reaction_counts($this->cm->instance, $this->userid);
        } else {
            $summary = ['uniquecount' => 0, 'uniqueids' => []];
        }

        if ($rule === 'minreactions') {
            if (empty($instance->reactionsrequired) || empty($instance->minreactions)) {
                return COMPLETION_INCOMPLETE;
            }
            return ($summary['uniquecount'] >= (int)$instance->minreactions) ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        }
        if ($rule === 'requiredreactions') {
            $requiredids = array_keys((array)$DB->get_records_menu('videotrack_react', [
                'videotrackid' => $this->cm->instance,
                'requiredforcompletion' => 1,
                'isdeleted' => 0,
            ], '', 'id,id'));
            if (empty($requiredids)) {
                return COMPLETION_INCOMPLETE;
            }
            return count(array_intersect(
                array_map('intval', $requiredids),
                array_map('intval', $summary['uniqueids'])
            )) === count($requiredids) ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        }
        if ($rule === 'allreactiontypes') {
            if (empty($instance->requireallreactiontypes)) {
                return COMPLETION_INCOMPLETE;
            }
            $reactions = $DB->get_records('videotrack_react', ['videotrackid' => $this->cm->instance, 'isdeleted' => 0], '', 'id');
            $requiredids = array_keys($reactions);
            if (empty($requiredids)) {
                return COMPLETION_INCOMPLETE;
            }
            return count(array_intersect(
                array_map('intval', $requiredids),
                array_map('intval', $summary['uniqueids'])
            )) === count($requiredids) ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
        }
        return COMPLETION_INCOMPLETE;
    }

    public function get_custom_rule_descriptions(): array {
        $instance = $this->cm->get_instance_record();
        $descriptions = [];
        if (!empty($instance->completionpercent)) {
            $descriptions['completionpercent'] = get_string('completiondetail:percent', 'mod_videotrack', $instance->completionpercent);
        }
        if (!empty($instance->reactionsrequired) && !empty($instance->minreactions)) {
            $descriptions['minreactions'] = get_string('completiondetail:minreactions', 'mod_videotrack', $instance->minreactions);
        }
        $required = $this->get_required_reaction_labels();
        if (!empty($required)) {
            $descriptions['requiredreactions'] = get_string('completiondetail:requiredreactions', 'mod_videotrack', implode(', ', $required));
        }
        if (!empty($instance->requireallreactiontypes)) {
            $descriptions['allreactiontypes'] = get_string('completiondetail:allreactiontypes', 'mod_videotrack');
        }
        return $descriptions;
    }

    private function get_required_reaction_labels(): array {
        global $DB;
        $records = $DB->get_records('videotrack_react', [
            'videotrackid' => $this->cm->instance,
            'requiredforcompletion' => 1,
            'isdeleted' => 0,
        ], 'sortorder ASC, id ASC', 'id,label');
        return array_map(static function($record) {
            return format_string($record->label);
        }, array_values($records));
    }
}
