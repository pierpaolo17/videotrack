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

// This file is part of Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

class backup_videotrack_activity_structure_step extends backup_activity_structure_step {
    protected function define_structure() {
        $userinfo = $this->get_setting_value('userinfo');

        $videotrack = new backup_nested_element('videotrack', ['id'], [
            'name', 'intro', 'introformat', 'youtubeurl', 'videoid',
            'videosource', 'videourl', 'playbackspeeds',
            'autoplay', 'loop', 'startmuted', 'allowdownload', 'html5controls',
            'playerwidth', 'rewindstep', 'fastforwardstep', 'captions', 'captionslang',
            'durationseconds',
            'showcontrols', 'disablekeyboard', 'showfullscreen', 'allowseekforward',
            'allowseekbackward', 'allowplaybackratechange', 'countbyvideotime',
            'resumeplayback', 'maxplaybackrate',
            'completionpercent', 'reactionsenabled', 'reactionsrequired', 'minreactions',
            'requireallreactiontypes', 'completionlogic', 'clusterwindow', 'showstudentreport',
            'showreactionnotice', 'reactionnoticeformat', 'reactionnotice',
            'showtranscript', 'showchapters', 'studentnotesenabled',
            'grade', 'gradepass', 'showgradeto',
            'timemodified', 'timecreated'
        ]);

        $reactions = new backup_nested_element('reactions');
        $reaction = new backup_nested_element('reaction', ['id'], [
            'reactionkey', 'label', 'description', 'icontype', 'iconvalue',
            'requiredforcompletion', 'sortorder', 'isdeleted', 'timecreated', 'timemodified'
        ]);

        $segments = new backup_nested_element('segments');
        $segment = new backup_nested_element('segment', ['id'], [
            'userid', 'videoid', 'sessionid', 'wallclockstart', 'wallclockend',
            'videotimestart', 'videotimeend', 'playbackrate', 'endreason', 'timecreated'
        ]);

        $states = new backup_nested_element('states');
        $state = new backup_nested_element('state', ['id'], [
            'userid', 'videoid', 'lastposition', 'durationseconds', 'uniquecoveredseconds',
            'completionpercent', 'intervaljson', 'iscompleted', 'timemodified', 'timecreated'
        ]);

        $reactionevents = new backup_nested_element('reactionevents');
        $reactionevent = new backup_nested_element('reactionevent', ['id'], [
            'userid', 'videoid', 'sessionid', 'reactionid', 'reactionkey', 'reactionlabel',
            'reactiondesc', 'notetext', 'notetype',
            'videotime', 'playbackrate', 'isdeleted', 'timecreated', 'timemodified'
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
        }

        $videotrack->set_source_table('videotrack', ['id' => backup::VAR_ACTIVITYID]);
        // Include tutte le reazioni (isdeleted=0 e isdeleted=1): gli eventi storici in
        // videotrack_reactev possono puntare a reazioni eliminate via soft-delete.
        // Escluderle dal backup causerebbe reactionid orfani al restore.
        $reaction->set_source_table('videotrack_react', ['videotrackid' => backup::VAR_PARENTID]);

        if ($userinfo) {
            $segment->set_source_table('videotrack_seg', ['videotrackid' => backup::VAR_PARENTID]);
            $segment->annotate_ids('user', 'userid');

            $state->set_source_table('videotrack_state', ['videotrackid' => backup::VAR_PARENTID]);
            $state->annotate_ids('user', 'userid');

            $reactionevent->set_source_table('videotrack_reactev', ['videotrackid' => backup::VAR_PARENTID]);
            $reactionevent->annotate_ids('user', 'userid');
        }

        $videotrack->annotate_files('mod_videotrack', 'intro',        null);
        $videotrack->annotate_files('mod_videotrack', 'videocontent', null);
        $videotrack->annotate_files('mod_videotrack', 'subtitles',    null);
        $videotrack->annotate_files('mod_videotrack', 'posterimage',  null);
        $reaction->annotate_files('mod_videotrack',   'reactionicon', 'id');

        return $this->prepare_activity_structure($videotrack);
    }
}
