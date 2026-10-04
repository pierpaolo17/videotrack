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
 * SQL builders for teacher-report event and learner discovery queries.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class report_event_query {
    /**
     * Builds the standard reaction-event report condition and named parameters.
     *
     * Personal notes and bookmarks are intentionally excluded from this event stream.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @param int $useridfilter Optional Moodle user id filter.
     * @param int $reactionidfilter Optional reaction id filter.
     * @param float|null $timefrom Optional inclusive lower video-time bound.
     * @param float|null $timeto Optional inclusive upper video-time bound.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function reaction_event_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams,
        int $useridfilter,
        int $reactionidfilter,
        ?float $timefrom,
        ?float $timeto
    ): array {
        $conditions = "videotrackid = :vtid AND isdeleted = 0 AND (notetype = '' OR notetype IS NULL)";
        $conditions .= " AND {$learnerwhere}";
        $params = ['vtid' => $videotrackid] + $learnerparams;
        if ($useridfilter > 0) {
            $conditions .= ' AND userid = :uid';
            $params['uid'] = $useridfilter;
        }
        if ($reactionidfilter > 0) {
            $conditions .= ' AND reactionid = :rid';
            $params['rid'] = $reactionidfilter;
        }
        if ($timefrom !== null) {
            $conditions .= ' AND videotime >= :timefrom';
            $params['timefrom'] = $timefrom;
        }
        if ($timeto !== null) {
            $conditions .= ' AND videotime <= :timeto';
            $params['timeto'] = $timeto;
        }
        return [$conditions, $params];
    }

    /**
     * Builds the standard bookmark-event report condition and named parameters.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @param int $useridfilter Optional Moodle user id filter.
     * @param float|null $timefrom Optional inclusive lower video-time bound.
     * @param float|null $timeto Optional inclusive upper video-time bound.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function bookmark_event_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams,
        int $useridfilter,
        ?float $timefrom,
        ?float $timeto
    ): array {
        $conditions = "videotrackid = :bookmarkvtid AND isdeleted = 0 AND notetype = 'bookmark' AND {$learnerwhere}";
        $params = ['bookmarkvtid' => $videotrackid] + $learnerparams;
        if ($useridfilter > 0) {
            $conditions .= ' AND userid = :bookmarkuserid';
            $params['bookmarkuserid'] = $useridfilter;
        }
        if ($timefrom !== null) {
            $conditions .= ' AND videotime >= :bookmarktimefrom';
            $params['bookmarktimefrom'] = $timefrom;
        }
        if ($timeto !== null) {
            $conditions .= ' AND videotime <= :bookmarktimeto';
            $params['bookmarktimeto'] = $timeto;
        }
        return [$conditions, $params];
    }

    /**
     * Builds the standard integrity-event report condition and named parameters.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @param int $useridfilter Optional Moodle user id filter.
     * @param float|null $timefrom Optional inclusive lower video-time bound.
     * @param float|null $timeto Optional inclusive upper video-time bound.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function integrity_event_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams,
        int $useridfilter,
        ?float $timefrom,
        ?float $timeto
    ): array {
        $conditions = "videotrackid = :integrityvtid AND {$learnerwhere}";
        $params = ['integrityvtid' => $videotrackid] + $learnerparams;
        if ($useridfilter > 0) {
            $conditions .= ' AND userid = :integrityuserid';
            $params['integrityuserid'] = $useridfilter;
        }
        if ($timefrom !== null) {
            $conditions .= ' AND videotime >= :integritytimefrom';
            $params['integritytimefrom'] = $timefrom;
        }
        if ($timeto !== null) {
            $conditions .= ' AND videotime <= :integritytimeto';
            $params['integritytimeto'] = $timeto;
        }
        return [$conditions, $params];
    }

    /**
     * Builds the note-user discovery condition and named parameters.
     *
     * This condition is used only to discover learners with personal notes for report user options.
     * Note content, date filtering and export paths remain separate.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @param int $useridfilter Optional Moodle user id filter.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function note_user_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams,
        int $useridfilter
    ): array {
        $conditions = "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note' AND {$learnerwhere}";
        $params = ['vtid' => $videotrackid] + $learnerparams;
        if ($useridfilter > 0) {
            $conditions .= ' AND userid = :uid';
            $params['uid'] = $useridfilter;
        }
        return [$conditions, $params];
    }

    /**
     * Builds the personal-note event condition and named parameters.
     *
     * This condition is used by the per-student note list. Note exports remain separate.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @param int $useridfilter Optional Moodle user id filter.
     * @param int $createdfrom Optional inclusive creation-time lower bound.
     * @param int $createdto Optional inclusive creation-time upper bound.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function note_event_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams,
        int $useridfilter,
        int $createdfrom,
        int $createdto
    ): array {
        $conditions = "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note' AND {$learnerwhere}";
        $params = ['vtid' => $videotrackid] + $learnerparams;
        if ($useridfilter > 0) {
            $conditions .= ' AND userid = :uid';
            $params['uid'] = $useridfilter;
        }
        if ($createdfrom) {
            $conditions .= ' AND timecreated >= :notecreatedfrom';
            $params['notecreatedfrom'] = $createdfrom;
        }
        if ($createdto) {
            $conditions .= ' AND timecreated <= :notecreatedto';
            $params['notecreatedto'] = $createdto;
        }
        return [$conditions, $params];
    }

    /**
     * Builds the state-row report condition and named parameters.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @param int $useridfilter Optional Moodle user id filter.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function state_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams,
        int $useridfilter
    ): array {
        $conditions = "videotrackid = :svtid AND {$learnerwhere}";
        $params = ['svtid' => $videotrackid] + $learnerparams;
        if ($useridfilter > 0) {
            $conditions .= ' AND userid = :suid';
            $params['suid'] = $useridfilter;
        }
        return [$conditions, $params];
    }

    /**
     * Builds the segment-user discovery condition and named parameters.
     *
     * This condition is used only to discover learners represented by validated/raw segment rows
     * when assembling the report user filter options. Segment loading and Analytics remain separate.
     *
     * @param int $videotrackid VideoTrack instance id.
     * @param string $learnerwhere Canonical learner-scope SQL fragment.
     * @param array $learnerparams Canonical learner-scope named parameters.
     * @return array Tuple of SQL condition and named parameters.
     */
    public static function segment_user_condition(
        int $videotrackid,
        string $learnerwhere,
        array $learnerparams
    ): array {
        return [
            "videotrackid = :vtid AND {$learnerwhere}",
            ['vtid' => $videotrackid] + $learnerparams,
        ];
    }
}
