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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Forum composer form for VideoTrack.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotrack\form;

defined('MOODLE_INTERNAL') || die();

use moodleform;

/**
 * Student form for composing a Forum discussion linked to a video timestamp.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class forum_post_form extends moodleform {
    /**
     * Defines the composer form.
     */
    public function definition(): void {
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'time');
        $mform->setType('time', PARAM_INT);

        $mform->addElement(
            'static',
            'forumname',
            get_string('forum:destination', 'mod_videotrack'),
            $data['forumname']
        );
        $mform->addElement(
            'static',
            'videotime',
            get_string('forum:videotime', 'mod_videotrack'),
            $data['timestamp']
        );

        $groupoptions = $data['groupoptions'];
        if (count($groupoptions) === 1) {
            $mform->addElement('hidden', 'groupid', (int)array_key_first($groupoptions));
            $mform->setType('groupid', PARAM_INT);
        } else {
            $mform->addElement('select', 'groupid', get_string('group'), $groupoptions);
            $mform->setType('groupid', PARAM_INT);
            $mform->addRule('groupid', null, 'required', null, 'client');
        }

        $mform->addElement(
            'text',
            'subject',
            get_string('subject', 'forum'),
            ['size' => 64, 'maxlength' => 255]
        );
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', null, 'required', null, 'client');

        $editoroptions = [
            'maxfiles' => 0,
            'maxbytes' => 0,
            'context' => $data['context'],
        ];
        $mform->addElement('editor', 'message_editor', get_string('message', 'forum'), null, $editoroptions);
        $mform->setType('message_editor', PARAM_RAW);
        $mform->addRule('message_editor', null, 'required', null, 'client');

        if ($data['cansubscribe']) {
            $mform->addElement(
                'advcheckbox',
                'subscribe',
                get_string('forum:subscribe', 'mod_videotrack'),
                get_string('forum:subscribe_help', 'mod_videotrack')
            );
            $mform->setDefault('subscribe', 1);
            $mform->setType('subscribe', PARAM_BOOL);
        } else {
            $mform->addElement('hidden', 'subscribe', 1);
            $mform->setType('subscribe', PARAM_BOOL);
        }

        $this->add_action_buttons(true, get_string('forum:publish', 'mod_videotrack'));
    }

    /**
     * Validates required composer content and the submitted group.
     *
     * @param array $data Submitted values.
     * @param array $files Submitted files.
     * @return array Validation errors.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        if (!array_key_exists((int)($data['groupid'] ?? 0), $this->_customdata['groupoptions'])) {
            $errors['groupid'] = get_string('forum:errorinvalidgroup', 'mod_videotrack');
        }
        $message = $data['message_editor']['text'] ?? '';
        if (trim(html_to_text($message)) === '') {
            $errors['message_editor'] = get_string('required');
        }
        return $errors;
    }
}
