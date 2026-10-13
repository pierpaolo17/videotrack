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
        $userinfo = (bool) $this->get_setting_value('userinfo');
        $retentioncutoff = \mod_videotrack\local\privacy_manager::retention_cutoff_timestamp();

        $videotrack = $this->create_activity_element();
        [$reactions, $reaction] = $this->create_reaction_elements();
        $userelements = $this->create_user_elements();

        $videotrack->add_child($reactions);
        $reactions->add_child($reaction);
        if ($userinfo) {
            $this->attach_user_elements($videotrack, $userelements);
        }

        $this->configure_sources($videotrack, $reaction, $userelements, $userinfo, $retentioncutoff);
        $this->annotate_files($videotrack, $reaction);

        return $this->prepare_activity_structure($videotrack);
    }

    /**
     * Create the root activity element.
     *
     * @return backup_nested_element
     */
    private function create_activity_element(): backup_nested_element {
        return new backup_nested_element('videotrack', ['id'], [
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
    }

    /**
     * Create the reaction wrapper and row elements.
     *
     * @return array{0: backup_nested_element, 1: backup_nested_element}
     */
    private function create_reaction_elements(): array {
        $reactions = new backup_nested_element('reactions');
        $reaction = new backup_nested_element('reaction', ['id'], [
            'reactionkey', 'label', 'description', 'icontype', 'iconvalue',
            'requiredforcompletion', 'sortorder', 'isdeleted', 'timecreated', 'timemodified',
        ]);

        return [$reactions, $reaction];
    }

    /**
     * Create the elements that contain user data.
     *
     * @return array<string, backup_nested_element>
     */
    private function create_user_elements(): array {
        return [
            'segments' => new backup_nested_element('segments'),
            'segment' => new backup_nested_element('segment', ['id'], [
                'userid', 'videoid', 'sessionid', 'requestid', 'wallclockstart', 'wallclockend',
                'videotimestart', 'videotimeend', 'playbackrate', 'endreason', 'servervalidated', 'timecreated',
            ]),
            'reactionevents' => new backup_nested_element('reactionevents'),
            'reactionevent' => new backup_nested_element('reactionevent', ['id'], [
                'userid', 'videoid', 'sessionid', 'reactionid', 'reactionkey', 'reactionlabel',
                'reactiondesc', 'notetext', 'notetype', 'videotime', 'playbackrate', 'isdeleted',
                'timecreated', 'timemodified',
            ]),
            'integrityevents' => new backup_nested_element('integrityevents'),
            'integrityevent' => new backup_nested_element('integrityevent', ['id'], [
                'userid', 'videoid', 'sessionid', 'eventtype', 'videotime', 'timecreated',
            ]),
            'acknowledgements' => new backup_nested_element('acknowledgements'),
            'acknowledgement' => new backup_nested_element('acknowledgement', ['id'], [
                'userid', 'statementhash', 'instanceversion', 'viewedseconds', 'viewedpercent', 'timeconfirmed',
            ]),
        ];
    }

    /**
     * Attach user-data elements in their canonical backup order.
     *
     * @param backup_nested_element $videotrack Root activity element.
     * @param array $elements User-data elements keyed by XML element name.
     * @return void
     */
    private function attach_user_elements(backup_nested_element $videotrack, array $elements): void {
        foreach (['segment', 'reactionevent', 'integrityevent', 'acknowledgement'] as $name) {
            $wrapper = $name . 's';
            $videotrack->add_child($elements[$wrapper]);
            $elements[$wrapper]->add_child($elements[$name]);
        }
    }

    /**
     * Configure activity, reaction and retained user-data sources.
     *
     * @param backup_nested_element $videotrack Root activity element.
     * @param backup_nested_element $reaction Reaction row element.
     * @param array $elements User-data elements keyed by XML element name.
     * @param bool $userinfo Whether user data is included.
     * @param int $retentioncutoff Earliest retained event timestamp.
     * @return void
     */
    private function configure_sources(
        backup_nested_element $videotrack,
        backup_nested_element $reaction,
        array $elements,
        bool $userinfo,
        int $retentioncutoff
    ): void {
        $videotrack->set_source_table('videotrack', ['id' => backup::VAR_ACTIVITYID]);
        // Include all reactions (isdeleted=0 and isdeleted=1): historic reaction events in
        // videotrack_reactev may point to soft-deleted reactions. Excluding them from
        // backup would create orphan reaction IDs during restore.
        $reaction->set_source_table('videotrack_react', ['videotrackid' => backup::VAR_PARENTID]);

        if ($userinfo) {
            $retentionparams = [
                'videotrackid' => backup::VAR_PARENTID,
                'cutoff' => backup_helper::is_sqlparam($retentioncutoff),
            ];
            $elements['segment']->set_source_sql(
                "SELECT *
                   FROM {videotrack_seg}
                  WHERE videotrackid = :videotrackid
                    AND userid > 0
                    AND timecreated >= :cutoff",
                $retentionparams
            );
            $elements['segment']->annotate_ids('user', 'userid');

            // Aggregate state is derived and is intentionally not backed up. Restore
            // rebuilds it from retained server-validated segments and completion inputs.
            $elements['reactionevent']->set_source_sql(
                "SELECT *
                   FROM {videotrack_reactev}
                  WHERE videotrackid = :videotrackid
                    AND userid > 0
                    AND timecreated >= :cutoff",
                $retentionparams
            );
            $elements['reactionevent']->annotate_ids('user', 'userid');

            $elements['integrityevent']->set_source_sql(
                "SELECT *
                   FROM {videotrack_integrity}
                  WHERE videotrackid = :videotrackid
                    AND userid > 0
                    AND timecreated >= :cutoff",
                $retentionparams
            );
            $elements['integrityevent']->annotate_ids('user', 'userid');

            $elements['acknowledgement']->set_source_sql(
                "SELECT *
                   FROM {videotrack_acknowledge}
                  WHERE videotrackid = :videotrackid
                    AND userid > 0
                    AND timeconfirmed >= :cutoff",
                $retentionparams
            );
            $elements['acknowledgement']->annotate_ids('user', 'userid');
        }

        $videotrack->annotate_ids('forum', 'linkedforumid');
    }

    /**
     * Register every activity and reaction file area in the backup structure.
     *
     * @param backup_nested_element $videotrack Root activity element.
     * @param backup_nested_element $reaction Reaction row element.
     * @return void
     */
    private function annotate_files(backup_nested_element $videotrack, backup_nested_element $reaction): void {
        $videotrack->annotate_files('mod_videotrack', 'intro', null);
        $videotrack->annotate_files('mod_videotrack', 'videocontent', null);
        $videotrack->annotate_files('mod_videotrack', 'subtitles', null);
        $videotrack->annotate_files('mod_videotrack', 'transcripts', null);
        $videotrack->annotate_files('mod_videotrack', 'chapters', null);
        $videotrack->annotate_files('mod_videotrack', 'posterimage', null);
        $reaction->annotate_files('mod_videotrack', 'reactionicon', 'id');
    }
}
