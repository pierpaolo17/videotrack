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
use coding_exception;
use stdClass;

/**
 * PHPUnit coverage for side-effect-free helpers in locallib.php.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class locallib_test extends advanced_testcase {
    /**
     * Load helper functions under test.
     */
    protected function setUp(): void {
        parent::setUp();
        require_once(__DIR__ . '/../locallib.php');
    }

    /**
     * YouTube extraction accepts supported HTTPS URL shapes and rejects unsafe input.
     *
     * @covers \videotrack_extract_videoid
     */
    public function test_extract_videoid_accepts_supported_youtube_urls(): void {
        $this->assertSame('AbCdEfGhIj1', \videotrack_extract_videoid('https://youtu.be/AbCdEfGhIj1'));
        $this->assertSame('AbCdEfGhIj1', \videotrack_extract_videoid('https://www.youtube.com/watch?v=AbCdEfGhIj1'));
        $this->assertSame('AbCdEfGhIj1', \videotrack_extract_videoid('https://youtube.com/embed/AbCdEfGhIj1'));
        $this->assertSame('AbCdEfGhIj1', \videotrack_extract_videoid('https://music.youtube.com/shorts/AbCdEfGhIj1'));

        $this->assertNull(\videotrack_extract_videoid('http://youtu.be/AbCdEfGhIj1'));
        $this->assertNull(\videotrack_extract_videoid("https://youtu.be/AbCdEfGhIj1\n"));
        $this->assertNull(\videotrack_extract_videoid('https://example.com/watch?v=AbCdEfGhIj1'));
        $this->assertNull(\videotrack_extract_videoid('https://youtu.be/not-valid'));
    }

    /**
     * Vimeo extraction accepts supported HTTPS URL shapes and rejects unsafe input.
     *
     * @covers \videotrack_extract_vimeo_id
     */
    public function test_extract_vimeo_id_accepts_supported_vimeo_urls(): void {
        $this->assertSame('123456789', \videotrack_extract_vimeo_id('https://vimeo.com/123456789'));
        $this->assertSame('123456789', \videotrack_extract_vimeo_id('https://player.vimeo.com/video/123456789'));
        $this->assertSame('123456789', \videotrack_extract_vimeo_id('https://vimeo.com/channels/staffpicks/123456789'));

        $this->assertNull(\videotrack_extract_vimeo_id('http://vimeo.com/123456789'));
        $this->assertNull(\videotrack_extract_vimeo_id("https://vimeo.com/123456789\n"));
        $this->assertNull(\videotrack_extract_vimeo_id('https://example.com/123456789'));
        $this->assertNull(\videotrack_extract_vimeo_id('https://vimeo.com/not-a-number'));
    }

    /**
     * Human-readable time formatting clamps negative values and switches to hours when needed.
     *
     * @covers \videotrack_format_seconds
     */
    public function test_format_seconds_clamps_and_formats_duration(): void {
        $this->assertSame('00:00', \videotrack_format_seconds(-5));
        $this->assertSame('00:01', \videotrack_format_seconds(1.4));
        $this->assertSame('01:01', \videotrack_format_seconds(61));
        $this->assertSame('01:00:01', \videotrack_format_seconds(3601));
    }

    /**
     * Bounded integer settings preserve explicit zero and clamp out-of-range values.
     *
     * @covers \videotrack_get_config_int
     */
    public function test_get_config_int_preserves_zero_and_clamps_values(): void {
        $this->resetAfterTest();

        set_config('testint', '0', 'mod_videotrack');
        $this->assertSame(0, \videotrack_get_config_int('testint', 10, 0, 100));

        set_config('testint', '500', 'mod_videotrack');
        $this->assertSame(100, \videotrack_get_config_int('testint', 10, 0, 100));

        set_config('testint', 'notnumeric', 'mod_videotrack');
        $this->assertSame(10, \videotrack_get_config_int('testint', 10, 0, 100));
    }

    /**
     * Invalid helper bounds should fail loudly for developers.
     *
     * @covers \videotrack_get_config_int
     */
    public function test_get_config_int_rejects_invalid_bounds(): void {
        $this->expectException(coding_exception::class);
        \videotrack_get_config_int('testint', 10, 100, 0);
    }

    /**
     * Instance playback speeds override site defaults and remain capped by the site maximum.
     *
     * @covers \videotrack_get_playback_speeds
     */
    public function test_get_playback_speeds_filters_and_applies_site_cap(): void {
        $this->resetAfterTest();
        set_config('maxplaybackrate', '150', 'mod_videotrack');

        $videotrack = new stdClass();
        $videotrack->playbackspeeds = '2, 1.5, 1, 0, invalid, 4.5';

        $this->assertSame([1.0, 1.5], \videotrack_get_playback_speeds($videotrack));
    }
}
