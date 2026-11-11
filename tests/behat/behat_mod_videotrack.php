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
    /** @var array End-of-video observations used only by read-only replay assertions. */
    private array $endedbaselines = [];

    /**
     * Verify only the expected denial before restoring a valid page for Moodle's automatic error hook.
     *
     * @When /^I request the VideoTrack report for "([^"]+)" and forbidden learner "([^"]+)"$/
     * @param string $activityname VideoTrack activity name.
     * @param string $username Deliberately out-of-scope learner username.
     */
    public function i_request_the_videotrack_report_for_forbidden_learner(string $activityname, string $username): void {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $this->i_open_the_videotrack_report_for_learner($activityname, $username);
        $page = $this->getSession()->getPage();
        $fatal = $page->findAll('css', '[data-rel="fatalerror"]');
        $link = $page->find('css', '.errorcode a[href]');
        $path = $link ? parse_url($link->getAttribute('href'), PHP_URL_PATH) : '';
        if (
            count($fatal) !== 1
            || strpos($fatal[0]->getText(), get_string('invaliduser', 'error')) === false
            || !is_string($path) || preg_match('~/invaliduser/?$~', $path) !== 1
            || $page->find('css', '#id_userid') !== null
            || $page->find('css', 'table.generaltable') !== null
            || strpos($page->getText(), fullname($user)) !== false
            || $page->find('css', '[data-rel="debugging"], [data-rel="phpdebugmessage"]') !== null
        ) {
            throw new ExpectationException('Expected an isolated invaliduser denial without learner data.', $this->getSession());
        }
        // Keep Moodle's automatic exception checks active on a normal, authorised report page.
        $this->i_open_the_videotrack_report_for_learner($activityname, 'all');
    }

    /**
     * Verify the selected learner and the sole rendered progress row independently from available choices.
     *
     * @Then /^the VideoTrack report shows only learner "([^"]+)"$/
     * @param string $username Expected selected learner username.
     */
    public function the_videotrack_report_shows_only_learner(string $username): void {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $page = $this->getSession()->getPage();
        $select = $page->find('css', '#id_userid');
        $tables = [];
        foreach ($page->findAll('css', 'table') as $table) {
            $caption = $table->find('css', 'caption');
            if ($caption && trim($caption->getText()) === get_string('report:perstudent', 'mod_videotrack')) {
                $tables[] = $table;
            }
        }
        if (!$select || (int)$select->getValue() !== (int)$user->id || count($tables) !== 1) {
            throw new ExpectationException('The report selection or per-student progress table is incorrect.', $this->getSession());
        }
        $rows = $tables[0]->findAll('css', 'tbody tr');
        $label = count($rows) === 1 ? $rows[0]->find('css', 'td') : null;
        $name = fullname($user);
        $allowed = [$name, $name . ' (' . $user->email . ')'];
        if (!$label || !in_array(trim($label->getText()), $allowed, true)) {
            throw new ExpectationException('Filtered progress must contain exactly the selected learner.', $this->getSession());
        }
    }

    /**
     * Observe the effective learner-tracking switch exposed by the actual activity page.
     *
     * @Then /^the VideoTrack learner tracking policy is "(enabled|disabled)"$/
     * @param string $policy Expected effective tracking switch.
     */
    public function the_videotrack_learner_tracking_policy_is(string $policy): void {
        $actual = $this->getSession()->evaluateScript(
            "(function() {var node = document.querySelector('script[id^=\"mod-videotrack-player-config-\"]');"
                . "return node ? JSON.parse(node.textContent).trackingenabled : null;}())"
        );
        if ($actual !== ($policy === 'enabled')) {
            throw new ExpectationException('Unexpected effective VideoTrack learner-tracking policy.', $this->getSession());
        }
    }

    /**
     * Require staff preview to leave no plugin playback records or completed native viewing state.
     *
     * @Then /^preview creates no learner tracking for "([^"]+)" in "([^"]+)"$/
     * @param string $username Previewing Moodle username.
     * @param string $activityname VideoTrack activity name.
     */
    public function preview_creates_no_learner_tracking(string $username, string $activityname): void {
        global $DB;

        $conditions = $this->browser_activity_conditions($username, $activityname);
        $native = $DB->get_record('course_modules_completion', [
            'coursemoduleid' => $conditions['cmid'],
            'userid' => $conditions['userid'],
        ]);
        if (
            $DB->record_exists('videotrack_seg', $conditions)
            || $DB->record_exists('videotrack_state', $conditions)
            || ($native && (int)$native->completionstate !== COMPLETION_INCOMPLETE)
        ) {
            throw new ExpectationException('Staff preview created learner playback or completion records.', $this->getSession());
        }
    }

    /**
     * Visit the real report endpoint, including deliberate out-of-scope learner requests.
     *
     * @When /^I open the VideoTrack report for "([^"]+)" and learner "([^"]+)"$/
     * @param string $activityname VideoTrack activity name.
     * @param string $username Learner username, or all for the unfiltered report.
     */
    public function i_open_the_videotrack_report_for_learner(string $activityname, string $username): void {
        global $DB;

        $activity = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('videotrack', (int)$activity->id, (int)$activity->course, false, MUST_EXIST);
        $params = ['id' => (int)$cm->id, 'mode' => 'student'];
        if ($username !== 'all') {
            $params['userid'] = (int)$DB->get_field('user', 'id', ['username' => $username], MUST_EXIST);
        }
        $url = new moodle_url('/mod/videotrack/report.php', $params);
        $this->getSession()->visit($this->locate_path($url->out_as_local_url(false)));
    }

    /**
     * Compare actual rendered report choices with the exact expected learner ids.
     *
     * @Then /^the VideoTrack report learner choices are "([^"]+)"$/
     * @param string $usernames Comma-separated usernames, or none for no visible learners.
     */
    public function the_videotrack_report_learner_choices_are(string $usernames): void {
        global $DB;

        $select = $this->getSession()->getPage()->find('css', '#id_userid');
        if (!$select) {
            throw new ExpectationException('The report learner selector is missing.', $this->getSession());
        }
        $actual = [];
        foreach ($select->findAll('css', 'option') as $option) {
            $id = (int)$option->getAttribute('value');
            if ($id > 0) {
                $actual[] = $id;
            }
        }
        $expected = [];
        foreach ($usernames === 'none' ? [] : explode(',', $usernames) as $username) {
            $expected[] = (int)$DB->get_field('user', 'id', ['username' => trim($username)], MUST_EXIST);
        }
        sort($actual, SORT_NUMERIC);
        sort($expected, SORT_NUMERIC);
        if ($actual !== $expected) {
            throw new ExpectationException(
                'Report learner ids differ: expected ' . json_encode($expected) . '; actual ' . json_encode($actual) . '.',
                $this->getSession()
            );
        }
    }

    /**
     * Request a real media/SDK rate selected by the fixture, or an intentional above-cap probe.
     *
     * @When /^I request VideoTrack playback rate "([0-9.]+)"$/
     * @param float $rate Requested media playback rate.
     */
    public function i_request_videotrack_playback_rate(float $rate): void {
        $target = json_encode($rate, JSON_THROW_ON_ERROR);
        $script = "(function() {var requested = " . $target . ";"
            . "var node = document.querySelector('script[id^=\"mod-videotrack-player-config-\"]');"
            . "if (!node) {throw new Error('VideoTrack configuration missing');}"
            . "var config = JSON.parse(node.textContent); var speeds = config.playbackspeeds;"
            . "var cap = Number(config.maxplaybackrate) / 100;"
            . "if (!config.allowplaybackratechange || !Array.isArray(speeds)"
            . "|| (speeds.map(Number).indexOf(requested) === -1 && !(cap > 0 && requested > cap)))"
            . "{throw new Error('Test rate must be selected or intentionally above the configured cap');}"
            . "var media = document.querySelector('#mod-videotrack-player video');"
            . "if (media) {media.playbackRate = requested; return;}"
            . "var fixture = window.__videotrackBehatProvider;"
            . "if (!fixture || !fixture.player) {throw new Error('VideoTrack SDK fixture missing');}"
            . "fixture.player.setPlaybackRate(requested);}())";
        $this->getSession()->executeScript($script);
    }

    /**
     * Observe a native or asynchronous SDK rate without treating a Promise as a boolean.
     *
     * @Then /^the VideoTrack playback rate is "([0-9.]+)"$/
     * @param float $rate Expected effective media rate.
     */
    public function the_videotrack_playback_rate_is(float $rate): void {
        $target = json_encode($rate, JSON_THROW_ON_ERROR);
        $this->getSession()->executeScript('window.__videotrackRateProbe = {value: null};');
        $condition = "(function() {var expected = " . $target . ";"
            . "var media = document.querySelector('#mod-videotrack-player video');"
            . "if (media) {return Math.abs(media.playbackRate - expected) <= 0.001;}"
            . "var fixture = window.__videotrackBehatProvider; var probe = window.__videotrackRateProbe;"
            . "if (!fixture || !fixture.player || !probe) {return false;}"
            . "var value = fixture.player.getPlaybackRate();"
            . "if (value && typeof value.then === 'function') {"
            . "value.then(function(actual) {probe.value = Number(actual);});"
            . "return typeof probe.value === 'number' && Math.abs(probe.value - expected) <= 0.001;}"
            . "return Math.abs(Number(value) - expected) <= 0.001;}())";
        if (!$this->getSession()->wait(5000, $condition)) {
            throw new ExpectationException('VideoTrack rate did not settle at ' . $rate . '.', $this->getSession());
        }
    }

    /**
     * Wait for natural fixture end without seeking to it or accelerating the clock.
     *
     * @Then /^the VideoTrack player reaches its natural end$/
     */
    public function the_videotrack_player_reaches_its_natural_end(): void {
        $condition = "(function() {var media = document.querySelector('#mod-videotrack-player video');"
            . "if (media) {return media.ended && media.paused && media.currentTime >= 59.75;}"
            . "var fixture = window.__videotrackBehatProvider; if (!fixture) {return false;}"
            . "var time = fixture.getTime(); var state = fixture.getState();"
            . "return time >= 59.75 && (state === 0 || state === 2);}())";
        if (!$this->getSession()->wait(45000, $condition)) {
            throw new ExpectationException('VideoTrack did not reach the natural 60-second end.', $this->getSession());
        }
    }

    /**
     * Require an accepted end, closed credit and nearly complete browser-earned coverage.
     *
     * @Then /^an accepted end closes playback for "([^"]+)" in "([^"]+)"$/
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     */
    public function an_accepted_end_closes_playback(string $username, string $activityname): void {
        $conditions = $this->browser_activity_conditions($username, $activityname);
        $deadline = microtime(true) + 8.0;
        do {
            [$start, $end, $state] = $this->read_browser_playback_cycle($conditions, 'ended');
            $intervals = json_decode((string)($state->intervaljson ?? '[]'), true);
            $coversreplay = false;
            foreach (is_array($intervals) ? $intervals : [] as $interval) {
                if (
                    is_array($interval) && count($interval) >= 2
                    && (float)$interval[0] <= 5.0 && (float)$interval[1] >= 20.0
                ) {
                    $coversreplay = true;
                }
            }
            if (
                $this->browser_playback_cycle_is_closed($start, $end, $state)
                && (float)$end->videotimeend >= 59.0 && (float)$end->videotimeend <= 60.001
                && (float)$state->lastposition >= 59.0 && (float)$state->lastposition <= 60.001
                && (float)$state->uniquecoveredseconds >= 58.0 && (float)$state->uniquecoveredseconds <= 60.001
                && (float)$state->completionpercent >= 95.0 && (float)$state->completionpercent <= 100.0
                && (int)$state->iscompleted === 1 && $coversreplay
            ) {
                $key = $conditions['videotrackid'] . ':' . $conditions['userid'];
                $this->endedbaselines[$key] = [
                    'unique' => (float)$state->uniquecoveredseconds,
                    'percent' => (float)$state->completionpercent,
                    'endedid' => (int)$end->id,
                    'sessionid' => (string)$end->sessionid,
                ];
                return;
            }
            usleep(100000);
        } while (microtime(true) < $deadline);
        throw new ExpectationException(
            'Natural end did not persist accepted closed credit and bounded complete coverage in ' . $activityname . '.',
            $this->getSession()
        );
    }

    /**
     * Require a fresh replay handshake and accepted pause without counting watched footage twice.
     *
     * @Then /^replaying watched footage adds no coverage for "([^"]+)" in "([^"]+)"$/
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     */
    public function replaying_watched_footage_adds_no_coverage(string $username, string $activityname): void {
        $conditions = $this->browser_activity_conditions($username, $activityname);
        $key = $conditions['videotrackid'] . ':' . $conditions['userid'];
        $baseline = $this->endedbaselines[$key] ?? null;
        if ($baseline === null) {
            throw new ExpectationException('An accepted natural-end snapshot is required before replay.', $this->getSession());
        }
        $deadline = microtime(true) + 8.0;
        do {
            [$start, $pause, $state] = $this->read_browser_playback_cycle($conditions, 'pause');
            if (
                $this->browser_playback_cycle_is_closed($start, $pause, $state)
                && (int)$start->id > $baseline['endedid']
                && hash_equals($baseline['sessionid'], (string)$start->sessionid)
                && (float)$start->videotimeend >= 4.75 && (float)$start->videotimeend <= 6.25
                && (float)$pause->videotimeend >= 7.5 && (float)$pause->videotimeend <= 15.0
                && abs((float)$state->uniquecoveredseconds - $baseline['unique']) <= 0.001
                && abs((float)$state->completionpercent - $baseline['percent']) <= 0.001
                && (int)$state->iscompleted === 1
            ) {
                return;
            }
            usleep(100000);
        } while (microtime(true) < $deadline);
        throw new ExpectationException(
            'Replay did not retain unique coverage/completion with a fresh accepted playback cycle in ' . $activityname . '.',
            $this->getSession()
        );
    }

    /**
     * Resolve one exact activity/user/course-module scope without writing test progress.
     *
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     * @return array Database conditions for this playback scope.
     */
    private function browser_activity_conditions(string $username, string $activityname): array {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $activity = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('videotrack', (int)$activity->id, (int)$activity->course, false, MUST_EXIST);
        return [
            'videotrackid' => (int)$activity->id,
            'userid' => (int)$user->id,
            'courseid' => (int)$activity->course,
            'cmid' => (int)$cm->id,
        ];
    }

    /**
     * Read the latest real handshake, requested terminal event and aggregate state.
     *
     * @param array $conditions Exact playback scope.
     * @param string $reason Terminal reason to observe.
     * @return array Latest start, terminal and state records, or false for absent records.
     */
    private function read_browser_playback_cycle(array $conditions, string $reason): array {
        global $DB;

        $starts = $DB->get_records('videotrack_seg', $conditions + ['endreason' => 'playstart'], 'id DESC', '*', 0, 1);
        $terminals = $DB->get_records('videotrack_seg', $conditions + ['endreason' => $reason], 'id DESC', '*', 0, 1);
        return [
            $starts ? reset($starts) : false,
            $terminals ? reset($terminals) : false,
            $DB->get_record('videotrack_state', $conditions),
        ];
    }

    /**
     * Check a positive same-session terminal write after its start with no active server credit.
     *
     * @param \stdClass|false $start Latest playback start.
     * @param \stdClass|false $terminal Latest requested terminal row.
     * @param \stdClass|false $state Actual aggregate state.
     * @return bool Whether this exact playback cycle was accepted and closed.
     */
    private function browser_playback_cycle_is_closed($start, $terminal, $state): bool {
        return $start && $terminal && $state
            && (int)$start->servervalidated === 0 && (int)$terminal->servervalidated === 1
            && (int)$terminal->id > (int)$start->id
            && hash_equals((string)$start->sessionid, (string)$terminal->sessionid)
            && (float)$terminal->videotimeend > (float)$terminal->videotimestart
            && (string)$state->serverplaybacksessionid === '' && (int)$state->serverlastactivity === 0;
    }

    /**
     * Read the effective per-activity attention configuration without changing it.
     *
     * @Then /^the VideoTrack attention policy is "(enabled|disabled)" from "([0-9]+)" to "([0-9]+)" seconds$/
     * @param string $state Expected switch state.
     * @param int $minimum Expected effective minimum seconds.
     * @param int $maximum Expected effective maximum seconds.
     */
    public function the_videotrack_attention_policy_is(string $state, int $minimum, int $maximum): void {
        $actual = $this->getSession()->evaluateScript(
            "(function() {var node = document.querySelector('script[id^=\"mod-videotrack-player-config-\"]');"
                . "if (!node) {return null;} var config = JSON.parse(node.textContent);"
                . "return {enabled: !!config.randomfocuspauses, min: config.randompauseminseconds,"
                . "max: config.randompausemaxseconds};}())"
        );
        $expected = ['enabled' => $state === 'enabled', 'min' => $minimum, 'max' => $maximum];
        if ($actual != $expected) {
            throw new ExpectationException(
                'Effective VideoTrack attention policy does not match ' . json_encode($expected)
                    . '; actual=' . json_encode($actual),
                $this->getSession()
            );
        }
    }

    /**
     * Slow the unchanged 60-second fixtures so the real one-minute deadline precedes video end.
     *
     * @When /^I use half speed for the VideoTrack attention check$/
     */
    public function i_use_half_speed_for_the_videotrack_attention_check(): void {
        $applied = $this->getSession()->evaluateScript(
            "(function() {var node = document.querySelector('script[id^=\"mod-videotrack-player-config-\"]');"
                . "if (!node) {return false;} var config = JSON.parse(node.textContent);"
                . "var speeds = config.playbackspeeds;"
                . "if (!Array.isArray(speeds) || speeds.map(Number).indexOf(0.5) === -1"
                . "|| config.allowplaybackratechange === false) {return false;}"
                . "var media = document.querySelector('#mod-videotrack-player video');"
                . "if (media) {media.playbackRate = 0.5; return media.playbackRate === 0.5;}"
                . "var fixture = window.__videotrackBehatProvider;"
                . "if (!fixture || !fixture.player) {return false;}"
                . "fixture.player.setPlaybackRate(0.5); return true;}())"
        );
        if (!$applied) {
            throw new ExpectationException(
                'Half speed must be allowed by the activity before the attention check.',
                $this->getSession()
            );
        }
    }

    /**
     * Observe the actual timer-driven provider pause and student notice, without clock overrides.
     *
     * @Then /^the VideoTrack attention check pauses playback$/
     */
    public function the_videotrack_attention_check_pauses_playback(): void {
        $condition = "(function() {var node = document.querySelector('script[id^=\"mod-videotrack-player-config-\"]');"
            . "if (!node) {return false;} var config = JSON.parse(node.textContent);"
            . "var media = document.querySelector('#mod-videotrack-player video');"
            . "var fixture = window.__videotrackBehatProvider;"
            . "var paused = media ? media.paused : fixture && fixture.getState() === 2;"
            . "var time = media ? media.currentTime : fixture && fixture.getTime();"
            . "return !!paused && time >= 20 && time <= 40"
            . "&& document.body.textContent.indexOf(config.randompausedlabel) !== -1;}())";
        if (!$this->getSession()->wait(75000, $condition)) {
            throw new ExpectationException(
                'The real one-minute attention deadline did not pause playback near 30 video seconds.',
                $this->getSession()
            );
        }
    }

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
     * Assert that browser completion scenarios start without validated ledger evidence.
     *
     * @Then /^no validated playback exists for "(?P<username>[^"]+)" in "(?P<activityname>[^"]+)"$/
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     */
    public function no_validated_playback_exists(string $username, string $activityname): void {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $activity = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $conditions = [
            'videotrackid' => (int)$activity->id,
            'userid' => (int)$user->id,
            'servervalidated' => 1,
        ];
        if ($DB->record_exists('videotrack_seg', $conditions)) {
            throw new ExpectationException(
                'Validated VideoTrack evidence already exists before browser playback in "' . $activityname . '".',
                $this->getSession()
            );
        }
    }

    /**
     * Wait for actual browser writes to complete both aggregate and native Moodle completion.
     *
     * @Then /^browser playback completes Moodle tracking for "(?P<username>[^"]+)" in "(?P<activityname>[^"]+)"$/
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     */
    public function browser_playback_completes_moodle_tracking(string $username, string $activityname): void {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $activity = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance(
            'videotrack',
            (int)$activity->id,
            (int)$activity->course,
            false,
            MUST_EXIST
        );
        $required = (float)$activity->durationseconds * (float)$activity->completionpercent / 100;
        if ($required <= 0 || (int)$cm->completion !== COMPLETION_TRACKING_AUTOMATIC) {
            throw new \coding_exception('Browser completion assertions require a positive automatic viewing threshold.');
        }
        $conditions = ['videotrackid' => (int)$activity->id, 'userid' => (int)$user->id];
        $deadline = microtime(true) + 5.0;
        $state = false;
        $native = false;
        $pause = false;
        do {
            $state = $DB->get_record('videotrack_state', $conditions);
            $native = $DB->get_record('course_modules_completion', [
                'coursemoduleid' => (int)$cm->id,
                'userid' => (int)$user->id,
            ]);
            $pauses = $DB->get_records(
                'videotrack_seg',
                $conditions + ['servervalidated' => 1, 'endreason' => 'pause'],
                'id DESC',
                '*',
                0,
                1
            );
            $pause = $pauses ? reset($pauses) : false;
            if (
                $state
                && $native
                && $pause
                && (int)$state->iscompleted === 1
                && (float)$state->uniquecoveredseconds >= $required
                && (float)$state->completionpercent >= (float)$activity->completionpercent
                && (int)$native->completionstate === COMPLETION_COMPLETE
                && (float)$pause->videotimeend > (float)$pause->videotimestart
                && preg_match('/^(?:[a-f0-9]{32}|sess[0-9a-z]{16,48})$/', (string)$pause->sessionid) === 1
            ) {
                return;
            }
            usleep(100000);
        } while (microtime(true) < $deadline);

        throw new ExpectationException(
            'Browser playback did not persist validated viewing and native Moodle completion in "' . $activityname . '". '
                . 'required=' . $required
                . '; state=' . ($state ? json_encode($state) : 'missing')
                . '; native=' . ($native ? json_encode($native) : 'missing')
                . '; pause=' . ($pause ? json_encode($pause) : 'missing') . '.',
            $this->getSession()
        );
    }

    /**
     * Check actual allowed-seek evidence without crediting the deliberately skipped middle of the fixture.
     *
     * @Then /^the allowed forward seek for "(?P<username>[^"]+)" in "(?P<activityname>[^"]+)" preserves the unwatched gap$/
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     */
    public function allowed_forward_seek_preserves_unwatched_gap(string $username, string $activityname): void {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $activity = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('videotrack', (int)$activity->id, (int)$activity->course, false, MUST_EXIST);
        if (
            empty($activity->allowseekforward)
            || (int)$activity->durationseconds !== 60
            || (int)$activity->completionpercent !== 50
            || (int)$cm->completion !== COMPLETION_TRACKING_AUTOMATIC
        ) {
            throw new \coding_exception('Allowed-seek assertions require a 60-second fixture with a 50% viewing threshold.');
        }
        $conditions = ['videotrackid' => (int)$activity->id, 'userid' => (int)$user->id];
        $deadline = microtime(true) + 5.0;
        $state = false;
        $native = false;
        $segments = [];
        do {
            $state = $DB->get_record('videotrack_state', $conditions);
            $native = $DB->get_record('course_modules_completion', [
                'coursemoduleid' => (int)$cm->id,
                'userid' => (int)$user->id,
            ]);
            $segments = $DB->get_records('videotrack_seg', $conditions + ['servervalidated' => 1], 'id ASC');
            $ledgerintervals = [];
            $seek = false;
            $pause = false;
            foreach ($segments as $segment) {
                $start = (float)$segment->videotimestart;
                $end = (float)$segment->videotimeend;
                $ledgerintervals[] = [$start, $end];
                if ((string)$segment->endreason === 'seek' && $start < 3.0 && $end >= 3.0 && $end <= 12.0) {
                    $seek = $segment;
                }
                if ((string)$segment->endreason === 'pause' && $start >= 29.5 && $end >= 33.0) {
                    $pause = $segment;
                }
            }
            $intervals = $state ? json_decode((string)$state->intervaljson, true) : null;
            $safegap = $this->seek_intervals_exclude_unwatched_gap($ledgerintervals)
                && is_array($intervals) && $this->seek_intervals_exclude_unwatched_gap($intervals);
            $covered = 0.0;
            if ($safegap) {
                foreach ($intervals as $interval) {
                    $covered += (float)$interval[1] - (float)$interval[0];
                }
            }
            if (
                $state
                && $seek
                && $pause
                && $safegap
                && (int)$pause->id > (int)$seek->id
                && hash_equals((string)$seek->sessionid, (string)$pause->sessionid)
                && $covered > 0.0 && $covered < 30.0
                && abs((float)$state->uniquecoveredseconds - $covered) <= 0.01
                && abs((float)$state->completionpercent - $covered * 100 / 60) <= 0.1
                && (float)$state->lastposition >= 33.0
                && (int)$state->iscompleted === 0
                && (!$native || (int)$native->completionstate === COMPLETION_INCOMPLETE)
            ) {
                return;
            }
            usleep(100000);
        } while (microtime(true) < $deadline);

        throw new ExpectationException(
            'The allowed forward seek in "' . $activityname . '" did not retain separate played intervals '
                . 'before 12s and after 29.5s without crossing the 50% viewing threshold. '
                . 'state=' . ($state ? json_encode($state) : 'missing')
                . '; native=' . ($native ? json_encode($native) : 'missing')
                . '; segments=' . json_encode(array_values($segments)) . '.',
            $this->getSession()
        );
    }

    /**
     * Validate the two played regions around the deliberately unwatched 12-29.5 second fixture gap.
     *
     * @param array $intervals Persisted ledger or aggregate intervals.
     * @return bool Whether all intervals are positive, bounded and outside the skipped middle.
     */
    private function seek_intervals_exclude_unwatched_gap(array $intervals): bool {
        if (count($intervals) < 2) {
            return false;
        }
        foreach ($intervals as $interval) {
            if (!is_array($interval) || count($interval) !== 2 || !is_numeric($interval[0]) || !is_numeric($interval[1])) {
                return false;
            }
            $start = (float)$interval[0];
            $end = (float)$interval[1];
            if ($start < 0.0 || $end <= $start || $end > 60.0 || ($start < 29.5 && $end > 12.0)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Observe rollback recovery through native/SDK media APIs, then explicitly pause through the same surface.
     *
     * @When /^I pause the VideoTrack player after rollback recovery$/
     */
    public function i_pause_the_videotrack_player_after_rollback_recovery(): void {
        $deadline = microtime(true) + 15.0;
        $phasebegan = null;
        $phasetime = null;
        $lasttime = null;
        $waspaused = null;
        $observed = null;
        do {
            $raw = $this->getSession()->evaluateScript(
                "(function() {var media = document.querySelector('#mod-videotrack-player video');"
                    . "if (media) {return JSON.stringify({time: media.currentTime, paused: media.paused,"
                    . "playing: !media.paused && !media.ended});}"
                    . "var fixture = window.__videotrackBehatProvider;"
                    . "if (!fixture) {return null;}"
                    . "var state = fixture.getState();"
                    . "return JSON.stringify({time: fixture.getTime(), paused: state === 2, playing: state === 1});}())"
            );
            $observed = is_string($raw) ? json_decode($raw, true) : null;
            $time = is_array($observed) && is_numeric($observed['time'] ?? null) ? (float)$observed['time'] : null;
            $paused = !empty($observed['paused']);
            $playing = !empty($observed['playing']);
            if ($time !== null && $time >= 0.0 && $time < 25.0 && ($paused || $playing)) {
                if ($phasebegan === null || $waspaused !== $paused || ($lasttime !== null && $time < $lasttime - 0.25)) {
                    $phasebegan = microtime(true);
                    $phasetime = $time;
                }
                // Existing recovery guards last up to six seconds. Observe a stable media phase past that window.
                if (microtime(true) - $phasebegan >= 6.5 && ($paused || $time >= $phasetime + 6.0)) {
                    $this->getSession()->executeScript(
                        "(function() {var media = document.querySelector('#mod-videotrack-player video');"
                            . "if (media) {media.pause(); return;}"
                            . "var fixture = window.__videotrackBehatProvider;"
                            . "if (!fixture) {throw new Error('VideoTrack rollback player not found');}"
                            . "fixture.pause();}())"
                    );
                    return;
                }
                $lasttime = $time;
                $waspaused = $paused;
            } else {
                $phasebegan = null;
                $phasetime = null;
                $lasttime = null;
                $waspaused = null;
            }
            usleep(100000);
        } while (microtime(true) < $deadline);

        throw new ExpectationException(
            'VideoTrack rollback recovery did not reach a stable paused or advancing playback phase before '
                . 'the refused 30-second target. media=' . json_encode($observed) . '.',
            $this->getSession()
        );
    }

    /**
     * Require a paused native media or SDK-double player to settle at the actual validated frontier.
     *
     * @Then /^the paused VideoTrack player matches "(?P<username>[^"]+)" frontier in "(?P<activityname>[^"]+)"$/
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     */
    public function paused_player_matches_validated_frontier(string $username, string $activityname): void {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $activity = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $conditions = ['videotrackid' => (int)$activity->id, 'userid' => (int)$user->id];
        $deadline = microtime(true) + 7.0;
        $stablefrontier = null;
        $stablesince = null;
        $time = null;
        $frontier = null;
        do {
            $frontier = (float)$DB->get_field('videotrack_state', 'lastposition', $conditions, MUST_EXIST);
            $time = $this->getSession()->evaluateScript(
                "(function() {var media = document.querySelector('#mod-videotrack-player video');"
                    . "if (media) {return media.paused ? media.currentTime : null;}"
                    . "var fixture = window.__videotrackBehatProvider;"
                    . "return fixture && fixture.getState() === 2 ? fixture.getTime() : null;}())"
            );
            if (is_numeric($time) && abs((float)$time - $frontier) <= 1.25) {
                if ($stablefrontier === null || abs($stablefrontier - $frontier) > 0.001) {
                    $stablefrontier = $frontier;
                    $stablesince = microtime(true);
                } else if ($stablesince !== null && microtime(true) - $stablesince >= 0.75) {
                    return;
                }
            } else {
                $stablefrontier = null;
                $stablesince = null;
            }
            usleep(100000);
        } while (microtime(true) < $deadline);

        throw new ExpectationException(
            'The paused VideoTrack player in "' . $activityname . '" did not settle at validated frontier '
                . var_export($frontier, true) . '; paused media time=' . var_export($time, true) . '.',
            $this->getSession()
        );
    }

    /**
     * Read persisted interactions and the actual Forum composer URL after rollback without changing completion.
     *
     * @Then /^post-rollback interactions for "(?P<username>[^"]+)" in "(?P<activityname>[^"]+)" retain safe timestamps$/
     * @param string $username Moodle username.
     * @param string $activityname VideoTrack activity name.
     */
    public function post_rollback_interactions_retain_safe_timestamps(string $username, string $activityname): void {
        global $DB;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $activity = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('videotrack', (int)$activity->id, (int)$activity->course, false, MUST_EXIST);
        if (
            !empty($activity->allowseekforward)
            || (int)$activity->durationseconds !== 60
            || (int)$activity->completionpercent !== 5
            || (int)$cm->completion !== COMPLETION_TRACKING_AUTOMATIC
        ) {
            throw new \coding_exception('Rollback assertions require a blocked-seek 60-second fixture with 5% completion.');
        }
        $url = $this->getSession()->getCurrentUrl();
        $forumparams = [];
        $query = parse_url($url, PHP_URL_QUERY);
        if (is_string($query)) {
            parse_str($query, $forumparams);
        }
        $forumtime = isset($forumparams['time']) && is_numeric($forumparams['time']) ? (float)$forumparams['time'] : null;
        $forumvalid = basename((string)parse_url($url, PHP_URL_PATH)) === 'forum_post.php'
            && (int)($forumparams['id'] ?? 0) === (int)$cm->id && $forumtime !== null;
        $expected = ['behat_test_reaction' => '', 'note' => 'Post-rollback note', 'bookmark' => 'Post-rollback bookmark'];
        $conditions = ['videotrackid' => (int)$activity->id, 'userid' => (int)$user->id];
        $deadline = microtime(true) + 5.0;
        $state = false;
        $native = false;
        $events = [];
        do {
            $state = $DB->get_record('videotrack_state', $conditions);
            $native = $DB->get_record('course_modules_completion', [
                'coursemoduleid' => (int)$cm->id,
                'userid' => (int)$user->id,
            ]);
            $events = $DB->get_records('videotrack_reactev', $conditions + ['isdeleted' => 0], 'id ASC');
            $pauses = $DB->get_records(
                'videotrack_seg',
                $conditions + ['servervalidated' => 1, 'endreason' => 'pause'],
                'id DESC',
                '*',
                0,
                1
            );
            $pause = $pauses ? reset($pauses) : false;
            $safe = $state && $pause && count($events) === 3 && $forumvalid
                && abs($forumtime - (float)$state->lastposition) <= 1.25
                && hash_equals((string)$pause->sessionid, (string)($forumparams['sessionid'] ?? ''));
            $found = [];
            foreach ($events as $event) {
                $key = (string)$event->reactionkey;
                $found[$key] = true;
                $safe = $safe && array_key_exists($key, $expected)
                    && (int)$event->cmid === (int)$cm->id
                    && (float)$event->videotime >= 0.0
                    && abs((float)$event->videotime - (float)$state->lastposition) <= 1.25
                    && (float)$event->videotime < 22.0
                    && hash_equals((string)$pause->sessionid, (string)$event->sessionid);
                if ($key === 'behat_test_reaction') {
                    $safe = $safe && (int)$event->reactionid > 0 && (string)$event->reactionlabel === 'Test reaction';
                } else {
                    $safe = $safe && (string)$event->notetext === ($expected[$key] ?? null)
                        && (string)$event->notetype === $key;
                }
            }
            if (
                $safe && count($found) === 3 && $native
                && (float)$state->lastposition >= 3.0 && (float)$state->lastposition < 22.0
                && (float)$state->uniquecoveredseconds >= 3.0 && (float)$state->uniquecoveredseconds < 22.0
                && (float)$state->completionpercent >= 5.0 && (int)$state->iscompleted === 1
                && (int)$native->completionstate === COMPLETION_COMPLETE
            ) {
                return;
            }
            usleep(100000);
        } while (microtime(true) < $deadline);

        throw new ExpectationException(
            'Post-rollback interactions in "' . $activityname . '" did not retain three same-session safe timestamps '
                . 'and completed viewing state. state=' . ($state ? json_encode($state) : 'missing')
                . '; native=' . ($native ? json_encode($native) : 'missing')
                . '; events=' . json_encode(array_values($events)) . '; forum=' . json_encode($forumparams) . '.',
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

    /**
     * Assert that Moodle's grouped completion items use separate visual rows.
     *
     * @Then /^the VideoTrack completion requirements are stacked vertically$/
     */
    public function the_videotrack_completion_requirements_are_stacked_vertically(): void {
        $result = $this->getSession()->evaluateScript(<<<'JS'
(function() {
    const selector = '[data-videotrack-completion-grouped="1"]';
    const list = document.querySelector(selector);
    if (!list) {
        return JSON.stringify({found: false});
    }
    const completionRegion = list.closest('[data-region="completionrequirements"]');
    const items = Array.from(list.children).filter((item) =>
        item.matches('li, [role="listitem"]')
    );
    const style = window.getComputedStyle(list);
    const rectangles = items.map((item) => item.getBoundingClientRect());
    const stacked = rectangles.every((rectangle, index) =>
        index === 0 || rectangle.top >= rectangles[index - 1].bottom - 0.5
    );
    return JSON.stringify({
        found: true,
        incompletionregion: Boolean(completionRegion),
        itemcount: items.length,
        display: style.display,
        direction: style.flexDirection,
        stacked: stacked,
    });
}())
JS
        );
        $layout = json_decode((string)$result, true);
        if (
            !is_array($layout)
            || empty($layout['found'])
            || empty($layout['incompletionregion'])
            || ($layout['itemcount'] ?? 0) < 2
            || ($layout['display'] ?? '') !== 'flex'
            || ($layout['direction'] ?? '') !== 'column'
            || empty($layout['stacked'])
        ) {
            throw new ExpectationException(
                'VideoTrack completion requirements are not stacked vertically: ' . (string)$result,
                $this->getSession()
            );
        }
    }

    /**
     * Submit a real authenticated reset POST and verify its effects independently from the UI button.
     *
     * @When /^I submit a VideoTrack reset for "([^"]+)" in "([^"]+)" expecting "(denied|accepted)"$/
     * @param string $username Learner whose existing data is targeted.
     * @param string $activityname VideoTrack activity name.
     * @param string $outcome Expected permission decision.
     */
    public function i_submit_a_videotrack_reset_expecting(string $username, string $activityname, string $outcome): void {
        global $DB, $CFG;

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $activity = $DB->get_record('videotrack', ['name' => $activityname], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('videotrack', (int)$activity->id, (int)$activity->course, false, MUST_EXIST);
        if ((int)$activity->grade !== 100) {
            throw new ExpectationException('The reset scenario requires its explicit points-grade fixture.', $this->getSession());
        }

        // Supply an ordinary Moodle grade fixture as well as the browser-earned playback/completion evidence.
        require_once($CFG->libdir . '/gradelib.php');
        $gradestatus = grade_update(
            'mod/videotrack',
            (int)$activity->course,
            'mod',
            'videotrack',
            (int)$activity->id,
            0,
            (object)['userid' => (int)$user->id, 'rawgrade' => 50]
        );
        if ($gradestatus !== GRADE_UPDATE_OK) {
            throw new ExpectationException('The native grade fixture could not be saved.', $this->getSession());
        }
        $before = $this->videotrack_reset_snapshot($activity, $user, (int)$cm->id);
        if (
            empty($before['target']['tables']['videotrack_seg'])
            || empty($before['target']['tables']['videotrack_state'])
            || empty($before['target']['completion']->completionstate)
            || empty($before['target']['grades'])
        ) {
            throw new ExpectationException('The reset scenario lacks persisted learner evidence.', $this->getSession());
        }
        $url = new moodle_url('/mod/videotrack/report.php', ['id' => (int)$cm->id]);
        $params = json_encode([
            'action' => $this->locate_path($url->out_as_local_url(false)),
            'id' => (int)$cm->id,
            'userid' => (int)$user->id,
        ], JSON_THROW_ON_ERROR);
        $script = <<<'JS'
(function(parameters) {
    if (!window.M || !M.cfg || !M.cfg.sesskey) {
        throw new Error('The real report page has no session key');
    }
    var form = document.createElement('form');
    form.method = 'post';
    form.action = parameters.action;
    var fields = {
        id: parameters.id,
        mode: 'student',
        resetaction: 'resetstudent',
        resetuserid: parameters.userid,
        sesskey: M.cfg.sesskey
    };
    Object.keys(fields).forEach(function(name) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = fields[name];
        form.appendChild(input);
    });
    document.body.appendChild(form);
    form.submit();
})(PARAMETERS);
JS;
        $this->getSession()->executeScript(str_replace('PARAMETERS', $params, $script));
        $success = get_string('report:studentreset', 'mod_videotrack');
        $ready = $this->getSession()->wait(
            15000,
            'document.readyState === "complete" && ('
                . 'document.querySelector("[data-rel=\"fatalerror\"]") !== null || '
                . '(document.body && document.body.textContent.indexOf(' . json_encode($success) . ') !== -1))'
        );
        if (!$ready) {
            throw new ExpectationException('The reset POST did not reach a decision page.', $this->getSession());
        }
        $page = $this->getSession()->getPage();
        $fatal = $page->findAll('css', '[data-rel="fatalerror"]');
        if ($outcome === 'denied') {
            $link = $page->find('css', '.errorcode a[href]');
            $path = $link ? parse_url($link->getAttribute('href'), PHP_URL_PATH) : '';
            $message = get_string('nopermissions', 'error', get_string('videotrack:managereactions', 'mod_videotrack'));
            if (
                count($fatal) !== 1 || strpos($fatal[0]->getText(), $message) === false
                || !is_string($path) || preg_match('~/nopermissions/?$~', $path) !== 1
                || $page->find('css', '[data-rel="debugging"], [data-rel="phpdebugmessage"]') !== null
            ) {
                throw new ExpectationException('Expected only the management-permission denial.', $this->getSession());
            }
        } else if (count($fatal) !== 0 || strpos($page->getText(), $success) === false) {
            throw new ExpectationException('The authorised reset did not report success.', $this->getSession());
        }
        $after = $this->videotrack_reset_snapshot($activity, $user, (int)$cm->id);
        if ($outcome === 'denied' && json_encode($before) !== json_encode($after)) {
            throw new ExpectationException('The denied reset changed learner data or native grade/completion.', $this->getSession());
        }
        if ($outcome === 'accepted') {
            foreach ($after['target']['tables'] as $records) {
                if (!empty($records)) {
                    throw new ExpectationException(
                        'The authorised reset retained plugin-owned learner data.',
                        $this->getSession()
                    );
                }
            }
            foreach ($after['target']['grades'] as $grade) {
                if ($grade->rawgrade !== null || $grade->finalgrade !== null) {
                    throw new ExpectationException('The authorised reset retained the native grade.', $this->getSession());
                }
            }
            if (
                !empty($after['target']['completion']->completionstate)
                || json_encode($before['others']) !== json_encode($after['others'])
            ) {
                throw new ExpectationException('The reset retained completion or changed another learner.', $this->getSession());
            }
        }
        // Restore a normal report before Moodle's automatic exception hook runs; no error hook is disabled.
        $this->i_open_the_videotrack_report_for_learner($activityname, $username);
    }

    /**
     * Read target and other-learner snapshots across plugin data, native grades and native completion.
     *
     * @param stdClass $activity Existing VideoTrack activity.
     * @param stdClass $user Target learner.
     * @param int $cmid Activity course-module id.
     * @return array Persisted records, without normalising away any mutation.
     */
    private function videotrack_reset_snapshot(stdClass $activity, stdClass $user, int $cmid): array {
        global $DB;

        $snapshot = ['target' => ['tables' => []], 'others' => ['tables' => []]];
        $tables = ['videotrack_seg', 'videotrack_state', 'videotrack_reactev', 'videotrack_integrity', 'videotrack_acknowledge'];
        foreach ($tables as $table) {
            $snapshot['target']['tables'][$table] = $DB->get_records($table, [
                'videotrackid' => (int)$activity->id, 'userid' => (int)$user->id,
            ], 'id ASC');
            $snapshot['others']['tables'][$table] = $DB->get_records_select(
                $table,
                'videotrackid = :activityid AND userid <> :userid',
                ['activityid' => (int)$activity->id, 'userid' => (int)$user->id],
                'id ASC'
            );
        }
        $snapshot['target']['completion'] = $DB->get_record('course_modules_completion', [
            'coursemoduleid' => $cmid, 'userid' => (int)$user->id,
        ]);
        $snapshot['others']['completion'] = $DB->get_records_select(
            'course_modules_completion',
            'coursemoduleid = :cmid AND userid <> :userid',
            ['cmid' => $cmid, 'userid' => (int)$user->id],
            'id ASC'
        );
        $itemid = (int)$DB->get_field('grade_items', 'id', [
            'itemtype' => 'mod', 'itemmodule' => 'videotrack', 'iteminstance' => (int)$activity->id, 'itemnumber' => 0,
        ], MUST_EXIST);
        $snapshot['target']['grades'] = $DB->get_records('grade_grades', [
            'itemid' => $itemid, 'userid' => (int)$user->id,
        ], 'id ASC');
        $snapshot['others']['grades'] = $DB->get_records_select(
            'grade_grades',
            'itemid = :itemid AND userid <> :userid',
            ['itemid' => $itemid, 'userid' => (int)$user->id],
            'id ASC'
        );
        return $snapshot;
    }
}
