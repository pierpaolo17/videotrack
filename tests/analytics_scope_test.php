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

use advanced_testcase;
use mod_videotrack\local\analytics_scope;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * PHPUnit coverage for cross-course Analytics video identity.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(analytics_scope::class)]
final class analytics_scope_test extends advanced_testcase {
    /**
     * Provider identities use the exact provider video id, not the activity name.
     */
    public function test_provider_identity_uses_exact_video_id(): void {
        $youtube = (object)[
            'videosource' => 'youtube',
            'videoid' => 'AbC_123-XyZ',
            'videourl' => 'https://www.youtube.com/watch?v=AbC_123-XyZ',
        ];
        $vimeo = (object)[
            'videosource' => 'vimeo',
            'videoid' => '1197424178',
            'videourl' => 'https://vimeo.com/1197424178',
        ];

        $this->assertSame('AbC_123-XyZ', analytics_scope::technical_identity($youtube, 0)['key']);
        $this->assertSame('1197424178', analytics_scope::technical_identity($vimeo, 0)['key']);
    }

    /**
     * External URLs ignore fragments and normalise host, port and query ordering.
     */
    public function test_external_url_identity_is_normalised(): void {
        $left = 'HTTPS://Media.Example.test:443/video.mp4?token=abc&lang=it#chapter-2';
        $right = 'https://media.example.test/video.mp4?lang=it&token=abc';

        $this->assertSame(
            analytics_scope::normalise_external_url($left),
            analytics_scope::normalise_external_url($right)
        );
        $this->assertSame('', analytics_scope::normalise_external_url('not a url'));
    }

    /**
     * Analytics scope descriptors satisfy Moodle's course-module group-mode contract.
     */
    public function test_effective_groupmode_satisfies_moodle_course_module_contract(): void {
        $instance = (object)[
            'course' => 42,
            'coursegroupmode' => SEPARATEGROUPS,
            'groupmodeforce' => 1,
            'groupmode' => VISIBLEGROUPS,
            'groupingid' => 0,
        ];

        $this->assertSame(SEPARATEGROUPS, analytics_scope::effective_groupmode($instance));

        $instance->groupmodeforce = 0;
        $this->assertSame(VISIBLEGROUPS, analytics_scope::effective_groupmode($instance));
    }
}
