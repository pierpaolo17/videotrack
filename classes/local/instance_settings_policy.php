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

use context_course;
use moodle_exception;
use stdClass;

/**
 * Enforces the activity form's override permissions before persistence or file changes.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class instance_settings_policy {
    /**
     * Replace locked inputs with stored values or creation defaults.
     *
     * Trusted automation must run as an authorised actor too. A missing form is
     * not an authorisation bypass. Restore writes its preserved records directly.
     *
     * @param stdClass $data Submitted activity data, modified in place.
     * @param stdClass|null $previous Persisted activity on update, or null on creation.
     */
    public static function enforce(stdClass $data, ?stdClass $previous = null): void {
        $courseid = (int)($previous->course ?? $data->course);
        $context = context_course::instance($courseid);
        if ($previous !== null) {
            $data->course = $courseid;
        }
        if (!has_capability('mod/videotrack:overrideplayersettings', $context)) {
            self::restore_player_fields($data, $previous);
        }
        if (!has_capability('mod/videotrack:overridecompletionsettings', $context)) {
            self::restore_completion_fields($data, $previous);
        }
    }

    /**
     * Preserve every field locked by the player form, including checkbox aliases.
     *
     * @param stdClass $data Submitted data.
     * @param stdClass|null $previous Persisted activity.
     */
    private static function restore_player_fields(stdClass $data, ?stdClass $previous): void {
        $defaults = [
            'showcontrols' => self::config('default_showcontrols', 1),
            'disablekeyboard' => self::config('default_disablekeyboard', 0),
            'showfullscreen' => self::config('default_showfullscreen', 1),
            'allowseekforward' => self::config('default_allowseekforward', 1),
            'allowseekbackward' => self::config('default_allowseekbackward', 1),
            'allowplaybackratechange' => self::config('default_allowplaybackratechange', 1),
            'blockedseekplaybackrate' => 50,
            'playerwidth' => 0,
            'autoplay' => self::config('default_autoplay', 0),
            'loopenabled' => self::config('default_loop', 0),
            'startmuted' => self::config('default_startmuted', 0),
            'rewindstep' => 0,
            'fastforwardstep' => 0,
            'allowdownload' => self::config('default_allowdownload', 0),
            'captions' => self::config('default_captions', 0),
            'captionslang' => self::config('default_captionslang', ''),
            'html5controls' => '',
            'playbackspeeds' => '',
        ];
        foreach ($defaults as $field => $default) {
            $data->{$field} = $previous->{$field} ?? $default;
        }
        $booleans = [
            'showcontrols',
            'disablekeyboard',
            'showfullscreen',
            'allowseekforward',
            'allowseekbackward',
            'allowplaybackratechange',
            'autoplay',
            'loopenabled',
            'startmuted',
            'allowdownload',
            'captions',
        ];
        foreach ($booleans as $field) {
            $data->{$field} = (int)!empty($data->{$field});
        }
        // The existing normalisers rebuild stored strings from flat checkboxes.
        // Replace those aliases too, before either normaliser can read the POST.
        self::restore_checkbox_aliases($data, 'html5ctrl_', (string)$data->html5controls);
        self::restore_checkbox_aliases($data, 'playbackspeed_', (string)$data->playbackspeeds);
    }

    /**
     * Restore completion settings and prevent disabling their dependencies.
     *
     * @param stdClass $data Submitted data.
     * @param stdClass|null $previous Persisted activity.
     */
    private static function restore_completion_fields(stdClass $data, ?stdClass $previous): void {
        $defaults = [
            'clusterwindow' => self::config('default_clusterwindow', 30),
            'completionpercent' => self::config('default_completionpercent', 0),
            'completionlogic' => 'and',
            'completionacknowledgement' => 0,
            'reactionsrequired' => 0,
            'minreactions' => 0,
            'requireallreactiontypes' => 0,
        ];
        foreach ($defaults as $field => $default) {
            $data->{$field} = $previous->{$field} ?? $default;
        }
        if ((int)$data->completionpercent > 0 && (float)($data->durationseconds ?? 0) <= 0) {
            throw new moodle_exception('error:lockedcompletionduration', 'mod_videotrack');
        }
        if (!empty($data->completionacknowledgement)) {
            $data->acknowledgementenabled = 1;
            $data->acknowledgementtiming = (int)($previous->acknowledgementtiming ?? 0);
            $data->acknowledgementtext = (string)($data->acknowledgementtext ?? $previous->acknowledgementtext ?? '');
            $data->acknowledgementformat = (int)($data->acknowledgementformat ?? $previous->acknowledgementformat ?? FORMAT_HTML);
            $text = is_array($data->acknowledgement_editor ?? null)
                ? (string)($data->acknowledgement_editor['text'] ?? '')
                : $data->acknowledgementtext;
            if (!acknowledgement::has_visible_text($text)) {
                throw new moodle_exception('error:lockedcompletionack', 'mod_videotrack');
            }
        }
        self::restore_reaction_requirements($data, $previous);
    }

    /**
     * Keep requirement flags tied to stored reaction ids, regardless of form order.
     *
     * @param stdClass $data Submitted data.
     * @param stdClass|null $previous Persisted activity.
     */
    private static function restore_reaction_requirements(stdClass $data, ?stdClass $previous): void {
        global $DB;

        $reactions = $previous === null ? [] : $DB->get_records('videotrack_react', [
            'videotrackid' => (int)$previous->id,
            'isdeleted' => 0,
        ]);
        $required = array_filter($reactions, static fn(stdClass $reaction): bool => !empty($reaction->requiredforcompletion));
        $lockedrules = $previous !== null && !empty($previous->reactionsenabled)
            && ($required || !empty($data->requireallreactiontypes)
                || (!empty($data->reactionsrequired) && (int)$data->minreactions > 0));
        $submittedids = [];
        $data->reactionrequired = [];
        foreach ((array)($data->reactionid ?? []) as $index => $reactionid) {
            $reactionid = (int)$reactionid;
            $data->reactionrequired[$index] = empty($reactions[$reactionid]->requiredforcompletion) ? 0 : 1;
            if (trim((string)($data->reactionlabel[$index] ?? '')) !== '') {
                $submittedids[$reactionid] = true;
            }
        }
        if ($lockedrules) {
            $data->reactionsenabled = 1;
            if (array_diff_key($reactions, $submittedids)) {
                throw new moodle_exception('error:lockedcompletionreactions', 'mod_videotrack');
            }
        }
    }

    /**
     * Reconstruct flat form aliases from the protected stored string.
     *
     * @param stdClass $data Submitted data.
     * @param string $prefix Checkbox name prefix.
     * @param string $stored Stored comma-separated choices; empty means inherit.
     */
    private static function restore_checkbox_aliases(stdClass $data, string $prefix, string $stored): void {
        foreach (array_keys(get_object_vars($data)) as $key) {
            if (str_starts_with($key, $prefix)) {
                unset($data->{$key});
            }
        }
        foreach (array_filter(array_map('trim', explode(',', $stored)), 'strlen') as $choice) {
            $data->{$prefix . $choice} = $choice;
        }
    }

    /**
     * Read the same missing/empty default contract as the activity form.
     *
     * @param string $key Site configuration key.
     * @param mixed $fallback Value when the setting is missing or empty.
     * @return mixed Configured value, including an explicit zero.
     */
    private static function config(string $key, mixed $fallback): mixed {
        $value = get_config('mod_videotrack', $key);
        return $value === false || $value === '' ? $fallback : $value;
    }
}
