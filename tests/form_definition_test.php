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
use mod_videotrack\local\form_definition;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural checks for delegated duration, player/timed-text, CSV, Forum, integrity and acknowledgement groups.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(form_definition::class)]
final class form_definition_test extends \advanced_testcase {
    /**
     * Load the real QuickForm and the shared VideoTrack Forum queries.
     */
    protected function setUp(): void {
        global $CFG;

        parent::setUp();
        require_once($CFG->libdir . '/formslib.php');
        require_once(__DIR__ . '/../locallib.php');
        require_once(__DIR__ . '/../lib.php');
    }

    /**
     * The manual duration field retains its zero default, decimal values and accessible status descriptions.
     */
    public function test_duration_fields_keep_defaults_decimal_values_and_status_descriptions(): void {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();
        $PAGE = new \moodle_page();
        $PAGE->set_context(\context_system::instance());
        $form = new \MoodleQuickForm('videotrack_duration_fields', 'post', '');

        form_definition::add_duration_elements($form, $PAGE);

        $duration = $form->getElement('durationseconds');
        $this->assertSame(0.0, (float)$duration->getValue());
        $this->assertFalse($duration->isFrozen());
        $this->assertSame(
            'videotrack-durationseconds-auto-note videotrack-durationseconds-human',
            $duration->getAttribute('aria-describedby')
        );
        $statusfields = [
            'durationseconds_auto_note' => 'videotrack-durationseconds-auto-note',
            'durationseconds_human' => 'videotrack-durationseconds-human',
        ];
        foreach ($statusfields as $field => $id) {
            $html = $form->getElement($field)->toHtml();
            $this->assertStringContainsString('id="' . $id . '"', $html);
            $this->assertStringContainsString('role="status"', $html);
            $this->assertStringContainsString('aria-live="polite"', $html);
        }
        $human = $form->getElement('durationseconds_human')->toHtml();
        $this->assertStringContainsString('aria-atomic="true"', $human);
        $this->assertStringContainsString(get_string('durationseconds_human', 'mod_videotrack', '00:00:00'), $human);
        $form->setDefaults(['durationseconds' => '123.456']);
        $this->assertSame(123.456, (float)$duration->getValue());
    }

