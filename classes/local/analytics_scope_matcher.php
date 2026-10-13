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
use stdClass;

/**
 * Finds capability-safe Analytics scopes that share one technical video identity.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class analytics_scope_matcher {
    /**
     * Returns matching VideoTrack activities for which the user can view reports.
     *
     * @param stdClass $videotrack Current activity record.
     * @param stdClass $cm Current course-module record.
     * @param int $userid User whose report capability must be checked.
     * @return array Accessible activity records, keyed by VideoTrack instance id.
     */
    public static function matching_accessible_instances(
        stdClass $videotrack,
        stdClass $cm,
        int $userid
    ): array {
        $identity = analytics_scope::technical_identity($videotrack, (int)$cm->id);
        $current = self::get_instance_record((int)$videotrack->id);
        if (!$identity || !$current) {
            return $current ? [(int)$current->id => $current] : [];
        }

        $candidates = self::matching_candidates((string)$videotrack->videosource, $identity);
        $accessible = self::accessible_candidates($candidates, $userid);
        self::include_accessible_current($accessible, $current, $videotrack, $cm, $userid);
        self::sort_current_first($accessible, (int)$videotrack->id);
        return $accessible;
    }

    /**
     * Loads candidates with the same provider id, upload hash or external URL.
     *
     * @param string $source Media source.
     * @param array $identity Technical identity descriptor.
     * @return array Candidate records keyed by instance id.
     */
    private static function matching_candidates(string $source, array $identity): array {
        global $DB;

        [$joins, $where, $params] = self::candidate_query_parts($source, (string)$identity['key']);
        $sql = "SELECT vt.id, vt.course, vt.name, vt.videosource, vt.videoid, vt.videourl,
                               vt.durationseconds, vt.reactionsenabled, vt.bookmarksenabled,
                       vt.acknowledgementenabled, vt.acknowledgementtext,
                       vt.acknowledgementformat, vt.acknowledgementtiming,
                       vt.integrityindicatorsenabled, vt.pauseonfocusloss,
                       vt.preventpictureinpicture, vt.randomfocuspauses, vt.clusterwindow,
                               cm.id AS cmid, cm.groupmode, cm.groupingid,
                               c.fullname AS coursefullname, c.groupmode AS coursegroupmode,
                               c.groupmodeforce
                  FROM {videotrack} vt
                  JOIN {course_modules} cm ON cm.instance = vt.id
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {course} c ON c.id = vt.course
                       {$joins}
                 WHERE cm.deletioninprogress = 0
                   AND {$where}";
        $candidates = $DB->get_records_sql($sql, $params);

        return self::filter_external_candidates($candidates, $source, (string)$identity['key']);
    }

    /**
     * Builds source-specific joins, predicates and parameters.
     *
     * @param string $source Media source.
     * @param string $identitykey Technical identity key.
     * @return array Join SQL, predicate SQL and named parameters.
     */
    private static function candidate_query_parts(string $source, string $identitykey): array {
        $params = ['modname' => 'videotrack', 'source' => $source];
        $joins = '';
        $where = 'vt.videosource = :source';

        if ($source === 'youtube' || $source === 'vimeo') {
            $where .= ' AND vt.videoid = :videoid';
            $params['videoid'] = $identitykey;
        } else if ($source === 'upload') {
            $joins = "
                JOIN {context} filectx
                  ON filectx.contextlevel = :modulecontext
                 AND filectx.instanceid = cm.id
                JOIN {files} vf
                  ON vf.contextid = filectx.id
                 AND vf.component = :filecomponent
                 AND vf.filearea = :filearea
                 AND vf.itemid = 0
                 AND vf.filename <> :directoryfilename";
            $where .= ' AND vf.contenthash = :contenthash';
            $params += [
                'modulecontext' => \core\context\module::LEVEL,
                'filecomponent' => 'mod_videotrack',
                'filearea' => 'videocontent',
                'directoryfilename' => '.',
                'contenthash' => $identitykey,
            ];
        }

        return [$joins, $where, $params];
    }

    /**
     * Applies canonical URL matching for future external HTML5 sources.
     *
     * @param array $candidates Candidate records.
     * @param string $source Media source.
     * @param string $identitykey Canonical external URL.
     * @return array Matching candidate records.
     */
    private static function filter_external_candidates(array $candidates, string $source, string $identitykey): array {
        if (in_array($source, ['youtube', 'vimeo', 'upload'], true)) {
            return $candidates;
        }
        return array_filter($candidates, static function (stdClass $candidate) use ($identitykey): bool {
            return analytics_scope::normalise_external_url((string)$candidate->videourl) === $identitykey;
        });
    }

    /**
     * Retains candidates whose module context grants aggregate-report access.
     *
     * @param array $candidates Candidate records.
     * @param int $userid Report viewer id.
     * @return array Accessible records keyed by instance id.
     */
    private static function accessible_candidates(array $candidates, int $userid): array {
        $accessible = [];
        foreach ($candidates as $candidate) {
            $candidatecontext = context_module::instance((int)$candidate->cmid, IGNORE_MISSING);
            if (!$candidatecontext || !report_access::can_view_aggregate($candidatecontext, $userid)) {
                continue;
            }
            $candidate->contextid = (int)$candidatecontext->id;
            $accessible[(int)$candidate->id] = $candidate;
        }
        return $accessible;
    }

    /**
     * Restores the current activity when it is accessible but absent from candidates.
     *
     * @param array $accessible Accessible records, updated in place.
     * @param stdClass $current Complete current-instance record.
     * @param stdClass $videotrack Current activity record.
     * @param stdClass $cm Current course-module record.
     * @param int $userid Report viewer id.
     * @return void
     */
    private static function include_accessible_current(
        array &$accessible,
        stdClass $current,
        stdClass $videotrack,
        stdClass $cm,
        int $userid
    ): void {
        if (isset($accessible[(int)$videotrack->id])) {
            return;
        }
        $currentcontext = context_module::instance((int)$cm->id, IGNORE_MISSING);
        if (!$currentcontext || !report_access::can_view_aggregate($currentcontext, $userid)) {
            return;
        }
        $current->contextid = (int)$currentcontext->id;
        $accessible[(int)$current->id] = $current;
    }

    /**
     * Sorts the current activity first, followed by course, activity name and id.
     *
     * @param array $accessible Accessible records, sorted in place.
     * @param int $currentid Current VideoTrack instance id.
     * @return void
     */
    private static function sort_current_first(array &$accessible, int $currentid): void {
        uasort($accessible, static function (stdClass $left, stdClass $right) use ($currentid): int {
            $leftcurrent = (int)$left->id === $currentid ? 0 : 1;
            $rightcurrent = (int)$right->id === $currentid ? 0 : 1;
            return [$leftcurrent, (string)$left->coursefullname, (string)$left->name, (int)$left->id]
                <=> [$rightcurrent, (string)$right->coursefullname, (string)$right->name, (int)$right->id];
        });
    }

    /**
     * Loads one instance together with course-module fields needed by Analytics.
     *
     * @param int $instanceid VideoTrack instance id.
     * @return stdClass|null Scope record.
     */
    private static function get_instance_record(int $instanceid): ?stdClass {
        global $DB;

        $sql = "SELECT vt.id, vt.course, vt.name, vt.videosource, vt.videoid, vt.videourl,
                       vt.durationseconds, vt.reactionsenabled, vt.bookmarksenabled,
                       vt.acknowledgementenabled, vt.acknowledgementtext,
                       vt.acknowledgementformat, vt.acknowledgementtiming,
                       vt.integrityindicatorsenabled, vt.pauseonfocusloss,
                       vt.preventpictureinpicture, vt.randomfocuspauses, vt.clusterwindow,
                       cm.id AS cmid, cm.groupmode, cm.groupingid,
                       c.fullname AS coursefullname, c.groupmode AS coursegroupmode,
                       c.groupmodeforce
                  FROM {videotrack} vt
                  JOIN {course_modules} cm ON cm.instance = vt.id
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {course} c ON c.id = vt.course
                 WHERE vt.id = :instanceid
                   AND cm.deletioninprogress = 0";
        return $DB->get_record_sql($sql, ['modname' => 'videotrack', 'instanceid' => $instanceid]) ?: null;
    }
}
