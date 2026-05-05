<?php
namespace mod_videotrack\privacy;

defined('MOODLE_INTERNAL') || die();

use context;
use context_module;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table('videotrack_seg', [
            'userid' => 'privacy:metadata:videotrack_seg:userid',
            'sessionid' => 'privacy:metadata:videotrack_seg:sessionid',
            'wallclockstart' => 'privacy:metadata:videotrack_seg:wallclockstart',
            'wallclockend' => 'privacy:metadata:videotrack_seg:wallclockend',
            'videotimestart' => 'privacy:metadata:videotrack_seg:videotimestart',
            'videotimeend' => 'privacy:metadata:videotrack_seg:videotimeend',
            'playbackrate' => 'privacy:metadata:videotrack_seg:playbackrate',
            'endreason' => 'privacy:metadata:videotrack_seg:endreason',
            'timecreated' => 'privacy:metadata:common:timecreated',
        ], 'privacy:metadata:videotrack_seg');

        $collection->add_database_table('videotrack_state', [
            'userid' => 'privacy:metadata:videotrack_state:userid',
            'lastposition' => 'privacy:metadata:videotrack_state:lastposition',
            'durationseconds' => 'privacy:metadata:videotrack_state:durationseconds',
            'uniquecoveredseconds' => 'privacy:metadata:videotrack_state:uniquecoveredseconds',
            'completionpercent' => 'privacy:metadata:videotrack_state:completionpercent',
            'intervaljson' => 'privacy:metadata:videotrack_state:intervaljson',
            'iscompleted' => 'privacy:metadata:videotrack_state:iscompleted',
            'timemodified' => 'privacy:metadata:common:timemodified',
            'timecreated' => 'privacy:metadata:common:timecreated',
        ], 'privacy:metadata:videotrack_state');

        $collection->add_database_table('videotrack_reactev', [
            'userid'        => 'privacy:metadata:videotrack_reactev:userid',
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

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $params = ['contextmodule' => CONTEXT_MODULE, 'userid1' => $userid, 'userid2' => $userid, 'userid3' => $userid];
        $sql = "SELECT DISTINCT c.id
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid AND c.contextlevel = :contextmodule
             LEFT JOIN {videotrack_state} vs ON vs.cmid = cm.id AND vs.userid = :userid1
             LEFT JOIN {videotrack_seg} vseg ON vseg.cmid = cm.id AND vseg.userid = :userid2
             LEFT JOIN {videotrack_reactev} vre ON vre.cmid = cm.id AND vre.userid = :userid3
                 WHERE vs.id IS NOT NULL OR vseg.id IS NOT NULL OR vre.id IS NOT NULL";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);
        return $contextlist;
    }

    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $params = ['cmid1' => $context->instanceid, 'cmid2' => $context->instanceid, 'cmid3' => $context->instanceid];
        $sql = "SELECT userid
                  FROM (
                        SELECT userid FROM {videotrack_state} WHERE cmid = :cmid1
                        UNION
                        SELECT userid FROM {videotrack_seg} WHERE cmid = :cmid2
                        UNION
                        SELECT userid FROM {videotrack_reactev} WHERE cmid = :cmid3
                       ) u";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $writer = writer::with_context($context);

            $state = $DB->get_record('videotrack_state', [
                'cmid'   => $context->instanceid,
                'userid' => $userid,
            ]);
            if ($state) {
                $state->timemodified = transform::datetime($state->timemodified);
                $state->timecreated  = transform::datetime($state->timecreated);
                $state->iscompleted  = transform::yesno((bool)$state->iscompleted);
                unset($state->id, $state->videotrackid, $state->courseid,
                      $state->cmid, $state->userid);
            }
            $writer->export_data([get_string('watch', 'mod_videotrack'), 'state'], (object)[
                'state' => $state,
            ]);

            $segmentrs = $DB->get_recordset('videotrack_seg', [
                'cmid'   => $context->instanceid,
                'userid' => $userid,
            ], 'timecreated ASC');
            $segments = [];
            $chunk = 1;
            foreach ($segmentrs as $segment) {
                $segment->timecreated    = transform::datetime($segment->timecreated);
                $segment->wallclockstart = transform::datetime($segment->wallclockstart);
                $segment->wallclockend   = transform::datetime($segment->wallclockend);
                unset($segment->id, $segment->videotrackid, $segment->courseid,
                      $segment->cmid, $segment->userid);
                $segments[] = $segment;
                if (count($segments) >= 500) {
                    $writer->export_data([get_string('watch', 'mod_videotrack'), 'segments-' . $chunk], (object)[
                        'segments' => $segments,
                    ]);
                    $segments = [];
                    $chunk++;
                }
            }
            $segmentrs->close();
            if (!empty($segments)) {
                $writer->export_data([get_string('watch', 'mod_videotrack'), 'segments-' . $chunk], (object)[
                    'segments' => $segments,
                ]);
            }

            $eventrs = $DB->get_recordset('videotrack_reactev', [
                'cmid'   => $context->instanceid,
                'userid' => $userid,
            ], 'timecreated ASC');
            $active = [];
            $deleted = [];
            $notes = [];
            $deletednotes = [];
            $activechunk = 1;
            $deletedchunk = 1;
            $noteschunk = 1;
            $deletednoteschunk = 1;
            foreach ($eventrs as $reactionevent) {
                $reactionevent->timecreated  = transform::datetime($reactionevent->timecreated);
                $reactionevent->timemodified = transform::datetime($reactionevent->timemodified);
                $isdeleted = !empty($reactionevent->isdeleted);
                $reactionevent->isdeleted = transform::yesno($isdeleted);
                unset($reactionevent->id, $reactionevent->videotrackid, $reactionevent->courseid,
                      $reactionevent->cmid, $reactionevent->userid, $reactionevent->reactionid);

                if (($reactionevent->notetype ?? '') === 'note') {
                    if ($isdeleted) {
                        $deletednotes[] = $reactionevent;
                        if (count($deletednotes) >= 500) {
                            $writer->export_data([get_string('studentnotes_title', 'mod_videotrack'), 'deleted-' . $deletednoteschunk], (object)[
                                'notes' => $deletednotes,
                            ]);
                            $deletednotes = [];
                            $deletednoteschunk++;
                        }
                    } else {
                        $notes[] = $reactionevent;
                        if (count($notes) >= 500) {
                            $writer->export_data([get_string('studentnotes_title', 'mod_videotrack'), 'active-' . $noteschunk], (object)[
                                'notes' => $notes,
                            ]);
                            $notes = [];
                            $noteschunk++;
                        }
                    }
                } else if ($isdeleted) {
                    $deleted[] = $reactionevent;
                    if (count($deleted) >= 500) {
                        $writer->export_data([get_string('reactionsheader', 'mod_videotrack'), 'deleted-' . $deletedchunk], (object)[
                            'events' => $deleted,
                        ]);
                        $deleted = [];
                        $deletedchunk++;
                    }
                } else {
                    $active[] = $reactionevent;
                    if (count($active) >= 500) {
                        $writer->export_data([get_string('reactionsheader', 'mod_videotrack'), 'active-' . $activechunk], (object)[
                            'events' => $active,
                        ]);
                        $active = [];
                        $activechunk++;
                    }
                }
            }
            $eventrs->close();

            if (!empty($active)) {
                $writer->export_data([get_string('reactionsheader', 'mod_videotrack'), 'active-' . $activechunk], (object)[
                    'events' => $active,
                ]);
            }
            if (!empty($deleted)) {
                $writer->export_data([get_string('reactionsheader', 'mod_videotrack'), 'deleted-' . $deletedchunk], (object)[
                    'events' => $deleted,
                ]);
            }
            if (!empty($notes)) {
                $writer->export_data([get_string('studentnotes_title', 'mod_videotrack'), 'active-' . $noteschunk], (object)[
                    'notes' => $notes,
                ]);
            }
            if (!empty($deletednotes)) {
                $writer->export_data([get_string('studentnotes_title', 'mod_videotrack'), 'deleted-' . $deletednoteschunk], (object)[
                    'notes' => $deletednotes,
                ]);
            }
        }
    }

    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }
        $DB->delete_records('videotrack_seg',    ['cmid' => $context->instanceid]);
        $DB->delete_records('videotrack_state',  ['cmid' => $context->instanceid]);
        $DB->delete_records('videotrack_reactev',['cmid' => $context->instanceid]);
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        $user = $contextlist->get_user();
        foreach ($contextlist->get_contexts() as $context) {
            self::delete_records_for_users_in_context($context, [$user->id]);
        }
    }

    public static function delete_data_for_users(approved_userlist $userlist): void {
        self::delete_records_for_users_in_context($userlist->get_context(), $userlist->get_userids());
    }

    protected static function delete_records_for_users_in_context(context $context, array $userids): void {
        global $DB;

        if ($context->contextlevel != CONTEXT_MODULE || empty($userids)) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $params['cmid1'] = $context->instanceid;
        $params['cmid2'] = $context->instanceid;
        $params['cmid3'] = $context->instanceid;
        $DB->delete_records_select('videotrack_seg',    "cmid = :cmid1 AND userid $insql", $params);
        $DB->delete_records_select('videotrack_state',  "cmid = :cmid2 AND userid $insql", $params);
        $DB->delete_records_select('videotrack_reactev',"cmid = :cmid3 AND userid $insql", $params);
    }
}
