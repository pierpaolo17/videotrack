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

namespace mod_videotrack\local;

/**
 * Builds self-contained Forum and acknowledgement groups in the activity form.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class form_definition {
    /**
     * Add Forum fields while retaining unavailable saved destinations and field order.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param int $courseid Course whose compatible forums are offered.
     * @param \stdClass|null $current Current activity settings, or null for a new activity.
     */
    public static function add_forum_elements(\MoodleQuickForm $mform, int $courseid, ?\stdClass $current): void {
        // Optional link to a Forum activity in the same course.
        $mform->addElement('header', 'forumlinkheader', get_string('forum:settingsheader', 'mod_videotrack'));
        $forumoptions = videotrack_get_compatible_forum_options($courseid);
        $currentforumid = isset($current->linkedforumid) ? (int)$current->linkedforumid : 0;
        if ($currentforumid > 0 && !isset($forumoptions[$currentforumid])) {
            $forumoptions[$currentforumid] = get_string('forum:configuredunavailable', 'mod_videotrack');
        }
        $enableattributes = [];
        if (!$forumoptions) {
            $enableattributes['disabled'] = 'disabled';
        }
        $mform->addElement(
            'advcheckbox',
            'forumpostingenabled',
            get_string('forum:enable', 'mod_videotrack'),
            get_string('forum:enable_help', 'mod_videotrack'),
            $enableattributes
        );
        $mform->setDefault('forumpostingenabled', 0);
        $mform->setType('forumpostingenabled', PARAM_BOOL);

        $selectoptions = [0 => get_string('forum:selectforum', 'mod_videotrack')] + $forumoptions;
        $mform->addElement(
            'select',
            'linkedforumid',
            get_string('forum:linkedforum', 'mod_videotrack'),
            $selectoptions
        );
        $mform->setType('linkedforumid', PARAM_INT);
        $mform->setDefault('linkedforumid', 0);
        $mform->hideIf('linkedforumid', 'forumpostingenabled', 'notchecked');
        $mform->disabledIf('linkedforumid', 'forumpostingenabled', 'notchecked');

        $mform->addElement(
            'text',
            'forumsubjecttemplate',
            get_string('forum:subjecttemplate', 'mod_videotrack'),
            ['size' => 64, 'maxlength' => 255]
        );
        $mform->setType('forumsubjecttemplate', PARAM_TEXT);
        $mform->setDefault(
            'forumsubjecttemplate',
            get_string('forum:subjecttemplatedefault', 'mod_videotrack')
        );
        $mform->addHelpButton('forumsubjecttemplate', 'forum:subjecttemplate', 'mod_videotrack');
        $mform->hideIf('forumsubjecttemplate', 'forumpostingenabled', 'notchecked');
        if (!$forumoptions) {
            $mform->freeze('linkedforumid');
            $mform->addElement(
                'static',
                'forumnoneavailable',
                '',
                get_string('forum:nocompatibleforums', 'mod_videotrack')
            );
        }
        $mform->addElement(
            'static',
            'forumprivacyinfo',
            '',
            get_string('forum:privacyinfo', 'mod_videotrack')
        );
    }

    /**
     * Add acknowledgement settings with the current activity context and safe editor options.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param \context $context Activity form context.
     */
    public static function add_acknowledgement_elements(\MoodleQuickForm $mform, \context $context): void {
        $mform->addElement(
            'header',
            'acknowledgementheader',
            get_string('acknowledgement:header', 'mod_videotrack')
        );
        $mform->setExpanded('acknowledgementheader', false);
        $mform->addElement(
            'advcheckbox',
            'acknowledgementenabled',
            get_string('acknowledgement:enabled', 'mod_videotrack'),
            get_string('acknowledgement:enabled_desc', 'mod_videotrack')
        );
        $mform->setType('acknowledgementenabled', PARAM_BOOL);
        $mform->setDefault('acknowledgementenabled', 0);

        $mform->addElement(
            'select',
            'acknowledgementtiming',
            get_string('acknowledgement:timing', 'mod_videotrack'),
            [
                \mod_videotrack\local\acknowledgement::TIMING_ANYTIME =>
                    get_string('acknowledgement:timing_anytime', 'mod_videotrack'),
                \mod_videotrack\local\acknowledgement::TIMING_VIDEO_END =>
                    get_string('acknowledgement:timing_videoend', 'mod_videotrack'),
            ]
        );
        $mform->setType('acknowledgementtiming', PARAM_INT);
        $mform->setDefault(
            'acknowledgementtiming',
            \mod_videotrack\local\acknowledgement::TIMING_ANYTIME
        );
        $mform->addHelpButton('acknowledgementtiming', 'acknowledgement:timing', 'mod_videotrack');
        $mform->disabledIf('acknowledgementtiming', 'acknowledgementenabled', 'notchecked');
        $acknowledgementoptions = [
            'context' => $context,
            'maxfiles' => 0,
            'maxbytes' => 0,
            'trusttext' => false,
            'noclean' => false,
        ];
        $mform->addElement(
            'editor',
            'acknowledgement_editor',
            get_string('acknowledgement:text', 'mod_videotrack'),
            null,
            $acknowledgementoptions
        );
        $mform->addHelpButton('acknowledgement_editor', 'acknowledgement:text', 'mod_videotrack');
        $mform->disabledIf('acknowledgement_editor', 'acknowledgementenabled', 'notchecked');
    }
}
