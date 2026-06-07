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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * VideoTrack plugin file.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Reads an integer mod_videotrack configuration value while preserving explicit zero values.
 *
 * This variant returns the default only when the setting is missing or invalid. It is useful
 * for settings where 0 has a documented meaning, such as disabling a feature.
 *
 * @param string $name mod_videotrack setting name without component prefix.
 * @param int $default Default value when the setting is missing or invalid.
 * @param int $min Inclusive lower bound.
 * @param int $max Inclusive upper bound.
 * @return int Bounded mod_videotrack setting value.
 */
function videotrack_get_config_int(string $name, int $default, int $min, int $max): int {
    if ($min > $max) {
        throw new \coding_exception(
            "videotrack_get_config_int: min ({$min}) must not exceed max ({$max}) for setting '{$name}'"
        );
    }
    $value = get_config('mod_videotrack', $name);
    if ($value === false || $value === null || $value === '' || !is_numeric($value)) {
        $value = $default;
    }
    $value = (int)$value;
    return max($min, min($max, $value));
}
/**
 * Extracts the 11-character YouTube video ID from a URL.
 *
 * @param  string      $url
 * @return string|null
 */
function videotrack_extract_videoid(string $url): ?string {
    if ($url === '' || preg_match('/[\r\n]/', $url)) {
        return null;
    }
    $url = trim($url);

    $parts = parse_url($url);
    if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
        return null;
    }
    if (strtolower($parts['scheme']) !== 'https') {
        return null;
    }

    $host = strtolower(rtrim($parts['host'], '.'));
    $host = preg_replace('/^(?:www|m|music)\./', '', $host);
    $path = $parts['path'] ?? '';
    $query = $parts['query'] ?? '';

    if ($host === 'youtu.be') {
        $candidate = ltrim($path, '/');
        $candidate = explode('/', $candidate, 2)[0];
        return preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate) ? $candidate : null;
    }

    if (!in_array($host, ['youtube.com', 'youtube-nocookie.com'], true)) {
        return null;
    }

    $path = preg_replace('~/+~', '/', $path);

    if ($path === '/watch') {
        parse_str($query, $queryparams);
        return (!empty($queryparams['v']) && preg_match('/^[A-Za-z0-9_-]{11}$/', $queryparams['v']))
            ? $queryparams['v']
            : null;
    }

    if (preg_match('~^/(?:embed|shorts|live)/([A-Za-z0-9_-]{11})(?:/)?$~', $path, $matches)) {
        return $matches[1];
    }

    return null;
}

/**
 * Extracts the Vimeo numeric video ID from a Vimeo URL.
 *
 * @param  string      $url
 * @return string|null
 */
function videotrack_extract_vimeo_id(string $url): ?string {
    if ($url === '' || preg_match('/[\r\n]/', $url)) {
        return null;
    }
    $url = trim($url);

    $parts = parse_url($url);
    if ($parts === false || empty($parts['scheme']) || empty($parts['host'])) {
        return null;
    }
    if (strtolower($parts['scheme']) !== 'https') {
        return null;
    }

    $host = preg_replace('/^www\./', '', strtolower(rtrim($parts['host'], '.')));
    if (!in_array($host, ['vimeo.com', 'player.vimeo.com'], true)) {
        return null;
    }

    $path = preg_replace('~/+~', '/', $parts['path'] ?? '');
    $patterns = [
        '~^/(?:video/)?(\d+)(?:/[A-Za-z0-9_-]{6,})?/?$~',
        '~^/(?:channels/[^/]+|groups/[^/]+/videos|showcase/\d+)/(?P<id>\d+)(?:/[A-Za-z0-9_-]{6,})?/?$~',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $path, $matches)) {
            return $matches['id'] ?? $matches[1];
        }
    }
    return null;
}

/**
 * Returns the effective list of allowed playback speeds for an activity.
 * If the instance has its own playbackspeeds, those override the site default.
 * Speeds above the site maxplaybackrate are filtered out.
 *
 * @param  stdClass  $videotrack  Instance record.
 * @return float[]                Sorted array of speed values.
 */
function videotrack_get_playback_speeds(stdClass $videotrack): array {
    $raw = !empty($videotrack->playbackspeeds)
        ? $videotrack->playbackspeeds
        : (string)get_config('mod_videotrack', 'playbackspeeds');

    if (empty($raw)) {
        $raw = '0.75,1,1.25,1.5,2';
    }
    $speeds = array_filter(array_map('floatval', preg_split('/[,\n]+/', $raw)), function ($speed) {
        return is_finite($speed) && $speed > 0 && $speed <= 4;
    });
    sort($speeds);
    $speeds = array_values(array_unique($speeds));
    if (empty($speeds)) {
        $speeds = [1.0];
    }

    // Apply site-wide hard cap (0 = no limit).
    $max = videotrack_get_max_playback_rate();
    if ($max > 0) {
        $speeds = array_values(array_filter($speeds, function ($s) use ($max) {
            return $s <= $max;
        }));
        // Always include at least 1× so students can play normally.
        if (empty($speeds)) {
            $speeds = [1.0];
        }
    }
    return $speeds;
}

