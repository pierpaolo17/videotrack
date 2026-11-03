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

use mod_videotrack\local\csv_export;
use mod_videotrack\local\form_preprocessing;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural checks for activity form default preparation.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(form_preprocessing::class)]
final class form_preprocessing_test extends \advanced_testcase {
    /**
     * Completion suffixes, acknowledgement editor and Vimeo URL retain their defaults.
     */
    public function test_completion_defaults_preserve_edit_values(): void {
        $values = [
            'acknowledgementtext' => '<p>Read</p>',
            'completionpercent' => 70,
            'completionlogic' => 'or',
            'videosource' => 'vimeo',
            'videourl' => 'https://vimeo.com/123',
        ];
        form_preprocessing::completion_defaults($values, 'custom', 0);

        $this->assertSame(['text' => '<p>Read</p>', 'format' => (int)FORMAT_HTML], $values['acknowledgement_editor']);
        $this->assertSame(70, $values['completionpercentcustom']);
        $this->assertSame(0, $values['completionacknowledgementcustom']);
        $this->assertSame('or', $values['completionlogiccustom']);
        $this->assertSame('https://vimeo.com/123', $values['vimeourl']);

        $empty = [];
        form_preprocessing::completion_defaults($empty, 'custom', 0);
        $this->assertSame(0, $empty['completionpercentcustom']);
        $this->assertSame('and', $empty['completionlogiccustom']);
    }

    /**
     * An existing notice editor takes precedence, while presets are always prepared.
     */
    public function test_reaction_notice_defaults_preserve_editors_and_presets(): void {
        $values = [
            'reactionnotice' => 'Saved notice',
            'reactionnoticeformat' => FORMAT_HTML,
        ];
        form_preprocessing::reaction_notice_defaults($values);
        $this->assertSame([
            'text' => 'Saved notice',
            'format' => FORMAT_HTML,
        ], $values['reactionnotice_editor']);
        $this->assertIsArray(json_decode($values['reactionpreset_json'], true));

        $existing = [
            'reactionnotice' => 'Saved notice',
            'reactionnotice_editor' => ['text' => 'Unsubmitted edit', 'format' => FORMAT_HTML],
        ];
        form_preprocessing::reaction_notice_defaults($existing);
        $this->assertSame('Unsubmitted edit', $existing['reactionnotice_editor']['text']);
        $this->assertIsArray(json_decode($existing['reactionpreset_json'], true));
    }

    /**
     * Explicit playback settings select only their speed and control checkboxes.
     */
    public function test_playback_defaults_preserve_selected_controls(): void {
        $values = ['playbackspeeds' => '0.25,1,2', 'html5controls' => 'play,download'];
        form_preprocessing::playback_defaults($values);

        $this->assertSame('0.25', $values['playbackspeed_0.25']);
        $this->assertSame('2', $values['playbackspeed_2']);
        $this->assertSame(0, $values['playbackspeed_3']);
        $this->assertSame('play', $values['html5ctrl_play']);
        $this->assertSame('download', $values['html5ctrl_download']);
        $this->assertSame(0, $values['html5ctrl_mute']);
    }

    /**
     * CSV field selections and the explicit none sentinel keep distinct defaults.
     */
    public function test_csv_defaults_respect_explicit_and_empty_field_sets(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $values = ['csvexportfields' => 'coursefullname,email', 'csvdelimiter' => csv_export::DELIMITER_COMMA];
        form_preprocessing::csv_defaults($values, $context);

        $this->assertSame(1, $values[csv_export::form_element_name('coursefullname')]);
        $this->assertSame(1, $values[csv_export::form_element_name('email')]);
        $this->assertSame(0, $values[csv_export::form_element_name('courseshortname')]);
        $this->assertSame(csv_export::DELIMITER_COMMA, $values['csvdelimiter']);

        $none = ['csvexportfields' => 'none'];
        form_preprocessing::csv_defaults($none, $context);
        $this->assertSame(0, $none[csv_export::form_element_name('email')]);
        $this->assertSame(csv_export::DELIMITER_INHERIT, $none['csvdelimiter']);
    }
}
