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

namespace mod_videotrack\local;

/**
 * Privacy-safe summary of versioned learner confirmations.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class acknowledgement_analytics {
    /**
     * Build a privacy-safe Analytics summary from current confirmation records.
     *
     * Average viewing values use only records that contain the immutable progress
     * snapshot introduced in VideoTrack 1.6.20. Legacy records are counted
     * separately and are never treated as zero progress.
     *
     * @param iterable $records Current confirmation records.
     * @param int $minusers Minimum distinct users required for exact values.
     * @return array Aggregate confirmation metrics and suppression state.
     */
    public static function summary(iterable $records, int $minusers): array {
        $totals = self::count_analytics_records($records);
        $confirmationcount = $totals['confirmationcount'];
        $studentcount = $totals['studentcount'];
        $progresscount = $totals['progresscount'];
        $progressstudentcount = $totals['progressstudentcount'];

        $hasdata = $confirmationcount > 0;
        $minusers = max(1, $minusers);
        $suppressed = $hasdata && $studentcount < $minusers;
        $progresssuppressed = !$suppressed && $progresscount > 0 && $progressstudentcount < $minusers;
        $averageviewedseconds = null;
        $averageviewedpercent = null;
        if ($progresscount > 0) {
            $averageviewedseconds = round($totals['viewedsecondssum'] / $progresscount, 3);
            $averageviewedpercent = round($totals['viewedpercentsum'] / $progresscount, 2);
        }
        $counts = self::mask_counts([
            'confirmationcount' => $confirmationcount,
            'studentcount' => $studentcount,
            'progresscount' => $progresscount,
            'progressstudentcount' => $progressstudentcount,
            'progressmissing' => $confirmationcount - $progresscount,
        ], $studentcount, $minusers);

        return [
            'hasdata' => $hasdata,
            'confirmationcount' => $counts['confirmationcount'],
            'studentcount' => $counts['studentcount'],
            'progresscount' => $counts['progresscount'],
            'progressstudentcount' => $counts['progressstudentcount'],
            'progressmissing' => $counts['progressmissing'],
            'averageviewedseconds' => ($suppressed || $progresssuppressed) ? null : $averageviewedseconds,
            'averageviewedpercent' => ($suppressed || $progresssuppressed) ? null : $averageviewedpercent,
            'suppressed' => $suppressed,
            'progresssuppressed' => $progresssuppressed,
        ];
    }

    /**
     * Count confirmations and progress snapshots without exposing individual records.
     *
     * @param iterable $records Current confirmation records.
     * @return array Counts and sums used by the privacy-safe summary.
     */
    private static function count_analytics_records(iterable $records): array {
        $confirmationcount = 0;
        $userids = [];
        $progresscount = 0;
        $progressuserids = [];
        $viewedsecondssum = 0.0;
        $viewedpercentsum = 0.0;

        foreach ($records as $record) {
            $userid = (int)($record->userid ?? 0);
            if ($userid <= 0) {
                continue;
            }
            $confirmationcount++;
            $userids[$userid] = true;
            $viewedseconds = property_exists($record, 'viewedseconds') ? $record->viewedseconds : null;
            $viewedpercent = property_exists($record, 'viewedpercent') ? $record->viewedpercent : null;
            if ($viewedseconds === null || $viewedpercent === null) {
                continue;
            }
            $progresscount++;
            $progressuserids[$userid] = true;
            $viewedsecondssum += max(0.0, (float)$viewedseconds);
            $viewedpercentsum += min(100.0, max(0.0, (float)$viewedpercent));
        }

        return [
            'confirmationcount' => $confirmationcount,
            'studentcount' => count($userids),
            'progresscount' => $progresscount,
            'progressstudentcount' => count($progressuserids),
            'viewedsecondssum' => $viewedsecondssum,
            'viewedpercentsum' => $viewedpercentsum,
        ];
    }

    /**
     * Suppress exact counts when a nonempty activity is below the privacy threshold.
     *
     * @param array $counts Exact counts keyed by summary field.
     * @param int $studentcount Distinct users with confirmations.
     * @param int $minusers Minimum distinct users required for exact values.
     * @return array Exact counts or null values when suppressed.
     */
    private static function mask_counts(array $counts, int $studentcount, int $minusers): array {
        if ($studentcount > 0 && $studentcount < $minusers) {
            return array_fill_keys(array_keys($counts), null);
        }
        return $counts;
    }
}