/**
 * Returns the site-wide maximum playback rate cap (0 = no limit).
 *
 * @return float  Max allowed rate, e.g. 1.5. 0 means uncapped.
 */
function videotrack_get_max_playback_rate(): float {
    // The value is stored in hundredths (150 = 1.5x) to avoid floating-point imprecision.
    // Return the corresponding float (1.5), or 0.0 when there is no limit.
    $val = (int)get_config('mod_videotrack', 'maxplaybackrate');
    $val = max(0, min(400, $val));
    return $val > 0 ? round($val / 100.0, 4) : 0.0;
}

/**
 * Returns the site-wide available playback speeds as configured by the admin.
 *
 * @return float[]
 */
function videotrack_get_site_playback_speeds(): array {
    $raw = (string)get_config('mod_videotrack', 'playbackspeeds');
    if (empty($raw)) {
        $raw = '0.75,1,1.25,1.5,2';
    }
    $speeds = array_filter(array_map('floatval', preg_split('/[,\n]+/', $raw)), function ($speed) {
        return is_finite($speed) && $speed > 0 && $speed <= 4;
    });
    sort($speeds);
    $speeds = array_values(array_unique($speeds));
    return empty($speeds) ? [1.0] : $speeds;
}

/**
 * Formats a number of seconds into a human-readable MM:SS or H:MM:SS string.
 *
 * @param  float  $seconds  Duration in seconds (will be rounded to nearest integer).
 * @return string           Formatted time string, e.g. "01:23" or "1:02:03".
 */
