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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

namespace mod_videotrack\local;

/**
 * Resolves an opaque browser resume key for one learner and video.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class resume_storage {
    /**
     * Scope a browser hint to this site, user, module and media identity.
     *
     * An incomplete identity disables browser storage. Older unscoped keys are
     * never imported; the database remains the source of validated progress.
     *
     * @param int $userid Participating user id.
     * @param int $cmid Course module id.
     * @param string $type Video provider.
     * @param string $videoid Canonical video identity.
     * @return string Versioned browser key, or an empty string.
     */
    public static function key(int $userid, int $cmid, string $type, string $videoid): string {
        global $CFG;

        if ($userid <= 0 || $cmid <= 0 || $type === '' || $videoid === '') {
            return '';
        }
        $identity = json_encode([(string)$CFG->wwwroot, $userid, $cmid, $type, $videoid], JSON_THROW_ON_ERROR);
        return 'videotrack:lastposition:v2:' . hash('sha256', $identity);
    }
}
