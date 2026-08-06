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
 * Resolves capability-safe Analytics scopes for the same technical video.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class analytics_scope {
    /**
     * Returns matching VideoTrack activities for which the user can view reports.
     *
     * YouTube and Vimeo are matched by provider id. Uploaded files are matched by
     * Moodle content hash, so equal filenames alone are not sufficient. A future
     * external HTML5 source is matched by a normalised URL.
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
        global $DB;

        $identity = self::technical_identity($videotrack, (int)$cm->id);
        $current = self::get_instance_record((int)$videotrack->id);
        if (!$identity || !$current) {
            return $current ? [(int)$current->id => $current] : [];
        }

        $source = (string)$videotrack->videosource;
        $params = ['modname' => 'videotrack', 'source' => $source];
        $joins = '';
        $where = 'vt.videosource = :source';

        if ($source === 'youtube' || $source === 'vimeo') {
            $where .= ' AND vt.videoid = :videoid';
            $params['videoid'] = $identity['key'];
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
                'modulecontext' => CONTEXT_MODULE,
                'filecomponent' => 'mod_videotrack',
                'filearea' => 'videocontent',
                'directoryfilename' => '.',
                'contenthash' => $identity['key'],
            ];
        }

        $sql = "SELECT vt.id, vt.course, vt.name, vt.videosource, vt.videoid, vt.videourl,
                               vt.durationseconds, vt.reactionsenabled, vt.bookmarksenabled, vt.clusterwindow,
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

        if ($source !== 'youtube' && $source !== 'vimeo' && $source !== 'upload') {
            $candidates = array_filter($candidates, static function (stdClass $candidate) use ($identity): bool {
                return self::normalise_external_url((string)$candidate->videourl) === $identity['key'];
            });
        }

        $accessible = [];
        foreach ($candidates as $candidate) {
            $candidatecontext = context_module::instance((int)$candidate->cmid, IGNORE_MISSING);
            if (!$candidatecontext || !has_capability('mod/videotrack:viewreport', $candidatecontext, $userid)) {
                continue;
            }
            $candidate->contextid = (int)$candidatecontext->id;
            $accessible[(int)$candidate->id] = $candidate;
        }

        if (!isset($accessible[(int)$videotrack->id])) {
            $currentcontext = context_module::instance((int)$cm->id, IGNORE_MISSING);
            if ($currentcontext && has_capability('mod/videotrack:viewreport', $currentcontext, $userid)) {
                $current->contextid = (int)$currentcontext->id;
                $accessible[(int)$current->id] = $current;
            }
        }

        uasort($accessible, static function (stdClass $left, stdClass $right) use ($videotrack): int {
            $leftcurrent = (int)$left->id === (int)$videotrack->id ? 0 : 1;
            $rightcurrent = (int)$right->id === (int)$videotrack->id ? 0 : 1;
            return [$leftcurrent, (string)$left->coursefullname, (string)$left->name, (int)$left->id]
                <=> [$rightcurrent, (string)$right->coursefullname, (string)$right->name, (int)$right->id];
        });
        return $accessible;
    }

    /**
     * Returns the effective Moodle group mode for one Analytics scope.
     *
     * Moodle requires synthetic course-module records to contain both the
     * course id and the activity group mode.
     *
     * @param stdClass $instance Analytics scope record.
     * @return int Effective group mode.
     */
    public static function effective_groupmode(stdClass $instance): int {
        global $CFG;

        require_once($CFG->libdir . '/grouplib.php');

        $course = (object)[
            'id' => (int)$instance->course,
            'groupmode' => (int)$instance->coursegroupmode,
            'groupmodeforce' => (int)$instance->groupmodeforce,
        ];
        $cm = (object)[
            'course' => (int)$instance->course,
            'groupmode' => (int)$instance->groupmode,
            'groupingid' => (int)$instance->groupingid,
        ];

        return groups_get_activity_groupmode($cm, $course);
    }

    /**
     * Returns the group ids whose learners are visible to a report viewer.
     *
     * Null means no group restriction. An empty array means the viewer has no
     * permitted group in a grouped activity and therefore no learner data may be
     * queried. Visible groups expose all groups in the activity grouping; separate
     * groups expose only the viewer's own groups unless access-all-groups is held.
     *
     * @param stdClass $instance Analytics scope descriptor.
     * @param int $userid Report viewer id.
     * @return array|null Permitted group ids, or null for an unrestricted scope.
     */
    public static function accessible_group_ids(stdClass $instance, int $userid): ?array {
        global $CFG, $DB;

        require_once($CFG->libdir . '/grouplib.php');

        $context = context_module::instance((int)$instance->cmid, MUST_EXIST);
        $groupmode = self::effective_groupmode($instance);
        $canaccessallgroups = has_capability('moodle/site:accessallgroups', $context, $userid);
        if (
            $canaccessallgroups
            || $groupmode === NOGROUPS
            || !$DB->record_exists('groups', ['courseid' => (int)$instance->course])
        ) {
            return null;
        }

        $groupuserid = analytics::restrict_to_own_groups($groupmode, $canaccessallgroups)
            ? $userid
            : 0;
        $groups = groups_get_all_groups(
            (int)$instance->course,
            $groupuserid,
            (int)$instance->groupingid,
            'g.id'
        );
        return array_map('intval', array_keys($groups));
    }

    /**
     * Resolves the stable technical identity of one configured video.
     *
     * @param stdClass $videotrack Activity record.
     * @param int $cmid Course-module id.
     * @return array|null Identity with source, kind and key, or null if unavailable.
     */
    public static function technical_identity(stdClass $videotrack, int $cmid): ?array {
        $source = trim((string)($videotrack->videosource ?? ''));
        if ($source === 'youtube' || $source === 'vimeo') {
            $videoid = trim((string)($videotrack->videoid ?? ''));
            return $videoid === '' ? null : [
                'source' => $source,
                'kind' => 'providerid',
                'key' => $videoid,
            ];
        }
        if ($source === 'upload') {
            $context = context_module::instance($cmid, IGNORE_MISSING);
            if (!$context) {
                return null;
            }
            $files = get_file_storage()->get_area_files(
                $context->id,
                'mod_videotrack',
                'videocontent',
                0,
                'id ASC',
                false
            );
            if (!$files) {
                return null;
            }
            $file = reset($files);
            return [
                'source' => $source,
                'kind' => 'contenthash',
                'key' => $file->get_contenthash(),
            ];
        }

        $url = self::normalise_external_url((string)($videotrack->videourl ?? ''));
        return $url === '' ? null : [
            'source' => $source,
            'kind' => 'url',
            'key' => $url,
        ];
    }

    /**
     * Normalises an external media URL for technical-identity comparisons.
     *
     * Fragments are ignored, host and scheme are lower-cased, default ports are
     * removed and query parameters are sorted. Query values are preserved.
     *
     * @param string $url External URL.
     * @return string Normalised URL, or an empty string for invalid input.
     */
    public static function normalise_external_url(string $url): string {
        $url = trim($url);
        if ($url === '') {
            return '';
        }
        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return '';
        }
        $scheme = \core_text::strtolower((string)($parts['scheme'] ?? 'https'));
        $host = \core_text::strtolower((string)$parts['host']);
        $port = isset($parts['port']) ? (int)$parts['port'] : 0;
        $authority = $host;
        if ($port > 0 && !(($scheme === 'http' && $port === 80) || ($scheme === 'https' && $port === 443))) {
            $authority .= ':' . $port;
        }
        $path = (string)($parts['path'] ?? '/');
        $path = $path === '' ? '/' : preg_replace('#/{2,}#', '/', $path);
        $query = '';
        if (!empty($parts['query'])) {
            parse_str((string)$parts['query'], $queryparts);
            ksort($queryparts);
            $query = http_build_query($queryparts, '', '&', PHP_QUERY_RFC3986);
        }
        return $scheme . '://' . $authority . $path . ($query === '' ? '' : '?' . $query);
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
                       vt.durationseconds, vt.reactionsenabled, vt.bookmarksenabled, vt.clusterwindow,
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
