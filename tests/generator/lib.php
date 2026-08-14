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
 * VideoTrack test data generator.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Generator used by PHPUnit and Behat fixtures.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_videotrack_generator extends testing_module_generator {
    /**
     * Create a VideoTrack instance with deterministic test-safe defaults.
     *
     * @param array|stdClass|null $record Instance fields.
     * @param array|null $options Course-module options.
     * @return stdClass Created instance with cmid.
     */
    public function create_instance($record = null, ?array $options = null) {
        global $DB;

        $record = (array)$record;
        $html5fixture = !empty($record['behathtml5fixture']);
        $linkedforumname = trim((string)($record['behatlinkedforum'] ?? ''));
        unset($record['behathtml5fixture'], $record['behatlinkedforum']);

        if ($html5fixture) {
            $record['videosource'] = 'upload';
            $record['durationseconds'] = $record['durationseconds'] ?? 60;
        }

        $record += [
            'videosource' => 'youtube',
            'youtubeurl' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'durationseconds' => 120,
            'reactionsenabled' => 0,
            'studentnotesenabled' => 0,
            'bookmarksenabled' => 0,
        ];

        if ($linkedforumname !== '') {
            $forum = $DB->get_record('forum', [
                'course' => (int)$record['course'],
                'name' => $linkedforumname,
            ], '*', MUST_EXIST);
            $record['forumpostingenabled'] = 1;
            $record['linkedforumid'] = (int)$forum->id;
        }

        $instance = parent::create_instance($record, (array)$options);

        if ($html5fixture) {
            $context = context_module::instance((int)$instance->cmid);
            $encodedfixture = file_get_contents(dirname(__DIR__) . '/fixtures/behat-video.mp4.b64');
            $fixturecontent = is_string($encodedfixture) ? base64_decode($encodedfixture, true) : false;
            if ($fixturecontent === false) {
                throw new coding_exception('Invalid VideoTrack HTML5 Behat fixture.');
            }
            get_file_storage()->create_file_from_string([
                'contextid' => $context->id,
                'component' => 'mod_videotrack',
                'filearea' => 'videocontent',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => 'behat-video.mp4',
            ], $fixturecontent);
        }

        if (
            !empty($record['reactionsenabled'])
            && !$DB->record_exists('videotrack_react', [
                'videotrackid' => $instance->id,
                'isdeleted' => 0,
            ])
        ) {
            $now = time();
            $DB->insert_record('videotrack_react', (object)[
                'videotrackid' => $instance->id,
                'reactionkey' => 'behat_test_reaction',
                'label' => 'Test reaction',
                'description' => 'Deterministic reaction created by the VideoTrack test generator.',
                'icontype' => 'emoji',
                'iconvalue' => '👍',
                'requiredforcompletion' => 0,
                'sortorder' => 0,
                'isdeleted' => 0,
                'timecreated' => $now,
                'timemodified' => $now,
            ]);
        }

        return $instance;
    }
}
