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
     * Assert the effective focus policy exposed by the real player JSON.
     *
     * @Then /^the VideoTrack focus policy is "(?P<policy>strict|hiddenonly)"$/
     * @param string $policy Expected effective policy.
     */
    public function the_videotrack_focus_policy_is(string $policy): void {
        $actual = $this->getSession()->evaluateScript(
            "(function() {"
                . "var node = document.querySelector('script[id^=\"mod-videotrack-player-config-\"]');"
                . "if (!node) { return null; }"
                . "try { return JSON.parse(node.textContent || '{}').focuslosspolicy || null; }"
                . "catch (error) { return null; }"
                . "}())"
        );
        if ($actual !== $policy) {
            throw new ExpectationException(
                'VideoTrack focus policy ' . var_export($actual, true)
                    . ' does not match expected policy ' . $policy . '.',
                $this->getSession()
            );
        }
    }

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
            . "window.__videotrackBehatPreSeekTime = media.currentTime;"
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
        if (!$this->getSession()->wait(6000, $condition)) {
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

    /**
     * Assert whether the deterministic HTML5 media is currently playing or paused.
     *
     * @Then /^the VideoTrack HTML5 media playback is "(?P<state>playing|paused)"$/
     * @param string $state Expected playback state.
     */
    public function the_videotrack_html5_media_playback_is(string $state): void {
        $expectedpaused = $state === 'paused' ? 'true' : 'false';
        $condition = "(function() {"
            . "var media = document.querySelector('#mod-videotrack-player video');"
            . "return !!media && media.paused === " . $expectedpaused . ";"
            . "}())";
        if (!$this->getSession()->wait(3000, $condition)) {
            $paused = $this->getSession()->evaluateScript(
                "(function() {var media = document.querySelector('#mod-videotrack-player video');"
                . "return media ? media.paused : null;}())"
            );
            throw new ExpectationException(
                'VideoTrack HTML5 media paused=' . var_export($paused, true)
                    . ' while expecting ' . $state . '.',
                $this->getSession()
            );
        }
    }

    /**
     * Wait until a deterministic provider SDK double and the production adapter are ready.
     *
     * @Then /^the deterministic VideoTrack "(?P<provider>youtube|vimeo)" provider is ready$/
     * @param string $provider Expected provider type.
     */
    public function the_deterministic_videotrack_provider_is_ready(string $provider): void {
        $expected = json_encode($provider, JSON_THROW_ON_ERROR);
        $condition = "(function() {var fixture = window.__videotrackBehatProvider;"
            . "return !!fixture && fixture.kind === " . $expected . " && fixture.ready === true; }())";
        if (!$this->getSession()->wait(5000, $condition)) {
            throw new ExpectationException(
                'The deterministic VideoTrack ' . $provider . ' provider did not become ready.',
                $this->getSession()
            );
        }
    }

    /**
     * Drive play or pause through the deterministic provider SDK surface.
     *
     * @When /^I (?P<action>play|pause) the deterministic VideoTrack "(?P<provider>youtube|vimeo)" provider$/
     * @param string $action Requested provider action.
     * @param string $provider Expected provider type.
     */
    public function i_control_the_deterministic_videotrack_provider(string $action, string $provider): void {
        $expected = json_encode($provider, JSON_THROW_ON_ERROR);
        $requested = json_encode($action, JSON_THROW_ON_ERROR);
        $this->getSession()->executeScript(
            "(function() {var fixture = window.__videotrackBehatProvider;"
                . "if (!fixture || fixture.kind !== " . $expected . ") {"
                . "throw new Error('VideoTrack provider fixture not found');}"
                . "fixture[" . $requested . "]();}())"
        );
        $expectedstate = $action === 'play' ? 1 : 2;
        $condition = "(function() {var fixture = window.__videotrackBehatProvider;"
            . "return !!fixture && fixture.getState() === " . $expectedstate . ";}())";
        if (!$this->getSession()->wait(3000, $condition)) {
            throw new ExpectationException(
                'The deterministic VideoTrack ' . $provider . ' provider did not ' . $action . '.',
                $this->getSession()
            );
        }
    }

    /**
     * Assert deterministic provider playback state without invoking another command.
     *
     * @Then /^the "(?P<provider>youtube|vimeo)" provider playback is "(?P<state>playing|paused)"$/
     * @param string $provider Expected provider type.
     * @param string $state Expected playback state.
     */
    public function the_deterministic_videotrack_provider_playback_is(string $provider, string $state): void {
        $expectedprovider = json_encode($provider, JSON_THROW_ON_ERROR);
        $expectedstate = $state === 'playing' ? 1 : 2;
        $condition = "(function() {var fixture = window.__videotrackBehatProvider;"
            . "return !!fixture && fixture.kind === " . $expectedprovider
            . " && fixture.getState() === " . $expectedstate . ";}())";
        if (!$this->getSession()->wait(3000, $condition)) {
            throw new ExpectationException(
                'The deterministic VideoTrack ' . $provider . ' provider is not ' . $state . '.',
                $this->getSession()
            );
        }
    }

    /**
     * Seek via the deterministic provider SDK surface.
     *
     * @When /^I seek the deterministic VideoTrack "(?P<provider>youtube|vimeo)" provider to "(?P<seconds>[0-9.]+)" seconds$/
     * @param string $provider Expected provider type.
     * @param float $seconds Requested timestamp.
     */
    public function i_seek_the_deterministic_videotrack_provider_to_seconds(
        string $provider,
        float $seconds
    ): void {
        $expected = json_encode($provider, JSON_THROW_ON_ERROR);
        $target = json_encode($seconds, JSON_THROW_ON_ERROR);
        $this->getSession()->executeScript(
            "(function() {var fixture = window.__videotrackBehatProvider;"
                . "if (!fixture || fixture.kind !== " . $expected . ") {"
                . "throw new Error('VideoTrack provider fixture not found');}"
                . "fixture.seek(" . $target . ");}())"
        );
    }

    /**
     * Require the provider timestamp to remain inside a range for a complete polling window.
     *
     * @Then /^the "(?P<provider>youtube|vimeo)" provider time is between "(?P<minimum>[0-9.]+)" and "(?P<maximum>[0-9.]+)"$/
     * @param string $provider Expected provider type.
     * @param float $minimum Minimum accepted timestamp.
     * @param float $maximum Maximum accepted timestamp.
     */
    public function the_deterministic_videotrack_provider_time_is_between(
        string $provider,
        float $minimum,
        float $maximum
    ): void {
        $condition = sprintf(
            "(function() {var fixture = window.__videotrackBehatProvider;"
                . "if (!fixture || fixture.kind !== %s) {return false;}"
                . "var time = fixture.getTime();"
                . "var key = '%.6F:%.6F';"
                . "if (fixture.rangeKey !== key) {fixture.rangeKey = key; fixture.rangeSince = 0;}"
                . "if (time < %.6F || time > %.6F) {fixture.rangeSince = 0; return false;}"
                . "fixture.rangeSince = fixture.rangeSince || Date.now();"
                . "return Date.now() - fixture.rangeSince >= 750;}())",
            json_encode($provider, JSON_THROW_ON_ERROR),
            $minimum,
            $maximum,
            $minimum,
            $maximum
        );
        if (!$this->getSession()->wait(7000, $condition)) {
            $time = $this->getSession()->evaluateScript(
                "(function() {var fixture = window.__videotrackBehatProvider;"
                    . "return fixture ? fixture.getTime() : null;}())"
            );
            throw new ExpectationException(
                'Deterministic VideoTrack ' . $provider . ' time ' . var_export($time, true)
                    . ' is outside the stable range ' . $minimum . '-' . $maximum . ' seconds.',
                $this->getSession()
            );
        }
    }

    /**
     * Verify that a blocked provider seek returns to the persisted validated frontier.
     *
     * @Then /^the "(?P<provider>youtube|vimeo)" provider matches "(?P<username>[^"]+)" frontier in "(?P<activityname>[^"]+)"$/
     * @param string $provider Expected provider type.
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     */
    public function the_deterministic_videotrack_provider_time_matches_the_validated_frontier(
        string $provider,
        string $username,
        string $activityname
    ): void {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $videotrack = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $conditions = [
            'videotrackid' => (int)$videotrack->id,
            'userid' => (int)$user->id,
        ];
        $expectedprovider = json_encode($provider, JSON_THROW_ON_ERROR);
        $deadline = microtime(true) + 7.0;
        $stablefrontier = null;
        $stablesince = null;
        $time = null;
        $frontier = null;
        do {
            $frontier = (float)$DB->get_field('videotrack_state', 'lastposition', $conditions, MUST_EXIST);
            $time = $this->getSession()->evaluateScript(
                "(function() {var fixture = window.__videotrackBehatProvider;"
                    . "if (!fixture || fixture.kind !== " . $expectedprovider . ") {return null;}"
                    . "return fixture.getTime();}())"
            );
            if (is_numeric($time) && abs((float)$time - $frontier) <= 1.25) {
                if ($stablefrontier === null || abs($stablefrontier - $frontier) > 0.001) {
                    $stablefrontier = $frontier;
                    $stablesince = microtime(true);
                } else if ($stablesince !== null && microtime(true) - $stablesince >= 0.75) {
                    $unchanged = (float)$DB->get_field(
                        'videotrack_state',
                        'lastposition',
                        $conditions,
                        MUST_EXIST
                    );
                    if (abs($unchanged - $stablefrontier) <= 0.001) {
                        return;
                    }
                    $stablefrontier = null;
                    $stablesince = null;
                }
            } else {
                $stablefrontier = null;
                $stablesince = null;
            }
            usleep(100000);
        } while (microtime(true) < $deadline);

        throw new ExpectationException(
            'Deterministic VideoTrack ' . $provider . ' time ' . var_export($time, true)
                . ' did not stably match validated frontier ' . var_export($frontier, true) . '.',
            $this->getSession()
        );
    }

    /**
     * Assert a real browser play/pause lifecycle against the server-authoritative ledger.
     *
     * @Then /^the playback credit window for "(?P<username>[^"]+)" in "(?P<activityname>[^"]+)" is closed by an accepted pause$/
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     */
    public function the_playback_credit_window_is_closed_by_an_accepted_pause(
        string $username,
        string $activityname
    ): void {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $videotrack = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $conditions = [
            'videotrackid' => (int)$videotrack->id,
            'userid' => (int)$user->id,
        ];
        $deadline = microtime(true) + 5.0;
        $playstart = false;
        $pause = false;
        $state = false;
        do {
            $playstarts = $DB->get_records(
                'videotrack_seg',
                $conditions + ['endreason' => 'playstart'],
                'id DESC',
                '*',
                0,
                1
            );
            $pauses = $DB->get_records(
                'videotrack_seg',
                $conditions + ['endreason' => 'pause'],
                'id DESC',
                '*',
                0,
                1
            );
            $playstart = $playstarts ? reset($playstarts) : false;
            $pause = $pauses ? reset($pauses) : false;
            $state = $DB->get_record('videotrack_state', $conditions);
            if (
                $playstart
                && $pause
                && $state
                && (int)$playstart->servervalidated === 0
                && (int)$pause->servervalidated === 1
                && hash_equals((string)$playstart->sessionid, (string)$pause->sessionid)
                && (float)$pause->videotimeend > (float)$pause->videotimestart
                && (string)$state->serverplaybacksessionid === ''
                && (int)$state->serverlastactivity === 0
            ) {
                return;
            }
            usleep(100000);
        } while (microtime(true) < $deadline);

        throw new ExpectationException(
            'The VideoTrack playback-credit lifecycle for "' . $activityname . '" and user "' . $username
                . '" did not persist a same-session accepted pause and close the active server window. '
                . 'playstart=' . ($playstart ? json_encode($playstart) : 'missing')
                . '; pause=' . ($pause ? json_encode($pause) : 'missing')
                . '; state=' . ($state ? json_encode($state) : 'missing') . '.',
            $this->getSession()
        );
    }

    /**
     * Seed validated watched evidence for a learner before a browser interaction scenario.
     *
     * @Given /^"(?P<username>[^"]+)" watched "(?P<activityname>[^"]+)" through "(?P<seconds>[0-9.]+)" seconds$/
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     * @param float $seconds End of the validated watched interval.
     */
    public function the_user_has_watched_videotrack_through_seconds(
        string $username,
        string $activityname,
        float $seconds
    ): void {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $videotrack = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance(
            'videotrack',
            (int)$videotrack->id,
            (int)$videotrack->course,
            false,
            MUST_EXIST
        );
        $duration = max(1.0, (float)($videotrack->durationseconds ?? 0));
        $end = min($duration, max(0.5, $seconds));
        $now = time();
        $videoid = (string)($videotrack->videoid ?? '');
        if ($videoid === '') {
            $videoid = 'behat-video';
        }

        $DB->delete_records('videotrack_seg', [
            'videotrackid' => (int)$videotrack->id,
            'userid' => (int)$user->id,
        ]);
        $DB->delete_records('videotrack_state', [
            'videotrackid' => (int)$videotrack->id,
            'userid' => (int)$user->id,
        ]);

        $DB->insert_record('videotrack_seg', (object)[
            'videotrackid' => (int)$videotrack->id,
            'courseid' => (int)$videotrack->course,
            'cmid' => (int)$cm->id,
            'userid' => (int)$user->id,
            'videoid' => $videoid,
            'sessionid' => 'behatseed',
            'requestid' => 'behatseed-' . (int)$videotrack->id . '-' . (int)$user->id,
            'wallclockstart' => $now - (int)ceil($end),
            'wallclockend' => $now,
            'videotimestart' => 0.0,
            'videotimeend' => $end,
            'playbackrate' => 1.0,
            'endreason' => 'heartbeat',
            'servervalidated' => 1,
            'timecreated' => $now,
        ]);
        $DB->insert_record('videotrack_state', (object)[
            'videotrackid' => (int)$videotrack->id,
            'courseid' => (int)$videotrack->course,
            'cmid' => (int)$cm->id,
            'userid' => (int)$user->id,
            'videoid' => $videoid,
            'lastposition' => $end,
            'durationseconds' => $duration,
            'serverlastactivity' => $now * 1000,
            'serverbudgetseconds' => $end,
            'servercreditedseconds' => $end,
            'uniquecoveredseconds' => $end,
            'completionpercent' => round(($end / $duration) * 100, 2),
            'intervaljson' => json_encode([[0.0, $end]], JSON_THROW_ON_ERROR),
            'iscompleted' => 0,
            'timemodified' => $now,
            'timecreated' => $now,
        ]);

        if ((int)$cm->completion === COMPLETION_TRACKING_AUTOMATIC) {
            $cminfo = \cm_info::create($cm);
            $state = \mod_videotrack\local\tracker::refresh_completion(
                $videotrack,
                $cminfo,
                (int)$user->id
            );
            $completion = new \completion_info(get_course((int)$videotrack->course));
            \mod_videotrack\local\tracker::update_moodle_completion_if_changed(
                $completion,
                $cminfo,
                (bool)$state->iscompleted,
                (int)$user->id
            );
        }
    }

    /**
     * Assert the persisted Moodle completion state after a browser interaction.
     *
     * @Then /^Moodle completion for "(?P<username>[^"]+)" in "(?P<activityname>[^"]+)" is "(?P<state>complete|incomplete)"$/
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     * @param string $state Expected Moodle completion state.
     */
    public function the_moodle_completion_state_for_videotrack_is(
        string $username,
        string $activityname,
        string $state
    ): void {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $videotrack = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance(
            'videotrack',
            (int)$videotrack->id,
            (int)$videotrack->course,
            false,
            MUST_EXIST
        );
        $record = $DB->get_record('course_modules_completion', [
            'coursemoduleid' => (int)$cm->id,
            'userid' => (int)$user->id,
        ]);
        $actualstate = $record ? (int)$record->completionstate : COMPLETION_INCOMPLETE;
        $completestates = [COMPLETION_COMPLETE, COMPLETION_COMPLETE_PASS, COMPLETION_COMPLETE_FAIL];
        $actualcomplete = in_array($actualstate, $completestates, true);
        $expectedcomplete = $state === 'complete';

        if ($actualcomplete !== $expectedcomplete) {
            throw new ExpectationException(
                'Moodle completion for "' . $activityname . '" and user "' . $username
                    . '" is state ' . $actualstate . ' while expecting ' . $state . '.',
                $this->getSession()
            );
        }
    }

    /**
     * Assert the Forum composer URL carries a safe VideoTrack timestamp.
     *
     * @Then /^the VideoTrack Forum time is between "(?P<minimum>[0-9.]+)" and "(?P<maximum>[0-9.]+)"$/
     * @param float $minimum Minimum accepted timestamp.
     * @param float $maximum Maximum accepted timestamp.
     */
    public function the_videotrack_forum_time_should_be_between(float $minimum, float $maximum): void {
        $query = parse_url($this->getSession()->getCurrentUrl(), PHP_URL_QUERY);
        $params = [];
        if (is_string($query)) {
            parse_str($query, $params);
        }
        $time = isset($params['time']) && is_numeric($params['time']) ? (float)$params['time'] : null;
        if ($time === null || $time < $minimum || $time > $maximum) {
            throw new ExpectationException(
                'VideoTrack Forum composer time ' . var_export($time, true)
                    . ' is outside the expected range ' . $minimum . '-' . $maximum . ' seconds.',
                $this->getSession()
            );
        }
    }

    /**
     * Assert the persisted seek boundary and the absence of aggregate credit for the skipped gap.
     *
     * @Then /^the seek segment for "(?P<username>[^"]+)" in "(?P<activityname>[^"]+)" matches the pre-seek time$/
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     */
    public function the_latest_seek_segment_matches_the_pre_seek_browser_time(
        string $username,
        string $activityname
    ): void {
        global $DB;

        $expected = $this->getSession()->evaluateScript(
            '(function() { return window.__videotrackBehatPreSeekTime ?? null; }())'
        );
        if (!is_numeric($expected)) {
            throw new ExpectationException(
                'The pre-seek VideoTrack browser timestamp was not captured.',
                $this->getSession()
            );
        }

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $videotrack = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $conditions = [
            'videotrackid' => (int)$videotrack->id,
            'userid' => (int)$user->id,
            'endreason' => 'seek',
        ];
        $deadline = microtime(true) + 5.0;
        $segment = false;
        do {
            $records = $DB->get_records('videotrack_seg', $conditions, 'id DESC', '*', 0, 1);
            if ($records) {
                $segment = reset($records);
                break;
            }
            usleep(100000);
        } while (microtime(true) < $deadline);

        if (!$segment) {
            throw new ExpectationException(
                'No persisted VideoTrack seek segment was found for "' . $activityname . '" and user "'
                    . $username . '".',
                $this->getSession()
            );
        }

        $expectedend = (float)$expected;
        $actualstart = (float)$segment->videotimestart;
        $actualend = (float)$segment->videotimeend;
        $state = $DB->get_record('videotrack_state', [
            'videotrackid' => (int)$videotrack->id,
            'userid' => (int)$user->id,
        ], '*', MUST_EXIST);
        $covered = (float)$state->uniquecoveredseconds;
        $lastposition = (float)$state->lastposition;
        $maximumallowed = $expectedend + 0.75;
        if (
            $actualstart < 0.0
            || $actualend <= $actualstart
            || abs($actualend - $expectedend) > 0.75
            || $covered > $maximumallowed
            || $lastposition > $maximumallowed
        ) {
            throw new ExpectationException(
                'Persisted seek segment [' . $actualstart . ', ' . $actualend . '] with servervalidated='
                    . (int)$segment->servervalidated . ', covered=' . $covered . ' and lastposition='
                    . $lastposition . ' does not preserve pre-seek browser boundary ' . $expectedend
                    . ' within 0.75 seconds.',
                $this->getSession()
            );
        }
    }
}
