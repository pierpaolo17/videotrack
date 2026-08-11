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

namespace mod_videotrack;

use advanced_testcase;
use mod_videotrack\local\completion_config;
use mod_videotrack\local\tracker;

/**
 * Completion integration contracts.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class completion_contract_test extends advanced_testcase {
    /**
     * Individually required reactions participate in custom completion only when reactions are enabled.
     */
    public function test_required_reactions_activate_custom_completion(): void {
        $instance = (object)[
            'id' => 101,
            'completionpercent' => 0,
            'completionacknowledgement' => 0,
            'acknowledgementenabled' => 0,
            'reactionsenabled' => 1,
            'reactionsrequired' => 0,
            'minreactions' => 0,
            'requireallreactiontypes' => 0,
        ];

        $this->assertTrue(completion_config::has_custom_rules($instance, true));
        $instance->reactionsenabled = 0;
        $this->assertFalse(completion_config::has_custom_rules($instance, true));
    }

    /**
     * Composite Moodle completion preserves VideoTrack OR semantics for individually required reactions.
     */
    public function test_required_reaction_or_semantics_match_tracker(): void {
        $instance = (object)[
            'id' => 102,
            'completionpercent' => 0,
            'completionacknowledgement' => 0,
            'reactionsenabled' => 1,
            'reactionsrequired' => 0,
            'minreactions' => 0,
            'requireallreactiontypes' => 0,
            'completionlogic' => 'or',
        ];
        $summary = ['uniquecount' => 1, 'uniqueids' => [10]];

        $this->assertTrue(tracker::completion_satisfied($instance, null, $summary, [10, 11]));
        $instance->completionlogic = 'and';
        $this->assertFalse(tracker::completion_satisfied($instance, null, $summary, [10, 11]));
    }

    /**
     * Disabled acknowledgement cannot leave a stale acknowledgement rule blocking completion.
     */
    public function test_disabled_acknowledgement_is_excluded_from_tracker_completion(): void {
        $instance = (object)[
            'id' => 104,
            'completionpercent' => 50,
            'completionacknowledgement' => 1,
            'acknowledgementenabled' => 0,
            'reactionsenabled' => 0,
            'reactionsrequired' => 0,
            'minreactions' => 0,
            'requireallreactiontypes' => 0,
            'completionlogic' => 'and',
        ];
        $state = (object)['completionpercent' => 75, 'userid' => 7];

        $this->assertTrue(tracker::completion_satisfied(
            $instance,
            $state,
            ['uniquecount' => 0, 'uniqueids' => []],
            []
        ));
    }

    /**
     * Disabled reactions cannot leave stale reaction rules blocking completion.
     */
    public function test_disabled_reactions_are_excluded_from_tracker_completion(): void {
        $instance = (object)[
            'id' => 103,
            'completionpercent' => 50,
            'completionacknowledgement' => 0,
            'reactionsenabled' => 0,
            'reactionsrequired' => 1,
            'minreactions' => 2,
            'requireallreactiontypes' => 1,
            'completionlogic' => 'and',
        ];
        $state = (object)['completionpercent' => 75, 'userid' => 7];
        $summary = ['uniquecount' => 0, 'uniqueids' => []];

        $this->assertTrue(tracker::completion_satisfied($instance, $state, $summary, [10, 11]));
    }

    /**
     * Moodle cm_info and custom completion expose one composite rule backed by the tracker.
     */
    public function test_moodle_completion_uses_composite_rule_contract(): void {
        $root = dirname(__DIR__);
        $lib = file_get_contents($root . '/lib.php');
        $customcompletion = file_get_contents($root . '/classes/completion/custom_completion.php');

        $this->assertStringContainsString("customdata['customcompletionrules']", $lib);
        $this->assertStringContainsString('required_reaction_activity_set', $lib);
        $this->assertStringContainsString('custom_completion::RULE', $lib);
        $this->assertStringContainsString("public const RULE = 'videotrackconditions';", $customcompletion);
        $this->assertStringContainsString('return [self::RULE];', $customcompletion);
        $this->assertStringContainsString('tracker::completion_satisfied(', $customcompletion);
    }

    /**
     * Form validation and locking include reaction-based completion inputs.
     */
    public function test_form_includes_required_reactions_in_completion_contract(): void {
        $source = file_get_contents(dirname(__DIR__) . '/mod_form.php');

        $this->assertStringContainsString("(array)(\$data['reactionrequired'] ?? [])", $source);
        $this->assertStringContainsString("\$mform->freeze('reactionrequired[' . \$i . ']');", $source);
        $this->assertStringContainsString('$suffix = $this->get_suffix();', $source);
        $this->assertStringContainsString("'completionreactionrules' . \$suffix", $source);
        $this->assertStringContainsString("'completionlogic' . \$suffix", $source);
        $this->assertStringContainsString("'completionpercent' . \$suffix", $source);
        $this->assertStringNotContainsString('get_suffixed_name(', $source);
        $this->assertStringContainsString('parent::data_preprocessing($defaultvalues);', $source);
    }

    /**
     * Updating completion configuration triggers a full tracked-state recalculation.
     */
    public function test_instance_update_recalculates_changed_completion_configuration(): void {
        $source = file_get_contents(dirname(__DIR__) . '/lib.php');

        $this->assertStringContainsString('completion_config::signature($previous)', $source);
        $this->assertStringContainsString('completion_config::signature($updated)', $source);
        $this->assertStringContainsString('$synchronisemoodle = empty($data->completionunlocked);', $source);
        $this->assertStringContainsString(
            'videotrack_recalculate_all_states((int)$data->id, $cm, 0, $synchronisemoodle);',
            $source
        );
        $this->assertStringContainsString('bool $synchronisemoodle = true', $source);
    }

    /**
     * A disabled or empty acknowledgement definition cannot activate a phantom custom rule.
     */
    public function test_empty_acknowledgement_is_not_a_custom_rule(): void {
        $instance = (object)[
            'id' => 105,
            'completionpercent' => 0,
            'completionacknowledgement' => 1,
            'acknowledgementenabled' => 1,
            'acknowledgementtext' => '<p><br></p>',
            'reactionsenabled' => 0,
            'reactionsrequired' => 0,
            'minreactions' => 0,
            'requireallreactiontypes' => 0,
        ];

        $this->assertFalse(completion_config::has_custom_rules($instance, false));
    }

    /**
     * Moodle completion can evaluate acknowledgement-only legacy data even when no state row exists yet.
     */
    public function test_completion_adapters_preserve_userid_without_state_row(): void {
        $root = dirname(__DIR__);
        $lib = file_get_contents($root . '/lib.php');
        $customcompletion = file_get_contents($root . '/classes/completion/custom_completion.php');

        $this->assertStringContainsString("'userid' => (int)\$userid", $lib);
        $this->assertStringContainsString("'userid' => \$this->userid", $customcompletion);
        $this->assertStringContainsString('tracker::completion_satisfied($instance, $state,', $customcompletion);
    }

    /**
     * Moodle sort order lists the composite rule together with every standard automatic condition.
     */
    public function test_custom_completion_sort_order_covers_standard_conditions(): void {
        $source = file_get_contents(dirname(__DIR__) . '/classes/completion/custom_completion.php');

        $this->assertStringContainsString("'completionview'", $source);
        $this->assertStringContainsString("'completionusegrade'", $source);
        $this->assertStringContainsString("'completionpassgrade'", $source);
        $this->assertStringContainsString('self::RULE', $source);
    }

    /**
     * Reaction OR logic can satisfy completion as an alternative to viewing percentage.
     */
    public function test_reaction_or_logic_can_be_alternative_to_viewing_percentage(): void {
        $instance = (object)[
            'id' => 106,
            'completionpercent' => 50,
            'completionacknowledgement' => 0,
            'reactionsenabled' => 1,
            'reactionsrequired' => 0,
            'minreactions' => 0,
            'requireallreactiontypes' => 0,
            'completionlogic' => 'or',
        ];
        $summary = ['uniquecount' => 1, 'uniqueids' => [10]];

        $this->assertTrue(tracker::completion_satisfied(
            $instance,
            (object)['completionpercent' => 25, 'userid' => 7],
            $summary,
            [10, 11]
        ));
        $this->assertFalse(tracker::completion_satisfied(
            $instance,
            (object)['completionpercent' => 25, 'userid' => 7],
            ['uniquecount' => 0, 'uniqueids' => []],
            [10, 11]
        ));
        $this->assertTrue(tracker::completion_satisfied(
            $instance,
            (object)['completionpercent' => 75, 'userid' => 7],
            ['uniquecount' => 0, 'uniqueids' => []],
            [10, 11]
        ));
    }
}
