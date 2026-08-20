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

namespace mod_videotrack;

use mod_videotrack\local\acknowledgement;
use mod_videotrack\local\form_validation;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural coverage for autonomous activity-form validation policy.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(form_validation::class)]
final class form_validation_test extends \advanced_testcase {
    /**
     * Scalar validation preserves completion, player, seek-step, reaction and preset rules.
     */
    public function test_scalar_settings_errors_preserve_existing_rules(): void {
        $errors = form_validation::scalar_settings_errors([
            'completionpercentcustom' => 101,
            'playerwidth' => 4097,
            'rewindstep' => -1,
            'fastforwardstep' => 301,
            'reactionsrequired' => 1,
            'minreactions' => 0,
            'requireallreactiontypes' => 0,
            'reactionpreset_json' => '{broken',
        ], 'custom');

        $this->assertSame([
            'completionpercentgroupcustom',
            'playerwidth',
            'rewindstep',
            'fastforwardstep',
            'minreactions',
            'reactionpreset',
        ], array_keys($errors));

        $this->assertSame([], form_validation::scalar_settings_errors([
            'completionpercentcustom' => 100,
            'playerwidth' => 4096,
            'rewindstep' => 0,
            'fastforwardstep' => 300,
            'reactionsrequired' => 1,
            'minreactions' => 0,
            'requireallreactiontypes' => 1,
            'reactionpreset_json' => '{"preset":[]}',
        ], 'custom'));
    }

    /**
     * Acknowledgement validation preserves timing and visible-statement requirements.
     */
    public function test_acknowledgement_errors_preserve_existing_rules(): void {
        $errors = form_validation::acknowledgement_errors([
            'acknowledgementenabled' => 1,
            'acknowledgementtiming' => 999,
            'acknowledgement_editor' => ['text' => '<p> </p>'],
        ]);

        $this->assertSame(['acknowledgementtiming', 'acknowledgement_editor'], array_keys($errors));
        $this->assertSame([], form_validation::acknowledgement_errors([
            'acknowledgementenabled' => 1,
            'acknowledgementtiming' => acknowledgement::TIMING_VIDEO_END,
            'acknowledgement_editor' => ['text' => '<p>Read and understood</p>'],
        ]));
        $this->assertSame([], form_validation::acknowledgement_errors([]));
    }

    /**
     * Duration validation preserves range and video-end dependency semantics.
     */
    public function test_duration_errors_preserve_existing_rules(): void {
        $this->assertArrayHasKey('durationseconds', form_validation::duration_errors([
            'durationseconds' => 86401,
        ]));
        $this->assertArrayHasKey('durationseconds', form_validation::duration_errors([
            'acknowledgementenabled' => 1,
            'acknowledgementtiming' => acknowledgement::TIMING_VIDEO_END,
            'durationseconds' => 0,
        ]));
        $this->assertSame([], form_validation::duration_errors([
            'acknowledgementenabled' => 1,
            'acknowledgementtiming' => acknowledgement::TIMING_VIDEO_END,
            'durationseconds' => 120.5,
        ]));
    }
    /**
     * mod_form delegates only autonomous policy and keeps contextual validation local.
     */
    public function test_mod_form_delegates_autonomous_validation_policy(): void {
        $source = file_get_contents(dirname(__DIR__) . '/mod_form.php');
        $this->assertIsString($source);

        $this->assertStringContainsString('form_validation::scalar_settings_errors(', $source);
        $this->assertStringContainsString('form_validation::acknowledgement_errors(', $source);
        $this->assertStringContainsString('form_validation::duration_errors(', $source);
        $this->assertStringNotContainsString('$playerwidth < 0 || $playerwidth > 4096', $source);
        $this->assertStringNotContainsString("json_decode((string)\$data['reactionpreset_json']", $source);
        $this->assertStringContainsString('file_get_draft_area_info(', $source);
        $this->assertStringContainsString('videotrack_is_compatible_forum(', $source);
    }

}
