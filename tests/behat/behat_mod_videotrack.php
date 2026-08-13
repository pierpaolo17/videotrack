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
 * VideoTrack Behat step definitions.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use Behat\Mink\Exception\ExpectationException;

/**
 * Behat steps for deterministic VideoTrack browser contracts.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_mod_videotrack extends behat_base {
    /**
     * Wait until the deterministic HTML5 fixture has loaded metadata.
     *
     * @Then /^the VideoTrack HTML5 media is ready$/
     */
    public function the_videotrack_html5_media_is_ready(): void {
        $condition = "(function() {"
            . "var media = document.querySelector('#mod-videotrack-player video');"
            . "return !!media && Number.isFinite(media.duration) && media.duration >= 30"
            . " && media.seekable && media.seekable.length > 0 && media.seekable.end(0) >= 30;"
            . "}())";
        if (!$this->getSession()->wait(5000, $condition)) {
            throw new ExpectationException(
                'The VideoTrack HTML5 fixture did not become ready.',
                $this->getSession()
            );
        }
    }

    /**
     * Seek the deterministic HTML5 player through the browser media API.
     *
     * @When /^I seek the VideoTrack HTML5 media to "(?P<seconds>[0-9]+(?:\.[0-9]+)?)" seconds$/
     * @param float $seconds Requested media time.
     */
    public function i_seek_the_videotrack_html5_media_to_seconds(float $seconds): void {
        $target = json_encode($seconds, JSON_THROW_ON_ERROR);
        $script = "(function() {"
            . "var media = document.querySelector('#mod-videotrack-player video');"
            . "if (!media) { throw new Error('VideoTrack HTML5 media not found'); }"
            . "media.currentTime = " . $target . ";"
            . "}())";
        $this->getSession()->executeScript($script);
    }

    /**
     * Assert the current HTML5 media time is inside a tolerance window.
     *
     * @Then /^the VideoTrack HTML5 media time is between "(?P<minimum>[0-9.]+)" and "(?P<maximum>[0-9.]+)"$/
     * @param float $minimum Minimum accepted timestamp.
     * @param float $maximum Maximum accepted timestamp.
     */
    public function the_videotrack_html5_media_time_should_be_between(float $minimum, float $maximum): void {
        $condition = sprintf(
            "(function() {var media = document.querySelector('#mod-videotrack-player video');"
                . "return !!media && media.currentTime >= %.6F && media.currentTime <= %.6F;}())",
            $minimum,
            $maximum
        );
        if (!$this->getSession()->wait(3000, $condition)) {
            $time = $this->getSession()->evaluateScript(
                "(function() {var media = document.querySelector('#mod-videotrack-player video');"
                . "return media ? media.currentTime : null;}())"
            );
            throw new ExpectationException(
                'VideoTrack HTML5 media time ' . var_export($time, true)
                . ' is outside the expected range ' . $minimum . '-' . $maximum . ' seconds.',
                $this->getSession()
            );
        }
    }
}
