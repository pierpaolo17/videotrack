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
 * Deterministic aggregation stages used by the teacher report.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_aggregation {
    /**
     * Builds viewer-drop candidates across contiguous visible Analytics bins.
     *
     * A suppressed or unavailable bin breaks continuity so values on opposite
     * sides of a privacy boundary are never compared.
     *
     * @param array $bins Privacy-processed Analytics bins.
     * @return array Viewer-drop candidates.
     */
    public static function analytics_viewer_drops(array $bins): array {
        $drops = [];
        $previousbin = null;
        foreach ($bins as $bin) {
            if (!empty($bin['suppressed']) || $bin['viewers'] === null) {
                $previousbin = null;
                continue;
            }
            if ($previousbin !== null && (int)$previousbin['viewers'] > (int)$bin['viewers']) {
                $drops[] = [
                    'from' => $previousbin,
                    'to' => $bin,
                    'count' => (int)$previousbin['viewers'] - (int)$bin['viewers'],
                ];
            }
            $previousbin = $bin;
        }
        return $drops;
    }

    /**
     * Applies the canonical report ordering to completed reaction clusters.
     *
     * @param array $clusters Completed reaction clusters, modified in place.
     * @param string $aggregationmode Aggregation mode: type or peak.
     * @param string $sort Report sort mode.
     * @return void
     */
    public static function sort_reaction_clusters(array &$clusters, string $aggregationmode, string $sort): void {
        if ($aggregationmode === 'type' && $sort === 'reaction') {
            usort(
                $clusters,
                static fn($a, $b) => [$a['reactionlabel'], $a['timestamp']] <=> [$b['reactionlabel'], $b['timestamp']]
            );
        } else if ($sort === 'clicks') {
            usort($clusters, static fn($a, $b) => $b['count'] <=> $a['count']);
        } else {
            usort($clusters, static fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);
        }
    }
}
