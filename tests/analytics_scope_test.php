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
use mod_videotrack\local\analytics_scope_matcher;
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
#[CoversClass(analytics_scope_matcher::class)]
final class analytics_scope_test extends advanced_testcase {
    /**
     * The stable public scope API delegates matching to the focused service.
     */
    public function test_matching_scope_api_delegates_to_focused_service(): void {
        $scopesource = file_get_contents(__DIR__ . '/../classes/local/analytics_scope.php');
        $matchersource = file_get_contents(__DIR__ . '/../classes/local/analytics_scope_matcher.php');

        $this->assertStringContainsString(
            'return analytics_scope_matcher::matching_accessible_instances($videotrack, $cm, $userid);',
            $scopesource
        );
        $this->assertStringContainsString('analytics_scope::technical_identity(', $matchersource);
        $this->assertStringContainsString('self::get_instance_record(', $matchersource);
        $this->assertStringContainsString('report_access::can_view_aggregate(', $matchersource);
        $this->assertStringContainsString('cm.deletioninprogress = 0', $matchersource);
        $this->assertStringContainsString('vf.contenthash = :contenthash', $matchersource);
        $this->assertStringContainsString('analytics_scope::normalise_external_url(', $matchersource);
    }

    /**
     * Matcher query stages preserve provider-id, upload-hash and canonical-URL identity.
     */
    public function test_matcher_preserves_source_specific_identity_contracts(): void {
        $queryparts = new \ReflectionMethod(analytics_scope_matcher::class, 'candidate_query_parts');
        [$joins, $where, $params] = $queryparts->invoke(null, 'youtube', 'provider-id');
        $this->assertSame('', $joins);
        $this->assertStringContainsString('vt.videoid = :videoid', $where);
        $this->assertSame('provider-id', $params['videoid']);

        [$joins, $where, $params] = $queryparts->invoke(null, 'upload', 'content-hash');
        $this->assertStringContainsString('JOIN {files} vf', $joins);
        $this->assertStringContainsString('vf.contenthash = :contenthash', $where);
        $this->assertSame('content-hash', $params['contenthash']);

        $filter = new \ReflectionMethod(analytics_scope_matcher::class, 'filter_external_candidates');
        $candidates = [
            7 => (object)['videourl' => 'HTTPS://Media.Example.test:443/video.mp4?b=2&a=1#part'],
            8 => (object)['videourl' => 'https://media.example.test/other.mp4?a=1&b=2'],
        ];
        $filtered = $filter->invoke(
            null,
            $candidates,
            'external',
            'https://media.example.test/video.mp4?a=1&b=2'
        );
        $this->assertSame([7], array_keys($filtered));
    }

    /**
     * Matcher ordering keeps the current instance first and then uses stable labels and ids.
     */
    public function test_matcher_orders_current_instance_first_deterministically(): void {
        $accessible = [
            8 => (object)['id' => 8, 'coursefullname' => 'Beta', 'name' => 'Video'],
            7 => (object)['id' => 7, 'coursefullname' => 'Zulu', 'name' => 'Current'],
            9 => (object)['id' => 9, 'coursefullname' => 'Alpha', 'name' => 'Video'],
        ];
        $sort = new \ReflectionMethod(analytics_scope_matcher::class, 'sort_current_first');
        $arguments = [&$accessible, 7];
        $sort->invokeArgs(null, $arguments);

        $this->assertSame([7, 9, 8], array_keys($accessible));
    }

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
        $this->assertSame(
            'https://media.example.test/video.mp4?a=1&b=2',
            analytics_scope::normalise_external_url('//Media.Example.test:443//video.mp4?b=2&a=1#fragment')
        );
        $this->assertSame(
            'http://media.example.test/video.mp4',
            analytics_scope::normalise_external_url('http://Media.Example.test:80//video.mp4')
        );
        $this->assertSame(
            'http://media.example.test:8080/',
            analytics_scope::normalise_external_url('http://Media.Example.test:8080')
        );
        $this->assertSame('', analytics_scope::normalise_external_url('not a url'));
        $this->assertSame('', analytics_scope::normalise_external_url('https:///missing-host'));
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
