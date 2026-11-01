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

namespace mod_videotrack\privacy;

use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use mod_videotrack\local\privacy_manager;

/**
 * Privacy provider for tracking, reactions, notes, bookmarks, integrity signals and acknowledgements.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describes the personal data stored by this plugin.
     *
     * @param collection $collection Metadata collection.
     * @return collection Updated metadata collection.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('videotrack_seg', [
            'videotrackid' => 'privacy:metadata:common:videotrackid',
            'courseid' => 'privacy:metadata:common:courseid',
            'cmid' => 'privacy:metadata:common:cmid',
            'userid' => 'privacy:metadata:videotrack_seg:userid',
            'videoid' => 'privacy:metadata:common:videoid',
            'sessionid' => 'privacy:metadata:videotrack_seg:sessionid',
            'requestid' => 'privacy:metadata:videotrack_seg:requestid',
            'wallclockstart' => 'privacy:metadata:videotrack_seg:wallclockstart',
            'wallclockend' => 'privacy:metadata:videotrack_seg:wallclockend',
            'videotimestart' => 'privacy:metadata:videotrack_seg:videotimestart',
            'videotimeend' => 'privacy:metadata:videotrack_seg:videotimeend',
            'playbackrate' => 'privacy:metadata:videotrack_seg:playbackrate',
            'endreason' => 'privacy:metadata:videotrack_seg:endreason',
            'servervalidated' => 'privacy:metadata:videotrack_seg:servervalidated',
            'timecreated' => 'privacy:metadata:common:timecreated',
        ], 'privacy:metadata:videotrack_seg');

        $collection->add_database_table('videotrack_state', [
            'videotrackid' => 'privacy:metadata:common:videotrackid',
            'courseid' => 'privacy:metadata:common:courseid',
            'cmid' => 'privacy:metadata:common:cmid',
            'userid' => 'privacy:metadata:videotrack_state:userid',
            'videoid' => 'privacy:metadata:common:videoid',
            'lastposition' => 'privacy:metadata:videotrack_state:lastposition',
            'durationseconds' => 'privacy:metadata:videotrack_state:durationseconds',
            'serverlastactivity' => 'privacy:metadata:videotrack_state:serverlastactivity',
            'serverplaybacksessionid' => 'privacy:metadata:videotrack_state:serverplaybacksessionid',
            'serverbudgetseconds' => 'privacy:metadata:videotrack_state:serverbudgetseconds',
            'servercreditedseconds' => 'privacy:metadata:videotrack_state:servercreditedseconds',
            'uniquecoveredseconds' => 'privacy:metadata:videotrack_state:uniquecoveredseconds',
            'completionpercent' => 'privacy:metadata:videotrack_state:completionpercent',
            'intervaljson' => 'privacy:metadata:videotrack_state:intervaljson',
            'iscompleted' => 'privacy:metadata:videotrack_state:iscompleted',
            'timemodified' => 'privacy:metadata:common:timemodified',
            'timecreated' => 'privacy:metadata:common:timecreated',
        ], 'privacy:metadata:videotrack_state');

        $collection->add_database_table('videotrack_integrity', [
            'videotrackid' => 'privacy:metadata:common:videotrackid',
            'courseid' => 'privacy:metadata:common:courseid',
            'cmid' => 'privacy:metadata:common:cmid',
            'userid' => 'privacy:metadata:videotrack_integrity:userid',
            'videoid' => 'privacy:metadata:common:videoid',
            'sessionid' => 'privacy:metadata:videotrack_integrity:sessionid',
            'eventtype' => 'privacy:metadata:videotrack_integrity:eventtype',
            'videotime' => 'privacy:metadata:videotrack_integrity:videotime',
            'timecreated' => 'privacy:metadata:common:timecreated',
        ], 'privacy:metadata:videotrack_integrity');

        $collection->add_database_table('videotrack_acknowledge', [
            'videotrackid' => 'privacy:metadata:common:videotrackid',
            'courseid' => 'privacy:metadata:common:courseid',
            'cmid' => 'privacy:metadata:common:cmid',
            'userid' => 'privacy:metadata:videotrack_acknowledge:userid',
            'statementhash' => 'privacy:metadata:videotrack_acknowledge:statementhash',
            'instanceversion' => 'privacy:metadata:videotrack_acknowledge:instanceversion',
            'viewedseconds' => 'privacy:metadata:videotrack_acknowledge:viewedseconds',
            'viewedpercent' => 'privacy:metadata:videotrack_acknowledge:viewedpercent',
            'timeconfirmed' => 'privacy:metadata:videotrack_acknowledge:timeconfirmed',
        ], 'privacy:metadata:videotrack_acknowledge');

        $collection->add_database_table('videotrack_reactev', [
            'videotrackid'   => 'privacy:metadata:common:videotrackid',
            'courseid'       => 'privacy:metadata:common:courseid',
            'cmid'           => 'privacy:metadata:common:cmid',
            'userid'        => 'privacy:metadata:videotrack_reactev:userid',
            'videoid'        => 'privacy:metadata:common:videoid',
            'reactionid'     => 'privacy:metadata:videotrack_reactev:reactionid',
            'sessionid'     => 'privacy:metadata:videotrack_reactev:sessionid',
            'reactionkey'   => 'privacy:metadata:videotrack_reactev:reactionkey',
            'reactionlabel' => 'privacy:metadata:videotrack_reactev:reactionlabel',
            'reactiondesc'  => 'privacy:metadata:videotrack_reactev:reactiondesc',
            'notetext'      => 'privacy:metadata:videotrack_reactev:notetext',
            'notetype'      => 'privacy:metadata:videotrack_reactev:notetype',
            'videotime'     => 'privacy:metadata:videotrack_reactev:videotime',
            'playbackrate'  => 'privacy:metadata:videotrack_reactev:playbackrate',
            'isdeleted'     => 'privacy:metadata:videotrack_reactev:isdeleted',
            'timemodified'  => 'privacy:metadata:common:timemodified',
            'timecreated'   => 'privacy:metadata:common:timecreated',
        ], 'privacy:metadata:videotrack_reactev');

        self::add_external_location_metadata($collection);

        return $collection;
    }

    /**
     * Describes the external video providers referenced by the activity.
     *
     * @param collection $collection Metadata collection.
     */
    private static function add_external_location_metadata(collection $collection): void {
        $collection->add_external_location_link('youtube', [
            'videoid' => 'privacy:metadata:youtube:videoid',
            'url' => 'privacy:metadata:youtube:url',
            'ipaddress' => 'privacy:metadata:external:ipaddress',
            'cookies' => 'privacy:metadata:external:cookies',
            'useragent' => 'privacy:metadata:external:useragent',
        ], 'privacy:metadata:youtube');

        $collection->add_external_location_link('vimeo', [
            'videoid' => 'privacy:metadata:vimeo:videoid',
            'url' => 'privacy:metadata:vimeo:url',
            'ipaddress' => 'privacy:metadata:external:ipaddress',
            'cookies' => 'privacy:metadata:external:cookies',
            'useragent' => 'privacy:metadata:external:useragent',
        ], 'privacy:metadata:vimeo');
    }

    /**
     * Returns contexts that contain user information for the specified user.
     *
     * @param int $userid User id.
     * @return contextlist Context list.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        // Use a UNION of module ids instead of joining all tracking tables at once.
        // This gives database optimisers a simpler plan on large installations
        // and mirrors get_users_in_context().
        $params = [
            'contextmodule' => \core\context\module::LEVEL,
            'userid1' => $userid,
            'userid2' => $userid,
            'userid3' => $userid,
            'userid4' => $userid,
            'userid5' => $userid,
        ];
        $sql = "SELECT DISTINCT c.id
                  FROM {context} c
                  JOIN (
                        SELECT cmid FROM {videotrack_state} WHERE userid = :userid1
                        UNION
                        SELECT cmid FROM {videotrack_seg} WHERE userid = :userid2
                        UNION
                        SELECT cmid FROM {videotrack_reactev} WHERE userid = :userid3
                        UNION
                        SELECT cmid FROM {videotrack_integrity} WHERE userid = :userid4
                        UNION
                        SELECT cmid FROM {videotrack_acknowledge} WHERE userid = :userid5
                       ) tracked ON tracked.cmid = c.instanceid
                 WHERE c.contextlevel = :contextmodule";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    /**
     * Adds users with data in the supplied context to the user list.
     *
     * @param userlist $userlist User list.
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel != \core\context\module::LEVEL) {
            return;
        }

        $params = [
            'cmid1' => $context->instanceid,
            'cmid2' => $context->instanceid,
            'cmid3' => $context->instanceid,
            'cmid4' => $context->instanceid,
            'cmid5' => $context->instanceid,
        ];
        $sql = "SELECT userid
                  FROM (
                        SELECT userid FROM {videotrack_state} WHERE cmid = :cmid1 AND userid > 0
                        UNION
                        SELECT userid FROM {videotrack_seg} WHERE cmid = :cmid2 AND userid > 0
                        UNION
                        SELECT userid FROM {videotrack_reactev} WHERE cmid = :cmid3 AND userid > 0
                        UNION
                        SELECT userid FROM {videotrack_integrity} WHERE cmid = :cmid4 AND userid > 0
                        UNION
                        SELECT userid FROM {videotrack_acknowledge} WHERE cmid = :cmid5 AND userid > 0
                       ) u";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Exports personal data for the approved context list.
     *
     * @param approved_contextlist $contextlist Approved context list.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        exporter::export_user_data($contextlist);
    }

    /**
     * Deletes all plugin-owned user data for a module context.
     *
     * Context-level erasure removes learner records only. Activity configuration
     * files remain attached to the activity and are removed by the normal module
     * deletion lifecycle. Moodle core owns gradebook privacy/export/erasure for
     * grade_grades rows linked to this module.
     *
     * @param context $context Moodle context.
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        if ($context->contextlevel != \core\context\module::LEVEL) {
            return;
        }

        privacy_manager::delete_all_user_data_in_context($context);
    }

    /**
     * Deletes personal data for the approved context list.
     *
     * @param approved_contextlist $contextlist Approved context list.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        $user = $contextlist->get_user();
        foreach ($contextlist->get_contexts() as $context) {
            self::delete_records_for_users_in_context($context, [$user->id]);
        }
    }

    /**
     * Deletes personal data for users in the supplied user list.
     *
     * @param approved_userlist $userlist Approved user list.
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        self::delete_records_for_users_in_context($userlist->get_context(), $userlist->get_userids());
    }

    /**
     * Deletes user records for GDPR erasure requests.
     *
     * The plugin removes personal tracking rows rather than pseudonymising them,
     * so Privacy API erasure matches the expected right-to-erasure semantics.
     *
     * @param context $context Moodle context.
     * @param array $userids User ids.
     */
    protected static function delete_records_for_users_in_context(context $context, array $userids): void {
        if ($context->contextlevel != \core\context\module::LEVEL || empty($userids)) {
            return;
        }

        foreach ($userids as $userid) {
            privacy_manager::delete_user_data_in_context($context, (int)$userid);
        }
    }
}
