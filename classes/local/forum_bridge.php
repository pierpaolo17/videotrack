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

/**
 * Forum integration adapter for VideoTrack.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_videotrack\local;

use cm_info;
use context_module;
use moodle_exception;
use stdClass;

/**
 * Adapter between VideoTrack and the official Moodle Forum API.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class forum_bridge {
    /**
     * Resolves and validates the configured forum for the current user.
     *
     * @param stdClass $videotrack VideoTrack instance.
     * @param stdClass $course Course record.
     * @return array Forum runtime data.
     */
    public static function resolve_destination(stdClass $videotrack, stdClass $course): array {
        global $CFG, $DB, $USER;

        require_once($CFG->dirroot . '/mod/forum/lib.php');
        if (empty($videotrack->forumpostingenabled) || empty($videotrack->linkedforumid)) {
            throw new moodle_exception('forum:errornotenabled', 'mod_videotrack');
        }
        $forum = $DB->get_record('forum', ['id' => (int)$videotrack->linkedforumid], '*', MUST_EXIST);
        if (
            (int)$forum->course !== (int)$course->id
            || !in_array($forum->type, \videotrack_get_compatible_forum_types(), true)
        ) {
            throw new moodle_exception('forum:errorinvaliddestination', 'mod_videotrack');
        }
        $cmrecord = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);
        $modinfo = get_fast_modinfo($course, $USER->id);
        $cm = $modinfo->get_cm((int)$cmrecord->id);
        if (!$cm->uservisible) {
            throw new moodle_exception('forum:errorunavailable', 'mod_videotrack');
        }
        $context = context_module::instance($cm->id);
        require_capability('mod/forum:viewdiscussion', $context);
        $groupoptions = self::get_group_options($forum, $cm, $context);
        if (!$groupoptions) {
            throw new moodle_exception('forum:errorcannotpost', 'mod_videotrack');
        }
        $thresholdwarning = forum_check_throttling($forum, $cm);
        forum_check_blocking_threshold($thresholdwarning);

        return [
            'forum' => $forum,
            'cm' => $cm,
            'context' => $context,
            'groupoptions' => $groupoptions,
            'cansubscribe' => self::can_choose_subscription($forum),
            'defaultsubscribe' => \mod_forum\subscriptions::is_subscribed(
                $USER->id,
                $forum,
                null,
                $cm
            ),
        ];
    }

    /**
     * Returns the groups in which the current user can create a discussion.
     *
     * @param stdClass $forum Forum record.
     * @param cm_info $cm Forum course module.
     * @param context_module $context Forum context.
     * @return array Group id => formatted group name.
     */
    public static function get_group_options(stdClass $forum, cm_info $cm, context_module $context): array {
        global $USER;

        if (groups_get_activity_groupmode($cm) === NOGROUPS) {
            return forum_user_can_post_discussion($forum, -1, -1, $cm, $context)
                ? [-1 => get_string('allparticipants')]
                : [];
        }

        $accessallgroups = has_capability('moodle/site:accessallgroups', $context);
        $groups = groups_get_all_groups(
            $cm->course,
            $accessallgroups ? 0 : $USER->id,
            $cm->groupingid,
            'g.id,g.name',
            false,
            true
        );
        $options = [];
        if ($accessallgroups && forum_user_can_post_discussion($forum, -1, -1, $cm, $context)) {
            $options[-1] = get_string('allparticipants');
        }
        foreach ($groups as $group) {
            if (forum_user_can_post_discussion($forum, (int)$group->id, -1, $cm, $context)) {
                $options[(int)$group->id] = format_string($group->name, true, ['context' => $context]);
            }
        }
        return $options;
    }

    /**
     * Indicates whether the student can choose discussion subscription.
     *
     * @param stdClass $forum Forum record.
     * @return bool Whether a subscription choice is applicable.
     */
    public static function can_choose_subscription(stdClass $forum): bool {
        global $CFG;

        require_once($CFG->dirroot . '/mod/forum/lib.php');
        return \mod_forum\subscriptions::is_subscribable($forum) &&
            !\mod_forum\subscriptions::is_forcesubscribed($forum);
    }

    /**
     * Validates whether a user may attach a Forum discussion to one video timestamp.
     *
     * Learners, including users who also have report access, may reference only a
     * timestamp that is already covered by server-validated watched progress. Pure
     * report viewers such as teachers may bypass this learner-only restriction.
     *
     * @param stdClass $videotrack VideoTrack instance.
     * @param context_module $context VideoTrack activity context.
     * @param int $userid User id.
     * @param float $videotime Requested video timestamp.
     * @param string $sessionid Current browser playback session id, when available.
     */
    public static function validate_timestamp_access(
        stdClass $videotrack,
        context_module $context,
        int $userid,
        float $videotime,
        string $sessionid = ''
    ): void {
        if (learner_scope::can_participate($context, $userid)) {
            $fallbackdays = \videotrack_get_config_int('validationfallbackdays', 30, 0, 3650);
            $maxage = $fallbackdays > 0 ? $fallbackdays * DAYSECS : 0;
            if (
                tracker::has_watched_videotime_any_session(
                    (int)$videotrack->id,
                    $userid,
                    $videotime,
                    2.0,
                    $maxage
                )
            ) {
                return;
            }
            if (
                $sessionid !== ''
                && tracker::interaction_timestamp_allowed(
                    $videotrack,
                    $userid,
                    $sessionid,
                    $videotime,
                    2.0,
                    $maxage
                )
            ) {
                return;
            }
            throw new moodle_exception('error:playbackpositionnotwatched', 'mod_videotrack');
        }

        if (!has_capability('mod/videotrack:viewreport', $context, $userid)) {
            throw new moodle_exception('error:learnertrackingstaff', 'mod_videotrack');
        }
    }

    /**
     * Creates a new Forum discussion through the official external API.
     *
     * @param stdClass $videotrack VideoTrack instance.
     * @param stdClass $course Course record.
     * @param string $subject Discussion subject.
     * @param string $message Discussion HTML.
     * @param int $groupid Selected group id.
     * @param bool $subscribe Whether to subscribe the author.
     * @return int New discussion id.
     */
    public static function create_discussion(
        stdClass $videotrack,
        stdClass $course,
        string $subject,
        string $message,
        int $groupid,
        bool $subscribe
    ): int {
        global $CFG;

        $destination = self::resolve_destination($videotrack, $course);
        if (!array_key_exists($groupid, $destination['groupoptions'])) {
            throw new moodle_exception('forum:errorinvalidgroup', 'mod_videotrack');
        }
        $subject = clean_param($subject, PARAM_TEXT);
        $message = clean_text($message, FORMAT_HTML);
        if ($subject === '' || trim(html_to_text($message)) === '') {
            throw new moodle_exception('forum:errorrequiredcontent', 'mod_videotrack');
        }
        require_once($CFG->dirroot . '/mod/forum/externallib.php');
        $subscribevalue = $destination['cansubscribe']
            ? (int)$subscribe
            : (int)\mod_forum\subscriptions::is_forcesubscribed($destination['forum']);
        $options = [[
            'name' => 'discussionsubscribe',
            'value' => $subscribevalue,
        ]];
        $result = \mod_forum_external::add_discussion(
            (int)$destination['forum']->id,
            $subject,
            $message,
            $groupid,
            $options
        );
        return (int)$result['discussionid'];
    }
}
