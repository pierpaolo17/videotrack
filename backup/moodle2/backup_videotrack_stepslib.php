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

/**
 * VideoTrack plugin file.
 *
 * @package   mod_videotrack
 * @copyright 2026 videotrack contributors
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines the VideoTrack activity backup structure.
 */
class backup_videotrack_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the backup structure for the activity and its related user data.
     *
     * @return backup_nested_element
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $videotrack = new backup_nested_element('videotrack', ['id'], [
            'name', 'intro', 'introformat', 'youtubeurl', 'videoid',
            'videosource', 'videourl', 'playbackspeeds',
            'autoplay', 'loopenabled', 'startmuted', 'allowdownload', 'html5controls',
            'playerwidth', 'rewindstep', 'fastforwardstep', 'captions', 'captionslang',
            'durationseconds',
            'showcontrols', 'disablekeyboard', 'showfullscreen', 'allowseekforward',
            'allowseekbackward', 'allowplaybackratechange', 'countbyvideotime',
            'resumeplayback', 'maxplaybackrate', 'blockedseekplaybackrate',
            'completionpercent', 'reactionsenabled', 'reactionsrequired', 'minreactions',
            'requireallreactiontypes', 'completionlogic', 'clusterwindow', 'showstudentreport',
            'showreactionnotice', 'reactionnoticeformat', 'reactionnotice',
            'showtranscript', 'showchapters', 'studentnotesenabled', 'bookmarksenabled',
            'integrityindicatorsenabled', 'pauseonfocusloss', 'preventpictureinpicture', 'randomfocuspauses',
            'acknowledgementenabled', 'acknowledgementtext', 'acknowledgementformat',
            'acknowledgementtiming', 'completionacknowledgement',
            'forumpostingenabled', 'linkedforumid',
            'forumsubjecttemplate',
            'csvdelimiter', 'csvexportfields',
            'grade', 'gradepass', 'showgradeto',
            'timemodified', 'timecreated',
        ]);

        $reactions = new backup_nested_element('reactions');
        $reaction = new backup_nested_element('reaction', ['id'], [
            'reactionkey', 'label', 'description', 'icontype', 'iconvalue',
            'requiredforcompletion', 'sortorder', 'isdeleted', 'timecreated', 'timemodified',
        ]);

        $segments = new backup_nested_element('segments');
        $segment = new backup_nested_element('segment', ['id'], [
            'userid', 'videoid', 'sessionid', 'wallclockstart', 'wallclockend',
            'videotimestart', 'videotimeend', 'playbackrate', 'endreason', 'servervalidated', 'timecreated',
        ]);

        $states = new backup_nested_element('states');
        $state = new backup_nested_element('state', ['id'], [
            'userid', 'videoid', 'lastposition', 'durationseconds', 'uniquecoveredseconds',
            'completionpercent', 'intervaljson', 'iscompleted', 'timemodified', 'timecreated',
        ]);

        $reactionevents = new backup_nested_element('reactionevents');
        $reactionevent = new backup_nested_element('reactionevent', ['id'], [
            'userid', 'videoid', 'sessionid', 'reactionid', 'reactionkey', 'reactionlabel',
            'reactiondesc', 'notetext', 'notetype',
            'videotime', 'playbackrate', 'isdeleted', 'timecreated', 'timemodified',
        ]);

        $integrityevents = new backup_nested_element('integrityevents');
        $integrityevent = new backup_nested_element('integrityevent', ['id'], [
            'userid', 'videoid', 'sessionid', 'eventtype', 'videotime', 'timecreated',
        ]);

        $acknowledgements = new backup_nested_element('acknowledgements');
        $acknowledgement = new backup_nested_element('acknowledgement', ['id'], [
            'userid', 'statementhash', 'instanceversion', 'viewedseconds', 'viewedpercent', 'timeconfirmed',
        ]);

        $videotrack->add_child($reactions);
        $reactions->add_child($reaction);

        if ($userinfo) {
            $videotrack->add_child($segments);
            $segments->add_child($segment);
            $videotrack->add_child($states);
            $states->add_child($state);
            $videotrack->add_child($reactionevents);
            $reactionevents->add_child($reactionevent);
            $videotrack->add_child($integrityevents);
            $integrityevents->add_child($integrityevent);
            $videotrack->add_child($acknowledgements);
            $acknowledgements->add_child($acknowledgement);
        }

        $videotrack->set_source_table('videotrack', ['id' => backup::VAR_ACTIVITYID]);
        // Include all reactions (isdeleted=0 and isdeleted=1): historic reaction events in
        // videotrack_reactev may point to soft-deleted reactions. Excluding them from
        // backup would create orphan reaction IDs during restore.
        $reaction->set_source_table('videotrack_react', ['videotrackid' => backup::VAR_PARENTID]);

        if ($userinfo) {
            $segment->set_source_table('videotrack_seg', ['videotrackid' => backup::VAR_PARENTID]);
            $segment->annotate_ids('user', 'userid');

            $state->set_source_table('videotrack_state', ['videotrackid' => backup::VAR_PARENTID]);
            $state->annotate_ids('user', 'userid');

            $reactionevent->set_source_table('videotrack_reactev', ['videotrackid' => backup::VAR_PARENTID]);
            $reactionevent->annotate_ids('user', 'userid');

            $integrityevent->set_source_table('videotrack_integrity', ['videotrackid' => backup::VAR_PARENTID]);
            $integrityevent->annotate_ids('user', 'userid');

            $acknowledgement->set_source_table(
                'videotrack_acknowledge',
                ['videotrackid' => backup::VAR_PARENTID]
            );
            $acknowledgement->annotate_ids('user', 'userid');
        }

        $videotrack->annotate_ids('forum', 'linkedforumid');

        $videotrack->annotate_files('mod_videotrack', 'intro', null);
        $videotrack->annotate_files('mod_videotrack', 'videocontent', null);
        $videotrack->annotate_files('mod_videotrack', 'subtitles', null);
        $videotrack->annotate_files('mod_videotrack', 'transcripts', null);
        $videotrack->annotate_files('mod_videotrack', 'chapters', null);
        $videotrack->annotate_files('mod_videotrack', 'posterimage', null);
        $reaction->annotate_files('mod_videotrack', 'reactionicon', 'id');

        return $this->prepare_activity_structure($videotrack);
    }
}
