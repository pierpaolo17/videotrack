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

use html_writer;

/**
 * Builds site-controlled playback settings in the activity form.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class form_playback_settings {
    /**
     * Add playback defaults and freeze the original fields when course override permission is missing.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     * @param \context_course $coursecontext Original course context used by the activity form.
     */
    public static function add_elements(\MoodleQuickForm $mform, \context_course $coursecontext): void {
        $canoverrideplayer = has_capability('mod/videotrack:overrideplayersettings', $coursecontext);
        $cfg = function (string $key, $fallback) {
            $val = get_config('mod_videotrack', $key);
            return ($val !== false && $val !== '') ? $val : $fallback;
        };

        // Player settings locked when teacher lacks overrideplayersettings.
        if (!$canoverrideplayer) {
            $mform->addElement(
                'static',
                'playersettings_locked_notice',
                '',
                html_writer::tag('div', get_string('setting:lockedbyAdmin', 'mod_videotrack'), ['class' => 'alert alert-info'])
            );
        }

        $mform->addElement('advcheckbox', 'showcontrols', get_string('showcontrols', 'mod_videotrack'));

        $mform->setType('showcontrols', PARAM_BOOL);
        $mform->setDefault('showcontrols', (int)(bool)$cfg('default_showcontrols', 1));

        $mform->addElement('advcheckbox', 'disablekeyboard', get_string('disablekeyboard', 'mod_videotrack'));

        $mform->setType('disablekeyboard', PARAM_BOOL);
        $mform->setDefault('disablekeyboard', (int)(bool)$cfg('default_disablekeyboard', 0));

        $mform->addElement('advcheckbox', 'showfullscreen', get_string('showfullscreen', 'mod_videotrack'));

        $mform->setType('showfullscreen', PARAM_BOOL);
        $mform->setDefault('showfullscreen', (int)(bool)$cfg('default_showfullscreen', 1));

        $mform->addElement('advcheckbox', 'allowseekforward', get_string('allowseekforward', 'mod_videotrack'));

        $mform->setType('allowseekforward', PARAM_BOOL);
        $mform->setDefault('allowseekforward', (int)(bool)$cfg('default_allowseekforward', 1));

        $mform->addElement('advcheckbox', 'allowseekbackward', get_string('allowseekbackward', 'mod_videotrack'));

        $mform->setType('allowseekbackward', PARAM_BOOL);
        $mform->setDefault('allowseekbackward', (int)(bool)$cfg('default_allowseekbackward', 1));

        $mform->addElement('advcheckbox', 'allowplaybackratechange', get_string('allowplaybackratechange', 'mod_videotrack'));

        $mform->setType('allowplaybackratechange', PARAM_BOOL);
        $mform->setDefault('allowplaybackratechange', (int)(bool)$cfg('default_allowplaybackratechange', 1));

        // Resume playback from the last saved position.
        $mform->addElement(
            'advcheckbox',
            'resumeplayback',
            get_string('resumeplayback', 'mod_videotrack'),
            get_string('resumeplayback_desc', 'mod_videotrack')
        );

        $mform->setType('resumeplayback', PARAM_BOOL);
        $mform->setDefault('resumeplayback', (int)get_config('mod_videotrack', 'resumeplayback'));
        $mform->addHelpButton('resumeplayback', 'resumeplayback', 'mod_videotrack');

        self::add_rate_limit_elements($mform);

        // Lock player fields if the teacher does not have the override capability.
        if (!$canoverrideplayer) {
            $lockedfields = [
                'showcontrols',
                'disablekeyboard',
                'showfullscreen',
                'allowseekforward',
                'allowseekbackward',
                'allowplaybackratechange',
                'blockedseekplaybackrate',
            ];
            foreach ($lockedfields as $field) {
                $mform->freeze($field);
            }
        }
    }

    /**
     * Add the existing maximum-rate and blocked-seek-rate choices without changing stored values.
     *
     * @param \MoodleQuickForm $mform Activity settings form.
     */
    private static function add_rate_limit_elements(\MoodleQuickForm $mform): void {
        // Maximum playback rate limit (0 = no limit).
        $maxspeedoptions = [
            0   => get_string('maxplaybackrate_nolimit', 'mod_videotrack'),
            100 => '1×', 125 => '1.25×', 150 => '1.5×', 175 => '1.75×',
            200 => '2×', 300 => '3×', 400 => '4×',
        ];
        $mform->addElement(
            'select',
            'maxplaybackrate',
            get_string('maxplaybackrate', 'mod_videotrack'),
            $maxspeedoptions
        );
        $mform->setType('maxplaybackrate', PARAM_INT);
        $mform->setDefault('maxplaybackrate', (int)get_config('mod_videotrack', 'maxplaybackrate'));
        $mform->addHelpButton('maxplaybackrate', 'maxplaybackrate', 'mod_videotrack');

        $blockedseekrateoptions = [
            50 => '0.5×',
            75 => '0.75×',
            100 => '1×',
        ];
        $mform->addElement(
            'select',
            'blockedseekplaybackrate',
            get_string('blockedseekplaybackrate', 'mod_videotrack'),
            $blockedseekrateoptions
        );
        $mform->setType('blockedseekplaybackrate', PARAM_INT);
        $mform->setDefault('blockedseekplaybackrate', 50);
        $mform->addHelpButton('blockedseekplaybackrate', 'blockedseekplaybackrate', 'mod_videotrack');
    }
}
