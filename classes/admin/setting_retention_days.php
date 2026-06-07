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

namespace mod_videotrack\admin;

/**
 * Retention-days admin setting with an explicit audit trail for unlimited retention.
 *
 * Moodle records configuration changes, but enabling a value of 0 has a specific
 * GDPR meaning for this plugin. Keep an explicit config-log entry when the site
 * switches from finite retention to unlimited retention so administrators can
 * evidence the decision during privacy review.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class setting_retention_days extends setting_nonnegative_int {

    /**
     * Persist the setting and log when unlimited retention is newly enabled.
     *
     * @param string $data Submitted value.
     * @return string Empty string on success, otherwise an error message.
     */
    public function write_setting($data) {
        $previous = get_config('mod_videotrack', 'retentionperioddays');
        $previous = ($previous === false || $previous === null || $previous === '') ? 730 : (int) $previous;
        $submitted = (int) trim((string) $data);
        $confirmed = optional_param('s_mod_videotrack_retentionunlimitedconfirmed', 0, PARAM_BOOL);

        if ($submitted === 0 && !$confirmed) {
            return get_string('setting:retentionunlimitedconfirm_required', 'mod_videotrack');
        }

        $result = parent::write_setting($data);
        if ($result !== '') {
            return $result;
        }

        if ($submitted > 0) {
            set_config('retentionunlimitedconfirmed', 0, 'mod_videotrack');
        }

        if ($submitted === 0 && $previous !== 0 && function_exists('add_to_config_log')) {
            add_to_config_log('retentionperioddays_unlimited_enabled', $previous, 0, 'mod_videotrack');
        }

        return '';
    }
}
