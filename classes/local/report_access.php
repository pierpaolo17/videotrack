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

use context_module;

/**
 * Capability policy for activity-level VideoTrack reports.
 *
 * The historical viewreport capability remains a backwards-compatible full-access
 * grant. New capabilities allow delegated roles to receive aggregate report access
 * without individual learner detail or download privileges.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_access {
    /**
     * Whether the historical full-report capability is present.
     *
     * @param context_module $context Module context.
     * @param int|null $userid Optional viewer id.
     * @return bool
     */
    public static function has_legacy_full_access(context_module $context, ?int $userid = null): bool {
        return has_capability('mod/videotrack:viewreport', $context, $userid);
    }

    /**
     * Whether aggregate activity reports may be viewed.
     *
     * Individual-report access implies aggregate viewing because it exposes a more
     * detailed representation of the same learner population.
     *
     * @param context_module $context Module context.
     * @param int|null $userid Optional viewer id.
     * @return bool
     */
    public static function can_view_aggregate(context_module $context, ?int $userid = null): bool {
        return self::has_legacy_full_access($context, $userid)
            || has_capability('mod/videotrack:viewaggregatereport', $context, $userid)
            || has_capability('mod/videotrack:viewindividualreport', $context, $userid);
    }

    /**
     * Whether individual learner report data may be viewed.
     *
     * @param context_module $context Module context.
     * @param int|null $userid Optional viewer id.
     * @return bool
     */
    public static function can_view_individual(context_module $context, ?int $userid = null): bool {
        return self::has_legacy_full_access($context, $userid)
            || has_capability('mod/videotrack:viewindividualreport', $context, $userid);
    }

    /**
     * Whether aggregate report data may be downloaded.
     *
     * @param context_module $context Module context.
     * @param int|null $userid Optional viewer id.
     * @return bool
     */
    public static function can_export_aggregate(context_module $context, ?int $userid = null): bool {
        return self::has_legacy_full_access($context, $userid)
            || has_capability('mod/videotrack:exportaggregatereport', $context, $userid);
    }

    /**
     * Whether individual learner report data may be downloaded.
     *
     * @param context_module $context Module context.
     * @param int|null $userid Optional viewer id.
     * @return bool
     */
    public static function can_export_individual(context_module $context, ?int $userid = null): bool {
        return self::has_legacy_full_access($context, $userid)
            || has_capability('mod/videotrack:exportindividualreport', $context, $userid);
    }
}
