<?php
namespace mod_videotrack\completion;

defined('MOODLE_INTERNAL') || die();

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
        }

        // Reaction-based rules: load summary only when reactions are enabled.
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
