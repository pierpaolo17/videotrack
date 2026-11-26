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

namespace mod_videotrack;

use mod_videotrack\local\form_validation;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Source-bound transient duration validation and the manual fallback.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(form_validation::class)]
final class form_duration_validation_test extends \advanced_testcase {
    /**
     * Known source metadata blocks excessive durations for each supported provider.
     */
    public function test_detected_upper_bound_applies_to_all_sources(): void {
        $sources = ['youtube' => 'youtubeurl', 'vimeo' => 'vimeourl', 'upload' => 'videofile'];
        foreach ($sources as $source => $field) {
            $input = $source === 'upload' ? '137' : 'https://example.invalid/video';
            $data = ['videosource' => $source, $field => $input, 'durationseconds' => 120.001,
                'durationprobe' => json_encode(['source' => $source, 'input' => $input, 'seconds' => 120])];
            $this->assertSame(['durationseconds' => get_string('durationseconds_exceeds_source', 'mod_videotrack', (object)[
                'configured' => '120.001', 'detected' => '120',
            ])], form_validation::duration_errors($data));
            foreach ([120, 100, 0] as $manual) {
                $data['durationseconds'] = $manual;
                $this->assertSame([], form_validation::duration_errors($data));
            }
        }
    }

    /**
     * A save of unrelated settings must still reject an excessive existing duration.
     */
    public function test_reaction_and_bookmark_edits_do_not_skip_duration_validation(): void {
        $input = 'https://www.youtube.com/watch?v=VTForm00001';
        $data = ['videosource' => 'youtube', 'youtubeurl' => $input, 'durationseconds' => 180,
            'durationprobe' => json_encode(['source' => 'youtube', 'input' => $input, 'seconds' => 60])];
        foreach ([['bookmarksenabled' => 1], ['studentnotesenabled' => 1], ['reactionlabel' => ['New reaction']]] as $changes) {
            $this->assertArrayHasKey('durationseconds', form_validation::duration_errors($data + $changes));
        }
    }

    /**
     * Metadata from another source or earlier video must never constrain a new link.
     */
    public function test_stale_metadata_is_ignored_after_source_or_link_changes(): void {
        $data = ['videosource' => 'youtube', 'youtubeurl' => 'https://youtu.be/VTForm00002', 'durationseconds' => 180];
        foreach (
            [
                ['source' => 'youtube', 'input' => 'https://youtu.be/VTForm00001', 'seconds' => 60],
                ['source' => 'vimeo', 'input' => $data['youtubeurl'], 'seconds' => 60],
                ['source' => 'upload', 'input' => '137', 'seconds' => 60],
            ] as $probe
        ) {
            $data['durationprobe'] = json_encode($probe);
            $this->assertSame([], form_validation::duration_errors($data));
        }
        $data = ['videosource' => 'upload', 'videofile' => 138, 'durationseconds' => 180,
            'durationprobe' => json_encode(['source' => 'upload', 'input' => '137', 'seconds' => 60])];
        $this->assertSame([], form_validation::duration_errors($data));
    }

    /**
     * Missing, invalid and zero metadata retain the manual path without changing submitted values.
     */
    public function test_unavailable_metadata_preserves_manual_duration(): void {
        $input = 'https://youtu.be/VTForm00001';
        $data = ['videosource' => 'youtube', 'youtubeurl' => $input, 'durationseconds' => 180];
        foreach (['', '{broken', 'null', '[]', str_repeat('x', 8193), ['seconds' => 60]] as $value) {
            $data['durationprobe'] = $value;
            $this->assertSame([], form_validation::duration_errors($data));
            $this->assertSame(180, $data['durationseconds']);
        }
        foreach ([0, -1, 86401, null, '60', ['60']] as $seconds) {
            $data['durationprobe'] = json_encode(['source' => 'youtube', 'input' => $input, 'seconds' => $seconds]);
            $this->assertSame([], form_validation::duration_errors($data));
        }
    }

    /**
     * Comparison follows the millisecond precision of the saved activity field.
     */
    public function test_decimal_precision_and_existing_required_duration_rules(): void {
        $input = 'https://youtu.be/VTForm00001';
        $data = ['videosource' => 'youtube', 'youtubeurl' => $input, 'durationseconds' => 60.0001,
            'durationprobe' => json_encode(['source' => 'youtube', 'input' => $input, 'seconds' => 60.0002])];
        $this->assertSame([], form_validation::duration_errors($data));
        $data['durationseconds'] = 60.001;
        $this->assertArrayHasKey('durationseconds', form_validation::duration_errors($data));
        $data['durationseconds'] = 86401;
        $this->assertSame(
            ['durationseconds' => get_string('durationseconds_invalid', 'mod_videotrack')],
            form_validation::duration_errors($data)
        );
        $data['durationseconds'] = 0;
        $data['acknowledgementenabled'] = 1;
        $data['acknowledgementtiming'] = \mod_videotrack\local\acknowledgement::TIMING_VIDEO_END;
        $this->assertSame(
            ['durationseconds' => get_string('durationseconds_required', 'mod_videotrack')],
            form_validation::duration_errors($data)
        );
    }
}
