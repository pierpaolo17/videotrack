<?php

defined('MOODLE_INTERNAL') || die();


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
    $url = trim($url);
    if ($url === '') {
        return null;
    }
    $patterns = [
        '~(?:youtube\.com/watch\?v=)([A-Za-z0-9_-]{11})~',
        '~(?:youtu\.be/)([A-Za-z0-9_-]{11})~',
        '~(?:youtube\.com/embed/)([A-Za-z0-9_-]{11})~',
        '~(?:youtube\.com/shorts/)([A-Za-z0-9_-]{11})~',
    ];
    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }
    }
    parse_str((string)parse_url($url, PHP_URL_QUERY), $queryparams);
    if (!empty($queryparams['v']) && preg_match('/^[A-Za-z0-9_-]{11}$/', $queryparams['v'])) {
        return $queryparams['v'];
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
    $url = trim($url);
    if (preg_match('~vimeo\.com/(?:video/|channels/[^/]+/|groups/[^/]+/videos/)?(\d+)~', $url, $m)) {
        return $m[1];
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
    $speeds = array_filter(array_map('floatval', preg_split('/[,\n]+/', $raw)));
    sort($speeds);
    $speeds = array_values(array_unique($speeds));

    // Apply site-wide hard cap (0 = no limit).
    $max = videotrack_get_max_playback_rate();
    if ($max > 0) {
        $speeds = array_values(array_filter($speeds, function($s) use ($max) {
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
    // Il valore è salvato in centesimi (150 = 1.5×) per evitare imprecisioni floating-point.
    // Restituiamo il float corrispondente (1.5) oppure 0.0 se non c'è limite.
    $val = (int)get_config('mod_videotrack', 'maxplaybackrate');
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
    $speeds = array_filter(array_map('floatval', preg_split('/[,\n]+/', $raw)));
    sort($speeds);
    return array_values(array_unique($speeds));
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
        $labels = array_map(static function($reaction) {
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
    // Cache statica per evitare query multiple sulla stessa attività nella stessa request.
    // Chiave separata per includedeleted=true (usato raramente, es. backup).
    static $cache = [];
    $key = $videotrackid . ($includedeleted ? ':all' : ':active');
    if (!isset($cache[$key])) {
        $where = ['videotrackid' => $videotrackid];
        if (!$includedeleted) {
            $where['isdeleted'] = 0;
        }
        $cache[$key] = $DB->get_records(
            'videotrack_react', $where, 'sortorder ASC, id ASC'
        );
    }
    return $cache[$key];
}

function videotrack_reaction_icon_url(\context_module $context, stdClass $reaction): string {
    if (($reaction->icontype ?? '') !== 'file') {
        return '';
    }
    // Blocca URL esterni: le icone devono essere file Moodle (pluginfile).
    // URL esterni possono introdurre tracking, mixed content o SSRF.
    if (!empty($reaction->iconvalue) && preg_match('~^https?://~', $reaction->iconvalue)) {
        return ''; // Ignora silenziosamente URL esterni.
    }
    $fs = get_file_storage();
    $files = $fs->get_area_files($context->id, 'mod_videotrack', 'reactionicon', (int)$reaction->id, 'itemid, filepath, filename', false);
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

function videotrack_render_reaction_icon(stdClass $reaction, ?\context_module $context = null, bool $withlabel = true): string {
    $label = s($reaction->label ?? '');
    $iconhtml = '';
    if (($reaction->icontype ?? 'emoji') === 'fa' && !empty($reaction->iconvalue)) {
        $iconhtml = html_writer::tag('i', '', [
            'class' => trim((string)$reaction->iconvalue),
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
        $iconhtml = html_writer::span(s($reaction->iconvalue !== '' ? $reaction->iconvalue : $label), 'videotrack-reaction-icon-text');
    }
    if (!$withlabel) {
        return $iconhtml;
    }
    return html_writer::span($iconhtml, 'videotrack-reaction-icon-wrapper') . ' ' . html_writer::span($label, 'videotrack-reaction-label');
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
