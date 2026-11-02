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
 * Pure policy for validating configured reaction rows in the activity form.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class reaction_form_validation {
    /**
     * Validates an emoji or Font Awesome icon value.
     *
     * @param array $data Submitted form data.
     * @param int $i Reaction row index.
     * @param string $type Icon type.
     * @param bool $reactionsenabled Whether reactions are enabled.
     * @param bool $rowhastext Whether this row contains a label or description.
     * @return array Icon presence and validation errors.
     */
    public static function reaction_text_icon_errors(
        array $data,
        int $i,
        string $type,
        bool $reactionsenabled,
        bool $rowhastext
    ): array {
        $iconvalue = trim((string)($data['reactioniconvalue'][$i] ?? ''));
        $hasicon = $iconvalue !== '';
        $errors = [];
        if ($reactionsenabled && $rowhastext && !$hasicon) {
            $errors['reactioniconvalue[' . $i . ']'] = get_string('err:reactioniconvaluerequired', 'mod_videotrack');
        } else if ($hasicon && $type === 'fa' && !\videotrack_is_valid_reaction_icon_class($iconvalue)) {
            $errors['reactioniconvalue[' . $i . ']'] = get_string('err:reactioniconvalueinvalidfa', 'mod_videotrack');
        }
        return ['hasicon' => $hasicon, 'errors' => $errors];
    }

    /**
     * Requires labels and descriptions for enabled reactions with an icon.
     *
     * @param string $label Reaction label.
     * @param string $description Reaction description.
     * @param bool $reactionsenabled Whether reactions are enabled.
     * @param bool $hasicon Whether this row has an icon.
     * @param int $i Reaction row index.
     * @return array Errors and whether this row configures a reaction.
     */
    public static function reaction_details_errors(
        string $label,
        string $description,
        bool $reactionsenabled,
        bool $hasicon,
        int $i
    ): array {
        $errors = [];
        $configured = false;
        if ($reactionsenabled && $hasicon) {
            if ($label === '') {
                $errors['reactionlabel[' . $i . ']'] = get_string('required');
            }
            if ($description === '') {
                $errors['reactiondescription[' . $i . ']'] = get_string('required');
            }
            if ($label !== '' && $description !== '') {
                $configured = true;
            }
        }
        return ['errors' => $errors, 'configured' => $configured];
    }
}