    /**
     * The rendered DOM configuration and AMD init share one identifier on the supplied page.
     */
    public function test_duration_dom_config_matches_the_supplied_page_amd_init(): void {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();
        $PAGE = new \moodle_page();
        $PAGE->set_context(\context_system::instance());
        $page = new \moodle_page();
        $page->set_context(\context_system::instance());
        $form = new \MoodleQuickForm('videotrack_duration_config', 'post', '');

        form_definition::add_duration_elements($form, $page);

        $scripts = [];
        $elements = (new \ReflectionProperty($form, '_elements'))->getValue($form);
        foreach ($elements as $element) {
            if ($element->getType() === 'html') {
                $scripts[] = $element->toHtml();
            }
        }
        $this->assertCount(1, $scripts);
        $document = new \DOMDocument();
        $this->assertTrue($document->loadXML(trim($scripts[0])));
        $script = $document->documentElement;
        $this->assertSame('script', $script->nodeName);
        $this->assertSame('application/json', $script->getAttribute('type'));
        $this->assertSame('videotrack-duration-config', $script->getAttribute('id'));
        $config = json_decode($script->textContent, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame([
            'sourceid' => 'id_videosource',
            'youtubeid' => 'id_youtubeurl',
            'vimeoid' => 'id_vimeourl',
            'fileid' => 'id_videofile',
            'durationid' => 'id_durationseconds',
            'noteid' => 'videotrack-durationseconds-auto-note',
            'humanid' => 'videotrack-durationseconds-human',
            'maximum' => 86400,
            'messages' => [
                'idle' => get_string('durationseconds_auto_note', 'mod_videotrack'),
                'detecting' => get_string('durationseconds_auto_detecting', 'mod_videotrack'),
                'success' => get_string('durationseconds_auto_success', 'mod_videotrack', '__VIDEOTRACK_DURATION__'),
                'manual' => get_string('durationseconds_auto_manual', 'mod_videotrack', '__VIDEOTRACK_DURATION__'),
                'unavailable' => get_string('durationseconds_auto_unavailable', 'mod_videotrack'),
                'human' => get_string('durationseconds_human', 'mod_videotrack', '__VIDEOTRACK_DURATION_HUMAN__'),
            ],
        ], $config);
        $endcode = $page->requires->get_end_code();
        $this->assertStringContainsString("require(['mod_videotrack/form/duration']", $endcode);
        $this->assertStringContainsString(
            'amd.init(' . json_encode(['configid' => $script->getAttribute('id')]) . ');',
            $endcode
        );
        $this->assertStringNotContainsString('mod_videotrack/form/duration', $PAGE->requires->get_end_code());
    }

    /**
     * Caption defaults and provider dependencies preserve false, empty, zero and configured values.
     */
    public function test_caption_defaults_and_provider_dependencies_are_preserved(): void {
        global $PAGE;

        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $PAGE = new \moodle_page();
        $PAGE->set_course($course);
        $PAGE->set_context($context);
        $cases = [
            [null, null, 0, ''],
            ['', '', 0, ''],
            ['0', '0', 0, '0'],
            ['1', 'it', 1, 'it'],
        ];
        foreach ($cases as $index => [$enabled, $language, $expectedenabled, $expectedlanguage]) {
            if ($enabled === null) {
                unset_config('default_captions', 'mod_videotrack');
                unset_config('default_captionslang', 'mod_videotrack');
            } else {
                set_config('default_captions', $enabled, 'mod_videotrack');
                set_config('default_captionslang', $language, 'mod_videotrack');
            }
            $form = new \MoodleQuickForm('videotrack_caption_defaults_' . $index, 'post', '');

            form_definition::add_caption_elements($form, $context);

            $this->assertSame($expectedenabled, (int)$form->getElement('captions')->getValue());
            $this->assertSame($expectedlanguage, (string)$form->getElement('captionslang')->getValue());
            $this->assertFalse($form->getElement('captions')->isFrozen());
            $this->assertFalse($form->getElement('captionslang')->isFrozen());
            $this->assertSame(0, (int)$form->getElement('showtranscript')->getValue());
            $this->assertSame(0, (int)$form->getElement('showchapters')->getValue());
            $this->assertTrue($form->elementExists('captionsheader'));
            $dependencies = (new \ReflectionProperty($form, '_dependencies'))->getValue($form);
            $this->assertSame(['captions' => ['notchecked' => ['1' => ['captionslang']]]], $dependencies);
            $hideifs = (new \ReflectionProperty($form, '_hideifs'))->getValue($form);
            $this->assertSame([
                'videosource' => [
                    'neq' => ['upload' => ['vttfile', 'vttfile_notice'], 'vimeo' => ['vimeo_captions_notice']],
                    'eq' => ['upload' => ['transcriptfiles_provider_notice']],
                ],
                'captions' => ['notchecked' => ['1' => ['vttfile', 'vttfile_notice', 'vimeo_captions_notice']]],
            ], $hideifs);
        }
    }

    /**
     * Real QuickForm upload elements retain WebVTT types, transcript count and effective size limits.
     */
    public function test_caption_file_options_keep_webvtt_limits_for_a_normal_user(): void {
        global $PAGE;

        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $context = \context_course::instance($course->id);
        $this->setUser($generator->create_user());
        $PAGE = new \moodle_page();
        $PAGE->set_course($course);
        $PAGE->set_context($context);
        $this->assertFalse(has_capability('moodle/course:ignorefilesizelimits', $context));
        $form = new \MoodleQuickForm('videotrack_caption_file_options', 'post', '');

        form_definition::add_caption_elements($form, $context);

        foreach (['vttfile', 'transcriptfiles', 'chapterfile'] as $field) {
            $element = $form->getElement($field);
            $options = (new \ReflectionProperty($element, '_options'))->getValue($element);
            $this->assertSame(['.vtt'], $options['accepted_types']);
            $this->assertFalse($element->isFrozen());
            if ($field === 'transcriptfiles') {
                $this->assertSame(10, $options['maxfiles']);
                $this->assertFalse($options['subdirs']);
            } else {
                $this->assertSame(FILE_INTERNAL, $options['return_types']);
            }
            if ($field !== 'vttfile') {
                $this->assertGreaterThan(0, $options['maxbytes']);
                $this->assertLessThanOrEqual(1048576, $options['maxbytes']);
            }
        }
    }

    /**
     * The original course permission freezes only captions and language, leaving timed-text tools editable.
     */
    public function test_caption_override_permissions_are_course_scoped(): void {
        global $PAGE;

        $this->resetAfterTest();
        set_config('default_captions', 1, 'mod_videotrack');
        set_config('default_captionslang', 'en', 'mod_videotrack');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $context = \context_course::instance($course->id);
        $othercontext = \context_course::instance($othercourse->id);
        $user = $generator->create_user();
        $roleid = create_role('VideoTrack caption editor', 'videotrackcaptioneditor', '');
        role_assign($roleid, $user->id, $context->id);
        assign_capability('mod/videotrack:overrideplayersettings', CAP_ALLOW, $roleid, $context->id);
        $this->setUser($user);

        $this->assertTrue(has_capability('mod/videotrack:overrideplayersettings', $context));
        $this->assertFalse(has_capability('mod/videotrack:overrideplayersettings', $othercontext));
        foreach ([$context, $othercontext] as $scope) {
            $PAGE = new \moodle_page();
            $PAGE->set_course($scope->id === $context->id ? $course : $othercourse);
            $PAGE->set_context($scope);
            $form = new \MoodleQuickForm('videotrack_caption_permissions_' . $scope->id, 'post', '');

            form_definition::add_caption_elements($form, $scope);

            foreach (['captions', 'captionslang'] as $field) {
                $this->assertSame($scope->id !== $context->id, $form->getElement($field)->isFrozen());
            }
            $this->assertSame(1, (int)$form->getElement('captions')->getValue());
            $this->assertSame('en', (string)$form->getElement('captionslang')->getValue());
            foreach (['vttfile', 'transcriptfiles', 'chapterfile', 'showtranscript', 'showchapters'] as $field) {
                $this->assertFalse($form->getElement($field)->isFrozen());
            }
        }
    }

    /**
     * Player behaviour keeps site boolean defaults, inherited numeric values and editable saved values.
     */
    public function test_player_behavior_keeps_defaults_inheritance_and_editable_values(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('default_autoplay', 1, 'mod_videotrack');
        set_config('default_loop', 0, 'mod_videotrack');
        set_config('default_startmuted', 1, 'mod_videotrack');
        set_config('default_allowdownload', 0, 'mod_videotrack');
        set_config('playerwidth', 1280, 'mod_videotrack');
        set_config('rewindstep', 10, 'mod_videotrack');
        set_config('fastforwardstep', 30, 'mod_videotrack');
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $form = new \MoodleQuickForm('videotrack_player_behavior_defaults', 'post', '');

        form_definition::add_player_behavior_elements($form, $context);

        $defaults = [
            'playerwidth' => 0, 'autoplay' => 1, 'loopenabled' => 0, 'startmuted' => 1,
            'rewindstep' => 0, 'fastforwardstep' => 0, 'allowdownload' => 0,
        ];
        foreach ($defaults as $field => $value) {
            $this->assertSame($value, (int)$form->getElement($field)->getValue());
            $this->assertFalse($form->getElement($field)->isFrozen());
        }
        $this->assertTrue($form->elementExists('playerbehaviorheader'));
        $this->assertStringContainsString(
            get_string('playerwidth_zero_note', 'mod_videotrack'),
            $form->getElement('playerwidthnote')->toHtml()
        );
        $this->assertSame('id_playerwidthnote', $form->getElement('playerwidth')->getAttribute('aria-describedby'));
        $hideifs = (new \ReflectionProperty($form, '_hideifs'))->getValue($form);
        $this->assertSame(['allowdownload'], $hideifs['videosource']['neq']['upload']);

        $saved = ['playerwidth' => 960, 'rewindstep' => 5, 'fastforwardstep' => 15, 'autoplay' => 0, 'loopenabled' => 1];
        $form->setDefaults($saved);
        foreach ($saved as $field => $value) {
            $this->assertSame($value, (int)$form->getElement($field)->getValue());
        }
    }

    /**
     * Missing, empty and explicit zero site values retain disabled boolean defaults.
     */
    public function test_player_behavior_defaults_preserve_missing_empty_and_zero(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $settings = ['default_autoplay', 'default_loop', 'default_startmuted', 'default_allowdownload'];
        foreach ([null, '', '0'] as $index => $value) {
            foreach ($settings as $setting) {
                if ($value === null) {
                    unset_config($setting, 'mod_videotrack');
                } else {
                    set_config($setting, $value, 'mod_videotrack');
                }
            }
            $form = new \MoodleQuickForm('videotrack_player_behavior_fallback_' . $index, 'post', '');

            form_definition::add_player_behavior_elements($form, $context);

            foreach (['autoplay', 'loopenabled', 'startmuted', 'allowdownload'] as $field) {
                $this->assertSame(0, (int)$form->getElement($field)->getValue());
            }
        }
    }

    /**
     * Course override permissions control all seven fields and the editable width description.
     */
    public function test_player_behavior_respects_course_override_permissions(): void {
        $this->resetAfterTest();
        set_config('default_autoplay', 1, 'mod_videotrack');
        set_config('default_loop', 0, 'mod_videotrack');
        set_config('default_startmuted', 0, 'mod_videotrack');
        set_config('default_allowdownload', 1, 'mod_videotrack');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $context = \context_course::instance($course->id);
        $othercontext = \context_course::instance($othercourse->id);
        $user = $generator->create_user();
        $roleid = create_role('VideoTrack behaviour editor', 'videotrackbehavioreditor', '');
        role_assign($roleid, $user->id, $context->id);
        assign_capability('mod/videotrack:overrideplayersettings', CAP_ALLOW, $roleid, $context->id);
        $this->setUser($user);

        $this->assertTrue(has_capability('mod/videotrack:overrideplayersettings', $context));
        $this->assertFalse(has_capability('mod/videotrack:overrideplayersettings', $othercontext));
        foreach ([$context, $othercontext] as $scope) {
            $form = new \MoodleQuickForm('videotrack_player_behavior_permissions_' . $scope->id, 'post', '');

            form_definition::add_player_behavior_elements($form, $scope);

            $defaults = [
                'playerwidth' => 0, 'autoplay' => 1, 'loopenabled' => 0, 'startmuted' => 0,
                'rewindstep' => 0, 'fastforwardstep' => 0, 'allowdownload' => 1,
            ];
            foreach ($defaults as $field => $value) {
                $element = $form->getElement($field);
                $this->assertSame($scope->id !== $context->id, $element->isFrozen());
                $this->assertSame($value, (int)$element->getValue());
            }
            $description = $form->getElement('playerwidth')->getAttribute('aria-describedby');
            if ($scope->id === $context->id) {
                $this->assertSame('id_playerwidthnote', $description);
            } else {
                $this->assertEmpty($description);
            }
        }
    }

    /**
     * Only site-permitted choices are offered, in canonical order, with flat checkbox values.
     */
    public function test_playback_choices_keep_site_scope_order_and_flat_values(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('html5controls', 'download,play,speed,unsupported,play', 'mod_videotrack');
        set_config('playbackspeeds', '4,0.25,2,1.25,unsupported,1', 'mod_videotrack');
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $form = new \MoodleQuickForm('videotrack_playback_choices', 'post', '');

        form_definition::add_html5_control_elements($form, $context);
        form_definition::add_playback_speed_elements($form, $context);

        $expected = [
            'html5controlsgroup' => ['html5ctrl_play', 'html5ctrl_speed', 'html5ctrl_download'],
            'playbackspeedsgroup' => [
                'playbackspeed_0.25', 'playbackspeed_1', 'playbackspeed_1.25', 'playbackspeed_2', 'playbackspeed_4',
            ],
        ];
        $form->setDefaults(['html5ctrl_download' => 'download', 'playbackspeed_1.25' => '1.25']);
        foreach ($expected as $groupname => $names) {
            $group = $form->getElement($groupname);
            $elements = $group->getElements();
            $this->assertFalse($group->isFrozen());
            $this->assertSame($names, array_map(static fn($element): string => $element->getName(), $elements));
            foreach ($elements as $element) {
                $name = $element->getName();
                $selected = ['html5ctrl_download' => 'download', 'playbackspeed_1.25' => '1.25'];
                $this->assertSame((string)($selected[$name] ?? 0), (string)$element->getValue());
                $this->assertStringContainsString('name="' . $name . '"', $element->toHtml());
            }
        }
        $hideifs = (new \ReflectionProperty($form, '_hideifs'))->getValue($form);
        $this->assertSame([
            'html5controlsheader', 'html5controls_desc', 'html5ctrl_play', 'html5ctrl_speed',
            'html5ctrl_download', 'html5controlsgroup',
        ], $hideifs['videosource']['neq']['upload']);
    }

    /**
     * The course capability unlocks both choice groups only in the authorised course.
     */
    public function test_playback_choices_respect_course_override_permissions(): void {
        $this->resetAfterTest();
        set_config('html5controls', 'play,speed', 'mod_videotrack');
        set_config('playbackspeeds', '1,1.5', 'mod_videotrack');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $context = \context_course::instance($course->id);
        $othercontext = \context_course::instance($othercourse->id);
        $user = $generator->create_user();
        $roleid = create_role('VideoTrack player settings editor', 'videotrackplayersettingseditor', '');
        role_assign($roleid, $user->id, $context->id);
        assign_capability('mod/videotrack:overrideplayersettings', CAP_ALLOW, $roleid, $context->id);
        $this->setUser($user);

        $this->assertTrue(has_capability('mod/videotrack:overrideplayersettings', $context));
        $this->assertFalse(has_capability('mod/videotrack:overrideplayersettings', $othercontext));
        foreach ([$context, $othercontext] as $scope) {
            $form = new \MoodleQuickForm('videotrack_playback_permissions_' . $scope->id, 'post', '');
            form_definition::add_html5_control_elements($form, $scope);
            form_definition::add_playback_speed_elements($form, $scope);
            foreach (['html5controlsgroup', 'playbackspeedsgroup'] as $groupname) {
                $group = $form->getElement($groupname);
                $this->assertSame($scope->id !== $context->id, $group->isFrozen());
                foreach ($group->getElements() as $element) {
                    $this->assertSame($scope->id !== $context->id, $element->isFrozen());
                }
            }
        }
    }

    /**
     * Missing site configuration keeps defaults; unsupported choices cannot create extra controls.
     */
    public function test_playback_choices_keep_site_fallback_and_empty_groups(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        unset_config('html5controls', 'mod_videotrack');
        unset_config('playbackspeeds', 'mod_videotrack');
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $form = new \MoodleQuickForm('videotrack_playback_defaults', 'post', '');

        form_definition::add_html5_control_elements($form, $context);
        form_definition::add_playback_speed_elements($form, $context);

        $controls = $form->getElement('html5controlsgroup')->getElements();
        $speeds = $form->getElement('playbackspeedsgroup')->getElements();
        $this->assertSame([
            'html5ctrl_play', 'html5ctrl_progress', 'html5ctrl_current', 'html5ctrl_duration',
            'html5ctrl_mute', 'html5ctrl_volume', 'html5ctrl_speed', 'html5ctrl_fullscreen',
        ], array_map(static fn($element): string => $element->getName(), $controls));
        $this->assertSame([
            'playbackspeed_0.75', 'playbackspeed_1', 'playbackspeed_1.25', 'playbackspeed_1.5', 'playbackspeed_2',
        ], array_map(static fn($element): string => $element->getName(), $speeds));

        set_config('html5controls', 'unsupported', 'mod_videotrack');
        set_config('playbackspeeds', '1.1', 'mod_videotrack');
        $empty = new \MoodleQuickForm('videotrack_playback_empty', 'post', '');
        form_definition::add_html5_control_elements($empty, $context);
        form_definition::add_playback_speed_elements($empty, $context);
        $omittedgroups = [
            'html5controlsheader', 'html5controls_desc', 'html5controlsgroup',
            'playbackspeedsheader', 'playbackspeeds_desc', 'playbackspeedsgroup',
        ];
        foreach ($omittedgroups as $name) {
            $this->assertFalse($empty->elementExists($name));
        }
    }

    /**
     * New activities cannot enable posting when no compatible Forum exists.
     */
    public function test_empty_course_disables_posting_and_freezes_destination(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $form = new \MoodleQuickForm('videotrack_forum_empty', 'post', '');

        form_definition::add_forum_elements($form, (int)$course->id, null);

        $this->assertSame('disabled', $form->getElement('forumpostingenabled')->getAttribute('disabled'));
        $this->assertTrue($form->getElement('linkedforumid')->isFrozen());
        $this->assertTrue($form->elementExists('forumnoneavailable'));
        $this->assertTrue($form->elementExists('forumsubjecttemplate'));
        $this->assertTrue($form->elementExists('forumprivacyinfo'));
    }

    /**
     * Choices keep course/type/deletion filters, ordering and hidden-module labels.
     */
    public function test_choices_keep_course_scope_and_module_status(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $first = $generator->create_module('forum', ['course' => $course->id, 'type' => 'qanda', 'name' => 'Alpha']);
        $hidden = $generator->create_module('forum', [
            'course' => $course->id, 'type' => 'general', 'name' => 'Beta', 'visible' => 0,
        ]);
        $incompatible = $generator->create_module('forum', ['course' => $course->id, 'type' => 'single']);
        $other = $generator->create_module('forum', ['course' => $othercourse->id, 'type' => 'general']);
        $deleting = $generator->create_module('forum', ['course' => $course->id, 'type' => 'general']);
        $DB->set_field('course_modules', 'deletioninprogress', 1, ['id' => $deleting->cmid]);
        $form = new \MoodleQuickForm('videotrack_forum_choices', 'post', '');

        form_definition::add_forum_elements($form, (int)$course->id, (object)[]);

        $destination = $form->getElement('linkedforumid');
        $html = $destination->toHtml();
        $this->assertFalse($destination->isFrozen());
        $this->assertEmpty($form->getElement('forumpostingenabled')->getAttribute('disabled'));
        $this->assertFalse($form->elementExists('forumnoneavailable'));
        $this->assertStringContainsString('value="0"', $html);
        $this->assertStringContainsString('value="' . $first->id . '"', $html);
        $this->assertStringContainsString('value="' . $hidden->id . '"', $html);
        $this->assertStringContainsString('Beta (' . get_string('forum:statushidden', 'mod_videotrack') . ')', $html);
        foreach ([$incompatible, $other, $deleting] as $excluded) {
            $this->assertStringNotContainsString('value="' . $excluded->id . '"', $html);
        }
        $this->assertLessThan(
            strpos($html, 'value="' . $hidden->id . '"'),
            strpos($html, 'value="' . $first->id . '"')
        );
    }

    /**
     * An unavailable saved destination remains explicit without importing another course's choices.
     */
    public function test_unavailable_saved_forum_remains_an_editable_placeholder(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $saved = $generator->create_module('forum', [
            'course' => $othercourse->id, 'type' => 'general', 'name' => 'Other course private label',
        ]);
        $form = new \MoodleQuickForm('videotrack_forum_saved', 'post', '');

        form_definition::add_forum_elements($form, (int)$course->id, (object)['linkedforumid' => $saved->id]);

        $destination = $form->getElement('linkedforumid');
        $html = $destination->toHtml();
        $this->assertFalse($destination->isFrozen());
        $this->assertEmpty($form->getElement('forumpostingenabled')->getAttribute('disabled'));
        $this->assertFalse($form->elementExists('forumnoneavailable'));
        $this->assertStringContainsString('value="' . $saved->id . '"', $html);
        $this->assertStringContainsString(get_string('forum:configuredunavailable', 'mod_videotrack'), $html);
        $this->assertStringNotContainsString('Other course private label', $html);
    }

    /**
     * Acknowledgement choices and editor options retain the actual activity context.
     */
    public function test_acknowledgement_fields_keep_context_and_safe_editor_options(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $forum = $this->getDataGenerator()->create_module('forum', ['course' => $course->id]);
        $contexts = [
            \context_course::instance($course->id),
            \context_module::instance($forum->cmid),
        ];
        $timings = [
            \mod_videotrack\local\acknowledgement::TIMING_ANYTIME,
            \mod_videotrack\local\acknowledgement::TIMING_VIDEO_END,
        ];
        foreach ($contexts as $context) {
            $form = new \MoodleQuickForm('videotrack_acknowledgement_' . $context->id, 'post', '');

            form_definition::add_acknowledgement_elements($form, $context);

            $this->assertTrue($form->elementExists('acknowledgementheader'));
            $this->assertTrue($form->elementExists('acknowledgementenabled'));
            $html = $form->getElement('acknowledgementtiming')->toHtml();
            foreach ($timings as $timing) {
                $this->assertStringContainsString('value="' . $timing . '"', $html);
            }
            $editor = $form->getElement('acknowledgement_editor');
            $options = (new \ReflectionProperty($editor, '_options'))->getValue($editor);
            $this->assertSame($context, $options['context']);
            $this->assertSame(0, $options['maxfiles']);
            $this->assertSame(0, $options['maxbytes']);
            $this->assertFalse($options['trusttext']);
            $this->assertFalse($options['noclean']);
        }
    }

    /**
     * CSV choices retain delimiter inheritance, flat field names and visible field order.
     */
    public function test_csv_fields_keep_delimiters_names_and_order(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('showuseridentity', 'email,idnumber');
        set_config('hiddenuserfields', '');
        $course = $this->getDataGenerator()->create_course();
        $context = \context_course::instance($course->id);
        $form = new \MoodleQuickForm('videotrack_csv_fields', 'post', '');

        form_definition::add_csv_elements($form, $context);

        $delimiter = $form->getElement('csvdelimiter');
        $this->assertSame([csv_export::DELIMITER_INHERIT], $delimiter->getSelected());
        $html = $delimiter->toHtml();
        foreach (['inherit', 'comma', 'semicolon', 'section', 'hash', 'pipe'] as $value) {
            $this->assertStringContainsString('value="' . $value . '"', $html);
        }
        $fields = [
            'coursefullname', 'courseshortname', 'courseid', 'instancename', 'videolink',
            'username', 'email', 'city', 'country', 'idnumber', 'institution', 'department',
            'phone1', 'phone2', 'address',
        ];
        $checkboxes = $form->getElement('csvexportfieldsgroup')->getElements();
        $names = array_map(static fn($checkbox): string => $checkbox->getName(), $checkboxes);
        $this->assertSame(array_map([csv_export::class, 'form_element_name'], $fields), $names);
        $this->assertTrue($form->elementExists('csvexportheader'));
        $this->assertTrue($form->elementExists('csvexportfields_desc'));
    }

    /**
     * The actual context and hidden-identity permissions control enabled CSV checkboxes.
     */
    public function test_csv_identity_fields_respect_context_and_hidden_field_permissions(): void {
        $this->resetAfterTest();
        set_config('showuseridentity', 'email,idnumber');
        set_config('hiddenuserfields', 'idnumber');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $othercourse = $generator->create_course();
        $context = \context_course::instance($course->id);
        $othercontext = \context_course::instance($othercourse->id);
        $user = $generator->create_user();
        $roleid = create_role('VideoTrack CSV identity viewer', 'videotrackcsvidentityviewer', '');
        role_assign($roleid, $user->id, $context->id);
        assign_capability('moodle/site:viewuseridentity', CAP_ALLOW, $roleid, $context->id);
        assign_capability('moodle/course:viewhiddenuserfields', CAP_PREVENT, $roleid, $context->id);
        $this->setUser($user);

        $this->assertTrue(has_capability('moodle/site:viewuseridentity', $context));
        $this->assertFalse(has_capability('moodle/site:viewuseridentity', $othercontext));
        foreach ([$context, $othercontext] as $scope) {
            $form = new \MoodleQuickForm('videotrack_csv_permissions_' . $scope->id, 'post', '');
            form_definition::add_csv_elements($form, $scope);
            $checkboxes = [];
            foreach ($form->getElement('csvexportfieldsgroup')->getElements() as $checkbox) {
                $checkboxes[$checkbox->getName()] = $checkbox;
            }
            $email = $checkboxes[csv_export::form_element_name('email')];
            if ($scope->id === $context->id) {
                $this->assertEmpty($email->getAttribute('disabled'));
            } else {
                $this->assertSame('disabled', $email->getAttribute('disabled'));
            }
            foreach (['idnumber', 'phone1'] as $field) {
                $element = $checkboxes[csv_export::form_element_name($field)];
                $this->assertSame('disabled', $element->getAttribute('disabled'));
                $this->assertStringContainsString(get_string('notavailable'), $element->toHtml());
            }
            $coursefield = $checkboxes[csv_export::form_element_name('coursefullname')];
            $this->assertEmpty($coursefield->getAttribute('disabled'));
        }
    }

    /**
     * Integrity options start disabled and show the default accessible site policy.
     */
    public function test_integrity_fields_keep_disabled_defaults_and_accessible_policy(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        foreach (['randompauseminseconds', 'randompausemaxseconds', 'focuslosspolicy', 'focuslossgraceseconds'] as $setting) {
            unset_config($setting, 'mod_videotrack');
        }
        $form = new \MoodleQuickForm('videotrack_integrity_defaults', 'post', '');

        form_definition::add_integrity_elements($form);

        $this->assertTrue($form->elementExists('integritysettingsheader'));
        $this->assertTrue($form->elementExists('integritysettingsintro'));
        foreach (['integrityindicatorsenabled', 'pauseonfocusloss', 'preventpictureinpicture', 'randomfocuspauses'] as $field) {
            $this->assertSame(0, (int)$form->getElement($field)->getValue());
        }
        $expected = get_string('integrity:sitepolicy_desc', 'mod_videotrack', (object)[
            'minimum' => 300,
            'maximum' => 1800,
            'policy' => get_string('setting:focuslosspolicy_hiddenonly', 'mod_videotrack'),
            'grace' => 5,
        ]);
        $this->assertStringContainsString($expected, $form->getElement('integritysitepolicy')->toHtml());
    }

    /**
     * The displayed policy uses canonical bounds, grace limits and policy fallback.
     */
    public function test_integrity_summary_uses_effective_site_settings(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $cases = [
            [1800, 300, 'strict', 12, 300, 1800, 'setting:focuslosspolicy_strict', 12],
            [1, 99999, 'invalid', 999, 60, 7200, 'setting:focuslosspolicy_hiddenonly', 30],
        ];
        foreach ($cases as $index => [$minimum, $maximum, $policy, $grace, $low, $high, $label, $effectivegrace]) {
            set_config('randompauseminseconds', $minimum, 'mod_videotrack');
            set_config('randompausemaxseconds', $maximum, 'mod_videotrack');
            set_config('focuslosspolicy', $policy, 'mod_videotrack');
            set_config('focuslossgraceseconds', $grace, 'mod_videotrack');
            $form = new \MoodleQuickForm('videotrack_integrity_policy_' . $index, 'post', '');

            form_definition::add_integrity_elements($form);

            $expected = get_string('integrity:sitepolicy_desc', 'mod_videotrack', (object)[
                'minimum' => $low,
                'maximum' => $high,
                'policy' => get_string($label, 'mod_videotrack'),
                'grace' => $effectivegrace,
            ]);
            $this->assertStringContainsString($expected, $form->getElement('integritysitepolicy')->toHtml());
        }
    }
}