function videotrack_format_seconds(float $seconds): string {
    $seconds = max(0, (int)round($seconds));
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;
    if ($hours > 0) {
        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
    return sprintf('%02d:%02d', $minutes, $secs);
}

/**
 * Builds a human-readable notice string describing the reaction requirements
 * for this activity. Used as the default reaction notice when the teacher
 * has not written a custom one.
 *
 * @param  stdClass  $videotrack  Instance record.
 * @param  array     $reactions   Array of reaction definition objects.
 * @return string                 Plain-text notice, or empty string if no requirements.
 */
function videotrack_build_required_reaction_notice(stdClass $videotrack, array $reactions): string {
    $parts = [];
    if (!empty($videotrack->reactionsrequired) && !empty($videotrack->minreactions)) {
        $parts[] = get_string('notice:minreactions', 'mod_videotrack', (int)$videotrack->minreactions);
    }
    if (!empty($videotrack->requireallreactiontypes) && $reactions) {
        $labels = array_map(static function ($reaction) {
            return $reaction->label;
        }, $reactions);
        $parts[] = get_string('notice:requiredtypes', 'mod_videotrack', implode(', ', $labels));
    }
    return implode(' ', $parts);
}

/**
 * Returns all reaction definitions for a videotrack instance, sorted by sortorder.
 * Results are statically cached within the request to avoid duplicate DB queries
 * when both the reaction buttons and the reaction table need the same data.
 *
 * @param  int    $videotrackid  Instance ID.
 * @return array                 Keyed array of reaction objects (id → stdClass).
 */
function videotrack_get_reactions(int $videotrackid, bool $includedeleted = false): array {
    global $DB;
    // Static cache to avoid repeated queries for the same activity in one request.
    // Separate cache key for includedeleted=true (rarely used, for example backup).
    static $cache = [];
    $key = $videotrackid . ($includedeleted ? ':all' : ':active');
    if (!isset($cache[$key])) {
        $where = ['videotrackid' => $videotrackid];
        if (!$includedeleted) {
            $where['isdeleted'] = 0;
        }
        $cache[$key] = $DB->get_records(
            'videotrack_react',
            $where,
            'sortorder ASC, id ASC'
        );
    }
    return $cache[$key];
}

/**
 * Returns a pluginfile URL for a stored reaction icon.
 *
 * @param \context_module $context Module context.
 * @param stdClass $reaction Reaction definition record.
 * @return string Pluginfile URL, or empty string when no safe file icon exists.
 */
function videotrack_reaction_icon_url(\context_module $context, stdClass $reaction): string {
    if (($reaction->icontype ?? '') !== 'file') {
        return '';
    }
    // Block external URLs: reaction icons must be Moodle pluginfile files.
    // External URLs may introduce tracking, mixed content or SSRF.
    if (!empty($reaction->iconvalue) && preg_match('~^https?://~', $reaction->iconvalue)) {
        return ''; // Ignore external URLs silently.
    }
    $fs = get_file_storage();
    $files = $fs->get_area_files(
        $context->id,
        'mod_videotrack',
        'reactionicon',
        (int)$reaction->id,
        'itemid, filepath, filename',
        false
    );
    if (!$files) {
        return '';
    }
    $file = reset($files);
    return moodle_url::make_pluginfile_url(
        $context->id,
        'mod_videotrack',
        'reactionicon',
        (int)$reaction->id,
        $file->get_filepath(),
        $file->get_filename(),
        false
    )->out(false);
}

/**
 * Renders a reaction icon with an optional accessible label.
 *
 * @param stdClass $reaction Reaction definition record.
 * @param \context_module|null $context Module context used for file icons.
 * @param bool $withlabel Whether to include the visible label.
 * @return string HTML fragment for the reaction icon.
 */
function videotrack_render_reaction_icon(
    stdClass $reaction,
    ?\context_module $context = null,
    bool $withlabel = true
): string {
    $label = s($reaction->label ?? '');
    $iconhtml = '';
    if (($reaction->icontype ?? 'emoji') === 'fa' && !empty($reaction->iconvalue)) {
        // Class names are validated when saved, but sanitise again before output
        // because reports may render older rows created before that validation.
        $classes = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', trim((string)$reaction->iconvalue));
        $iconhtml = html_writer::tag('i', '', [
            'class' => s($classes),
            'aria-hidden' => 'true',
        ]);
    } else if (($reaction->icontype ?? 'emoji') === 'file' && $context) {
        $url = videotrack_reaction_icon_url($context, $reaction);
        if ($url !== '') {
            $iconhtml = html_writer::empty_tag('img', [
                'src' => $url,
                'alt' => '',
                'aria-hidden' => 'true',
                'class' => 'videotrack-reaction-icon-file',
                'loading' => 'lazy',
            ]);
        }
    }
    if ($iconhtml === '') {
        $iconhtml = html_writer::span(
            s($reaction->iconvalue !== '' ? $reaction->iconvalue : $label),
            'videotrack-reaction-icon-text'
        );
    }
    if (!$withlabel) {
        return $iconhtml;
    }
    return html_writer::span($iconhtml, 'videotrack-reaction-icon-wrapper')
        . ' '
        . html_writer::span($label, 'videotrack-reaction-label');
}

/**
 * Returns all reaction presets stored in config as a keyed array.
 *
 * @return array  ['preset_key' => ['name' => ..., 'reactions' => [...]], ...]
 */
function videotrack_get_all_presets(): array {
    $raw = get_config('mod_videotrack', 'reaction_presets');
    if (empty($raw)) {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * Saves the full presets array back to config.
 *
 * @param array $presets
 */
function videotrack_save_presets(array $presets): void {
    set_config('reaction_presets', json_encode(array_values($presets)), 'mod_videotrack');
}

/**
 * Returns presets formatted for a Moodle select element.
 * First option is always the empty "manual configuration" choice.
 *
 * @return array  ['' => '--- manual ---', 'key' => 'name', ...]
 */
function videotrack_get_preset_select_options(): array {
    $options = ['' => get_string('reactionpreset:none', 'mod_videotrack')];
    foreach (videotrack_get_all_presets() as $preset) {
        if (!empty($preset['key']) && !empty($preset['name'])) {
            $options[$preset['key']] = $preset['name'];
        }
    }
    return $options;
}

/**
 * Returns all presets as a flat array keyed by preset key, for the JS client.
 *
 * @return array  ['key' => ['name' => ..., 'reactions' => [...]], ...]
 */
function videotrack_get_all_presets_for_js(): array {
    $result = [];
    foreach (videotrack_get_all_presets() as $preset) {
        if (!empty($preset['key'])) {
            $result[$preset['key']] = $preset;
        }
    }
    return $result;
}

/**
 * Registers the AMD preset selector module for the mod_form page.
 * Called from mod_form.php definition() after the preset select element is added.
 *
 * @param int $repeatcount  Number of reaction rows in the form.
 */
function videotrack_require_preset_amd(int $repeatcount): void {
    global $PAGE;
    $PAGE->requires->js_call_amd('mod_videotrack/presets', 'init', [
        'id_reactionpreset_json',
        'id_reactionpreset',
        $repeatcount,
    ]);
}


/**
 * Reads an optional ISO date (YYYY-MM-DD) report filter safely.
 *
 * @param string $name Parameter name.
 * @return string Date string or empty string when invalid/omitted.
 */
function videotrack_optional_iso_date_param(string $name): string {
    $value = optional_param($name, '', PARAM_TEXT);
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : '';
}
