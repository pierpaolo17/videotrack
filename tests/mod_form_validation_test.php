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

use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Focused activity-form validation cases for media and reaction file controls.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\mod_videotrack_mod_form::class)]
final class mod_form_validation_test extends \advanced_testcase {
    /**
     * Media-source errors distinguish URLs, missing uploads and existing activities.
     */
    public function test_media_source_errors_preserve_required_inputs(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/videotrack/mod_form.php');

        $form = (new \ReflectionClass(\mod_videotrack_mod_form::class))->newInstanceWithoutConstructor();
        $source = new \ReflectionMethod(\mod_videotrack_mod_form::class, 'source_errors');

        $this->assertSame([
            'youtubeurl' => get_string('invalidyoutubeurl', 'mod_videotrack'),
        ], $source->invoke($form, ['videosource' => 'youtube']));
        $this->assertSame([
            'vimeourl' => get_string('invalidvimeourl', 'mod_videotrack'),
        ], $source->invoke($form, ['videosource' => 'vimeo']));
        $this->assertSame([
            'videofile' => get_string('required'),
        ], $source->invoke($form, ['videosource' => 'upload']));
        $this->assertSame([], $source->invoke($form, ['videosource' => 'upload', 'instance' => 123]));
        $this->assertSame([
            'videosource' => get_string('invalidvideosource', 'mod_videotrack'),
        ], $source->invoke($form, ['videosource' => 'unknown']));
    }

    /**
     * Enabled transcripts and chapters require distinct WebVTT draft files.
     */
    public function test_missing_timed_text_files_have_distinct_errors(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/videotrack/mod_form.php');

        $form = (new \ReflectionClass(\mod_videotrack_mod_form::class))->newInstanceWithoutConstructor();
        $timedtext = new \ReflectionMethod(\mod_videotrack_mod_form::class, 'timed_text_errors');
        $this->assertSame([
            'transcriptfiles' => get_string('err:transcriptfilerequired', 'mod_videotrack'),
            'chapterfile' => get_string('err:chapterfilerequired', 'mod_videotrack'),
        ], $timedtext->invoke($form, ['showtranscript' => 1, 'showchapters' => 1], 'youtube'));
    }

    /**
     * An enabled reaction row needs an icon file; disabled reactions do not.
     */
    public function test_reaction_file_error_depends_on_enablement(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/videotrack/mod_form.php');

        $form = (new \ReflectionClass(\mod_videotrack_mod_form::class))->newInstanceWithoutConstructor();
        $icon = new \ReflectionMethod(\mod_videotrack_mod_form::class, 'reaction_file_icon_errors');
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $this->assertSame([
            'hasicon' => false,
            'errors' => ['reactioniconfile_0' => get_string('err:reactioniconfilerequired', 'mod_videotrack')],
        ], $icon->invoke($form, [], 0, 0, $allowed, true, true));
        $this->assertSame([
            'hasicon' => false,
            'errors' => [],
        ], $icon->invoke($form, [], 0, 0, $allowed, false, true));
    }

    /**
     * Reaction rows preserve icon, label and type error order and require a complete row.
     */
    public function test_reaction_rows_preserve_error_order_and_configuration_requirement(): void {
        global $CFG;
        require_once($CFG->dirroot . '/mod/videotrack/mod_form.php');

        $form = (new \ReflectionClass(\mod_videotrack_mod_form::class))->newInstanceWithoutConstructor();
        $repeatcount = new \ReflectionProperty(\mod_videotrack_mod_form::class, 'reactionrepeatcount');
        $repeatcount->setValue($form, 4);
        $reactions = new \ReflectionMethod(\mod_videotrack_mod_form::class, 'reaction_errors');

        $errors = $reactions->invoke($form, [
            'reactionsenabled' => 1,
            'reactionicontype' => ['emoji', 'emoji', 'invalid', 'file'],
            'reactionlabel' => ['First', '', 'Third', 'Fourth'],
            'reactiondescription' => ['First description', '', '', 'Fourth description'],
            'reactioniconvalue' => ['', '🙂'],
        ]);
        $this->assertSame([
            'reactioniconvalue[0]',
            'reactionlabel[1]',
            'reactiondescription[1]',
            'reactionicontype[2]',
            'reactioniconfile_3',
            'reactionsenabled',
        ], array_keys($errors));
        $this->assertSame(get_string('err:reactionconfigurationrequired', 'mod_videotrack'), $errors['reactionsenabled']);

        $repeatcount->setValue($form, 1);
        $this->assertSame([], $reactions->invoke($form, [
            'reactionsenabled' => 1,
            'reactionicontype' => ['emoji'],
            'reactionlabel' => ['First'],
            'reactiondescription' => ['First description'],
            'reactioniconvalue' => ['🙂'],
        ]));
        $this->assertSame([], $reactions->invoke($form, [
            'reactionsenabled' => 0,
            'reactionicontype' => ['file'],
            'reactionlabel' => ['First'],
        ]));
    }
}
