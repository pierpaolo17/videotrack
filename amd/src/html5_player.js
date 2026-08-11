/**
 * HTML5 player module for mod_videotrack.
 *
 * Handles tracking for locally uploaded video/audio files using the
 * native HTML5 <video> / <audio> element events. Mirrors the same
 * segment-tracking contract as the YouTube and Vimeo player modules.
 *
 * @module mod_videotrack/html5_player
 */

/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define([
    'core/log',
    'mod_videotrack/core/api',
    'mod_videotrack/core/adapter',
    'mod_videotrack/core/utils',
    'mod_videotrack/core/ui',
    'mod_videotrack/core/progress',
    'mod_videotrack/core/state',
    'mod_videotrack/core/reactions',
    'mod_videotrack/core/tracker',
    'mod_videotrack/core/player',
    'mod_videotrack/core/player/forum',
    'mod_videotrack/core/player/timed_text',
    'mod_videotrack/core/player/focus_guard',
    'mod_videotrack/core/debug'
], function(Log, Api, Adapter, Utils, Ui, Progress, State, Reactions, Tracker, PlayerCore, Forum, TimedText, FocusGuard, Debug) {
    'use strict';


    var media  = null; // The <video> or <audio> DOM element.
    var controlBar = null; // Current custom HTML5 control bar.
    var config = null;
    var reactionState = Reactions.createState();
    var focusGuard = null;
    var HEARTBEAT_INTERVAL = 30;

    var state = State.create({
        isSeeking: false
    });
    state.ajaxRequestScope = Api.createRequestScope();

    // ── Utilities ─────────────────────────────────────────────────────────


    /**
     * Resolve the full player configuration.
     *
     * Moodle debug warns when large payloads are passed directly through
     * js_call_amd(). The PHP view stores the full JSON in the page and passes
     * only the DOM id to the AMD init method.
     *
     * @param {Object} initConfig Minimal init configuration from PHP.
     * @return {Object} Full player configuration.
     */
    function resolveConfig(initConfig) {
        if (initConfig && initConfig.configid) {
            var node = document.getElementById(initConfig.configid);
            if (!node) {
                throw new Error('Videotrack player configuration not found.');
            }
            return JSON.parse(node.textContent || node.innerText || '{}');
        }
        return initConfig;
    }

    function uuid() {
        return PlayerCore.uuid();
    }

    function safeNumber(value, fallback) {
        var number = Number(value);
        return isFinite(number) ? number : fallback;
    }

    function getMaxWatchedFromIntervals(intervaljson) {
        var intervals;
        var max = 0;
        try {
            intervals = Array.isArray(intervaljson) ? intervaljson : JSON.parse(intervaljson || '[]');
            intervals.forEach(function(interval) {
                if (Array.isArray(interval) && interval.length > 1) {
                    max = Math.max(max, safeNumber(interval[1], 0));
                }
            });
        } catch (e) {
            Debug.log('invalidintervaljson', {message: e});
        }
        return Tracker.normaliseTime(max);
    }

    /**
     * Clamp automatic resume to progress the server has already validated when
     * forward seeking is disabled.
     *
     * @param {*} candidate Resume candidate in seconds.
     * @returns {number} Safe resume position.
     */
    function resolveResumePosition(candidate) {
        var position = Tracker.normaliseTime(candidate);
        var allowed;
        if (config && config.allowseekforward === false && (config.intervaljson || state.intervaljson)) {
            allowed = getMaxWatchedFromIntervals(config.intervaljson || state.intervaljson);
            if (position > allowed + 0.75) {
                return allowed;
            }
        }
        return position;
    }

    function markAllowedForwardTime(current) {
        state.maxallowedtime = Math.max(Number(state.maxallowedtime) || 0, Tracker.normaliseTime(current));
    }

    function getAllowedForwardLimit() {
        return Math.max(Number(state.maxallowedtime) || 0, getMaxWatchedFromIntervals(state.intervaljson));
    }


    function normaliseControls(controls) {
        var resolved = [];
        if (Array.isArray(controls)) {
            resolved = controls.slice();
        } else if (typeof controls === 'string') {
            resolved = controls.split(',').map(function(control) {
                return control.trim();
            }).filter(function(control) {
                return control.length > 0;
            });
        }
        if (config && config.preventpictureinpicture) {
            resolved = resolved.filter(function(control) {
                return control !== 'pip';
            });
        }
        return resolved;
    }

    function getConfiguredMaxPlaybackRate() {
        var configured = Number(config && config.maxplaybackrate ? config.maxplaybackrate : 0) / 100;
        if (configured > 0) {
            return configured;
        }
        if (config && config.allowplaybackratechange === false) {
            return 1;
        }
        return 0;
    }

    function getPlaybackRatePenalty() {
        var configured = Number(config && config.blockedseekplaybackrate ? config.blockedseekplaybackrate : 50) / 100;
        if ([0.5, 0.75, 1].indexOf(configured) >= 0) {
            return configured;
        }
        return 0.5;
    }

    function getNaturalPlaybackTolerance(rate) {
        var safeRate = safeNumber(rate, 1);
        if (safeRate <= 0) {
            safeRate = 1;
        }
        return Math.max(4, (safeRate * 2) + 1);
    }

    function setSpeedButtonState(rate) {
        var speedWrap = document.getElementById('videotrack-speed-controls');
        if (!speedWrap) {
            return;
        }
        speedWrap.querySelectorAll('.videotrack-speed-btn').forEach(function(button) {
            button.classList.toggle('active', parseFloat(button.dataset.speed) === rate);
        });
    }

    function writePlaybackRate(rate) {
        var safeRate = safeNumber(rate, getPlaybackRatePenalty());
        if (safeRate <= 0) {
            safeRate = getPlaybackRatePenalty();
        }
        media.playbackRate = safeRate;
        state.playbackrate = safeRate;
        setSpeedButtonState(safeRate);
        return safeRate;
    }

    function enforceMaxPlaybackRate() {
        var maxRate = getConfiguredMaxPlaybackRate();
        var currentRate;
        if (!media || maxRate <= 0) {
            return state.playbackrate || 1;
        }
        currentRate = safeNumber(media.playbackRate, state.playbackrate || 1);
        if (currentRate > maxRate) {
            if (focusGuard) {
                focusGuard.record('ratechange');
            }
            return writePlaybackRate(getPlaybackRatePenalty());
        }
        state.playbackrate = currentRate;
        setSpeedButtonState(currentRate);
        return currentRate;
    }

    function getBlockedSeekPlaybackRate(fallback) {
        var previous = Tracker.normaliseTime(state.lasttime);
        var limit = Tracker.normaliseTime(fallback);
        if (previous < limit - 0.75) {
            return 1;
        }
        return getPlaybackRatePenalty();
    }

    function applyBlockedSeekPenalty(rate) {
        return writePlaybackRate(rate || getPlaybackRatePenalty());
    }

    function retryBlockedSeekPenalty(rate) {
        [0, 150, 400, 900, 1600, 3000].forEach(function(delay) {
            window.setTimeout(function() {
                if (!media) {
                    return;
                }
                if (Math.abs(safeNumber(media.playbackRate, 1) - (rate || getPlaybackRatePenalty())) > 0.01) {
                    applyBlockedSeekPenalty(rate);
                }
            }, delay);
        });
    }

    function markHTML5PlaybackObserved() {
        state._html5RecentPlayingAt = Date.now();
    }

    function resolveHTML5SeekWasPlaying() {
        var recentPlayback = state._html5RecentPlayingAt && Date.now() - state._html5RecentPlayingAt <= 5000;
        return !!state.playing || !!(media && media.paused === false) || !!recentPlayback || !!state.wasPlayingBeforeSeekBlock;
    }

    function playHTML5AfterSeek(label, delays) {
        var attempts = Array.isArray(delays) && delays.length ? delays : [0, 250, 700, 1500, 3000];
        var token = Date.now() + Math.random();
        state._html5PlayAfterSeekToken = token;

        function attempt(index) {
            if (!media || state._html5PlayAfterSeekToken !== token) {
                return;
            }
            window.setTimeout(function() {
                var playPromise;
                if (!media || state._html5PlayAfterSeekToken !== token) {
                    return;
                }
                if (!media.paused) {
                    markHTML5PlaybackObserved();
                    if (index + 1 < attempts.length) {
                        attempt(index + 1);
                    } else if (state._html5PlayAfterSeekToken === token) {
                        state._html5PlayAfterSeekToken = null;
                        state._html5BlockedSeekResume = false;
                    }
                    return;
                }
                playPromise = media.play();
                if (playPromise && typeof playPromise.then === 'function') {
                    playPromise.then(function() {
                        if (state._html5PlayAfterSeekToken === token) {
                            state._html5PlayAfterSeekToken = null;
                            state._html5BlockedSeekResume = false;
                        }
                    }).catch(function(err) {
                        Debug.log(label || 'html5blockedseekresume', {message: err});
                        if (index + 1 < attempts.length) {
                            attempt(index + 1);
                        } else {
                            state._html5PlayAfterSeekToken = null;
                        }
                    });
                } else {
                    state._html5PlayAfterSeekToken = null;
                    state._html5BlockedSeekResume = false;
                }
            }, attempts[index]);
        }

        attempt(0);
    }

    function scheduleBlockedSeekResume(wasPlaying) {
        if (!wasPlaying) {
            return;
        }
        state._html5BlockedSeekResume = true;
        playHTML5AfterSeek('html5blockedseekresume', [0, 250, 700, 1500, 3000]);
        window.setTimeout(function() {
            state._html5BlockedSeekResume = false;
        }, 6000);
    }


    function finishProgrammaticSeek(current) {
        var time = safeNumber(current, media ? media.currentTime : state.lasttime || 0);
        if (state._programmaticSeekTimer) {
            window.clearTimeout(state._programmaticSeekTimer);
            state._programmaticSeekTimer = null;
        }
        if (Tracker.consumeProgrammaticSeek(state, time)) {
            markAllowedForwardTime(time);
        }
        state.isSeeking = false;
        Tracker.clearSeekBlock(state);
    }

    function scheduleProgrammaticSeekFallback(target) {
        if (state._programmaticSeekTimer) {
            window.clearTimeout(state._programmaticSeekTimer);
        }
        state._programmaticSeekTimer = window.setTimeout(function() {
            if (state.isProgrammaticSeek) {
                finishProgrammaticSeek(target);
            }
        }, 600);
    }

    function rotatePlayingSegmentForSeek(oldTime, newTime) {
        if (!state.playing) {
            return;
        }
        Tracker.saveOpenSegmentSnapshot(state, oldTime, saveSegment, 'seek').catch(Log.debug);
        Tracker.openSegment(
            state,
            newTime,
            Math.floor(Date.now() / 1000),
            safeNumber(media ? media.playbackRate : state.playbackrate, state.playbackrate || 1)
        );
    }

    function startProgrammaticSeek(target) {
        var previous = safeNumber(media ? media.currentTime : state.lasttime, state.lasttime || 0);
        rotatePlayingSegmentForSeek(previous, target);
        Tracker.markProgrammaticSeek(state);
        media.currentTime = target;
        scheduleProgrammaticSeekFallback(target);
    }

    function blockForwardSeek(target, fallbackTime) {
        var previous = Tracker.normaliseTime(state.lasttime);
        var fallback = typeof fallbackTime === 'number' ? fallbackTime : getAllowedForwardLimit();
        var wasPlaying = resolveHTML5SeekWasPlaying();
        var penaltyRate;
        fallback = Math.max(0, Tracker.normaliseTime(fallback));
        if (Tracker.normaliseTime(target) <= fallback + 0.75) {
            return false;
        }
        if (focusGuard) {
            focusGuard.noteAction('forwardseek');
            focusGuard.record('forwardseek');
        }
        if (wasPlaying && state.segmentstart !== null) {
            // Persist only the naturally played portion before the forbidden
            // jump. A farther rollback frontier may come from older watched
            // intervals and must not be credited as part of this segment.
            Tracker.saveOpenSegmentSnapshot(state, previous, saveSegment, 'seek').catch(function(error) {
                Debug.log('html5blockedseekfrontiersavefailed', {message: error && error.message});
            });
        }
        penaltyRate = applyBlockedSeekPenalty(getBlockedSeekPlaybackRate(fallback));
        retryBlockedSeekPenalty(penaltyRate);
        PlayerCore.showBlockedForwardSeekNotice(config, penaltyRate);
        state.isSeeking = true;
        Tracker.blockSeek(state, 900);
        media.currentTime = fallback;
        Tracker.syncTime(state, fallback, penaltyRate);
        if (wasPlaying) {
            Tracker.openSegment(state, fallback, Math.floor(Date.now() / 1000), penaltyRate);
        }
        markAllowedForwardTime(fallback);
        window.setTimeout(function() {
            state.isSeeking = false;
            Tracker.clearSeekBlock(state);
            scheduleBlockedSeekResume(wasPlaying);
        }, 0);
        Debug.log('html5blockedforwardseek', {target: target, fallback: fallback});
        return true;
    }


    function saveSegment(start, end, reason, wallclockstart) {
        var interactionSave = ['reaction', 'note', 'bookmark', 'interaction'].indexOf(reason) !== -1;
        return Api.saveSegment(config, state, start, end, reason, {
            swallowFailures: !interactionSave,
            errorMessage: 'html5-player-event',
            requestScope: state.ajaxRequestScope,
            wallclockstart: wallclockstart
        }).then(updateProgress);
    }

    function hasMedia(capability) {
        if (capability) {
            return Adapter.hasCapability(media, 'html5', capability);
        }
        return Adapter.canPlay(media, 'html5') && Adapter.canPause(media, 'html5');
    }

    function saveCurrentProgress(reason) {
        return PlayerCore.saveCurrentProgress(
            state,
            getInteractionVideoTime,
            saveSegment,
            reason,
            hasMedia('currentTime')
        );
    }

    function updateProgress(response) {
        return Progress.updateProgress(response, state, Utils, PlayerCore, Log);
    }

    /**
     * Resolve a reaction timestamp from the progress save response.
     *
     * @param {Object|null} progressResponse Progress save response.
     * @param {number} fallbackTime Current player time fallback.
     * @returns {number} Timestamp known to be inside the just-saved segment when available.
     */
    function resolveReactionTime(progressResponse, fallbackTime) {
        var savedEnd = progressResponse && Number(progressResponse.savedvideotimeend);
        var time = Number(fallbackTime);
        if (Number.isFinite(savedEnd) && savedEnd > 0) {
            return Math.max(0, savedEnd);
        }
        return Number.isFinite(time) ? Math.max(0, time) : 0;
    }

    function isDefinitiveReactionFailure(error) {
        var code = error && (error.errorcode || (error.originalError && error.originalError.errorcode));
        var message = error && (error.message || String(error));
        var text = String(code || message || '').toLowerCase();
        return text.indexOf('playbackrequired') !== -1 ||
            text.indexOf('playbackpositionnotwatched') !== -1 ||
            text.indexOf('reactionratelimit') !== -1 ||
            text.indexOf('reactionsdisabled') !== -1 ||
            text.indexOf('invalidsesskey') !== -1 ||
            text.indexOf('requirelogin') !== -1 ||
            text.indexOf('nopermissions') !== -1 ||
            text.indexOf('accessdenied') !== -1;
    }

    function removeReactionRow(row) {
        if (row && row.parentNode) {
            row.parentNode.removeChild(row);
        }
    }

    // Segment lifecycle.

    function startSegment() {
        var current = safeNumber(media.currentTime, 0);
        state.intervaljson = config.intervaljson || state.intervaljson || '[]';
        Tracker.openSegment(
            state,
            current,
            Math.floor(Date.now() / 1000),
            enforceMaxPlaybackRate()
        );
        markAllowedForwardTime(Math.max(current, getMaxWatchedFromIntervals(state.intervaljson)));
    }

    /**
     * Close the current segment and send it to the server.
     * Capture start and end before resetting state, so saveSegment() never
     * receives a null start after state.segmentstart has been cleared.
     *
     * @param {string} reason Close reason (pause, seek, tab, heartbeat...).
     */
    function closeSegment(reason) {
        return Tracker.closeAndSaveSegment(state, function() {
            return media ? safeNumber(media.currentTime, state.lasttime || 0) : state.lasttime;
        }, saveSegment, reason, hasMedia('currentTime')).catch(Log.debug);
    }

    // ── Heartbeat ─────────────────────────────────────────────────────────

    function startHeartbeat() {
        Tracker.startPolling(state, function() {
            Tracker.runHeartbeat({
                state: state,
                heartbeatInterval: HEARTBEAT_INTERVAL,
                getCurrentTime: function() {
                    return safeNumber(media.currentTime, state.lasttime || 0);
                },
                saveSegment: saveSegment,
                hasPlayer: function() {
                    return hasMedia();
                },
                shouldSkip: function() {
                    return state.isSeeking;
                },
                log: Log
            });
        }, HEARTBEAT_INTERVAL);
    }

    function stopHeartbeat() {
        Tracker.stopPolling(state);
    }

    function showResumeNotice(seconds) {
        PlayerCore.showResumeNotice(seconds, config, Utils);
    }

    /**
     * Seek to a replay fragment and request playback.
     *
     * Direct report links use the same path as in-page replay buttons so the
     * explicit replay target always takes precedence over the saved resume point.
     *
     * @param {number} start Replay start in seconds.
     * @param {number|null} end Optional replay end in seconds.
     */
    function replayHTML5Fragment(start, end) {
        var target = Math.max(0, Tracker.normaliseTime(start));
        var replayPlay;
        state.currentReplayEnd = typeof end === 'number' && isFinite(end) && end > 0 ? end : null;
        startProgrammaticSeek(target);
        replayPlay = Adapter.play(function() {
            return media.play();
        }, Log, 'HTML5 replay play');
        if (replayPlay && typeof replayPlay.catch === 'function') {
            replayPlay.catch(function(err) {
                Debug.log('playrequestfailed', {message: err});
            });
        }
    }

    function installGlobalListeners() {
        Tracker.installLifecycleHandlers({
            state: state,
            closeSegment: closeSegment,
            hasPlayer: function() { return hasMedia(); },
            sendBeacon: function() {
                return Tracker.sendUnloadBeacon({
                    state: state,
                    hasPlayer: function() { return hasMedia(); },
                    getCurrentTime: getCurrentVideoTime,
                    sendSegment: function(start, end) {
                        return PlayerCore.sendBeaconSegment(config, state, start, end, Utils, Log);
                    }
                });
            }
        });

        var root = PlayerCore.getPlayerShell(Log);
        if (!root) { return; }

        function handleReplayClick(e) {
            var btn = e.target.closest('.videotrack-replay');
            if (!btn || !media) { return false; }
            e.preventDefault();
            e.stopPropagation();
            var start = parseFloat(btn.dataset.time);
            if (!isFinite(start)) {
                start = parseFloat(btn.dataset.start) || 0;
            }
            var end   = parseFloat(btn.dataset.end)   || 0;
            replayHTML5Fragment(start, end);
            return true;
        }

        // Replay buttons can live outside the player shell in the reactions table.
        root.addEventListener('click', handleReplayClick);
        var reactionsTable = document.getElementById('videotrack-my-reactions');
        if (reactionsTable) {
            reactionsTable.addEventListener('click', handleReplayClick);
        }
    }

    // ── Build player ──────────────────────────────────────────────────────

    function buildPlayer() {
        var container = document.getElementById('mod-videotrack-player');
        if (!container) { return; }

        var isAudio = /\.(mp3|aac|m4a|ogg|wav)(\?|$)/i.test(config.videourl || '');
        var tag     = isAudio ? 'audio' : 'video';

        // If videourl is empty (file not uploaded by the teacher yet), show a notice
        // and do not create the media element; this avoids media.src='' browser errors.
        if (!config.videourl) {
            var nofileWrap = document.getElementById('mod-videotrack-player');
            if (nofileWrap) {
                var nofileMsg = document.createElement('div');
                nofileMsg.className = 'alert alert-warning mt-2';
                nofileMsg.setAttribute('role', 'alert');
                nofileMsg.textContent = config.nofilelabel;
                nofileWrap.parentNode.insertBefore(nofileMsg, nofileWrap.nextSibling);
            }
            return;
        }

        media = document.createElement(tag);
        media.src        = config.videourl;
        media.autoplay   = !!config.autoplay;
        media.loop       = !!config.loop;
        media.muted      = !!(config.autoplay || config.startmuted);
        media.playsinline = true;
        // Hide native controls: we replace them with a custom bar (or show them
        // if the teacher has enabled 'controls' and no custom bar is configured).
        media.controls   = false;
        media.style.width  = '100%';
        media.style.height = isAudio ? 'auto' : '100%';
        media.style.display = 'block';

        // Prevent download via right-click / context menu if not allowed.
        if (!config.allowdownload) {
            media.setAttribute('controlsList', 'nodownload');
            media.addEventListener('contextmenu', function(e) { e.preventDefault(); });
        }

        container.appendChild(media);
        if (focusGuard) {
            focusGuard.applyPictureInPicturePolicy(media);
        }

        // If autoplay is active, attempt playback only after the media element
        // has been configured and attached to the DOM so muted/playsinline are
        // applied before browser autoplay policy is evaluated.
        if (config.autoplay) {
            var autoplayPromise = media.play();
            if (autoplayPromise !== undefined) {
                autoplayPromise.catch(function() {
                    var wrap = media.parentElement;
                    if (wrap && !wrap.querySelector('.videotrack-autoplay-notice')) {
                        var notice = document.createElement('div');
                        notice.className = 'videotrack-autoplay-notice alert alert-info mt-1';
                        notice.setAttribute('role', 'status');
                        notice.setAttribute('aria-live', 'polite');
                        notice.textContent = config.autoblockedlabel;
                        wrap.appendChild(notice);
                    }
                });
            }
        }

        // Attach VTT subtitles if provided.
        if (config.captions && config.vtturl) {
            var track = document.createElement('track');
            track.kind    = 'subtitles';
            track.src     = config.vtturl;
            track.srclang = config.captionslang || 'und';
            track.label   = config.captionslang;
            track.default = true;
            media.appendChild(track);
        }

        // Build custom control bar.
        buildControlBar(isAudio);

        // Attach segment-tracking event listeners.
        attachTrackingEvents();
    }


    /**
     * Formats elapsed media time using MM:SS or HH:MM:SS for long media.
     *
     * @param {number} seconds Elapsed media time.
     * @return {string} Formatted elapsed time.
     */
    function formatElapsedTime(seconds) {
        var value = Math.max(0, Math.round(safeNumber(seconds, 0)));
        if (Math.max(value, safeNumber(state.duration, 0)) < 3600) {
            return Utils.formatSeconds(value);
        }
        var hours = Math.floor(value / 3600);
        var minutes = Math.floor((value % 3600) / 60);
        var secs = value % 60;
        return String(hours).padStart(2, '0') + ':'
            + String(minutes).padStart(2, '0') + ':'
            + String(secs).padStart(2, '0');
    }

    /**
     * Updates the elapsed-time value in the custom control bar.
     */
    function updateElapsedDisplays() {
        if (controlBar && controlBar._currentEl) {
            controlBar._currentEl.textContent = formatElapsedTime(media ? media.currentTime : 0);
        }
    }

    /**
     * Builds the custom HTML5 control bar.
     *
     * @param {boolean} isAudio Whether the source is audio-only.
     */
    function buildControlBar(isAudio) {
        var container = document.getElementById('mod-videotrack-player');
        if (!container) { return; }
        var controls = normaliseControls(config.html5controls || []);
        if (!controls.length) {
            controls = ['current', 'duration'];
        }

        // Always show elapsed time in the custom HTML5 bar. Some browser
        // native controls emphasise remaining time; VideoTrack's custom bar must
        // always expose the time elapsed from the beginning of the media.
        if (controls.indexOf('current') < 0) {
            controls.splice(Math.max(controls.indexOf('progress'), 0), 0, 'current');
        }

        var bar = document.createElement('div');
        controlBar = bar;
        bar.className = 'videotrack-html5-controls';
        bar.setAttribute('role', 'toolbar');
        bar.setAttribute('aria-label', config.html5controlslabel);
        bar.setAttribute('aria-orientation', 'horizontal');

        // ── Play / Pause ─────────────────────────────────────
        if (controls.indexOf('play') >= 0) {
            var playBtn = makeBtn('videotrack-ctrl-play', '▶', config.html5playlabel);
            playBtn.addEventListener('click', function() {
                if (Adapter.isPaused(state, function() { return media.paused; }, Log, 'HTML5')) {
                    Adapter.play(function() {
                        return media.play();
                    }, Log, 'HTML5 control play');
                } else {
                    Adapter.pause(function() {
                        return media.pause();
                    }, Log, 'HTML5 control pause');
                }
            });
            bar.appendChild(playBtn);
        }

        // ── Rewind ───────────────────────────────────────────
        // Shown only if: control is in the allowed list AND rewindstep > 0
        // AND allowseekbackward is not false.
        if (controls.indexOf('rewind') >= 0
                && config.rewindstep > 0
                && config.allowseekbackward !== false) {
            var rwBtn = makeBtn('videotrack-ctrl-rewind',
                '⏪ ' + config.rewindstep + 's',
                config.rewindlabel + ' ' + config.rewindstep + ' ' + config.secondslabel);
            rwBtn.addEventListener('click', function() {
                Adapter.seek(
                    Adapter.resolveSkipTarget(media.currentTime, -config.rewindstep, state.duration || media.duration),
                    function(target) {
                        startProgrammaticSeek(target);
                    },
                    Log,
                    'HTML5 rewind'
                );
            });
            bar.appendChild(rwBtn);
        }

        // ── Fast-forward ──────────────────────────────────────
        // Shown only if: control is in the allowed list AND fastforwardstep > 0
        // AND allowseekforward is not false.
        if (controls.indexOf('fastforward') >= 0
                && config.fastforwardstep > 0
                && config.allowseekforward !== false) {
            var ffBtn = makeBtn('videotrack-ctrl-ff',
                config.fastforwardstep + 's ⏩',
                config.fastforwardlabel + ' ' + config.fastforwardstep + ' ' + config.secondslabel);
            ffBtn.addEventListener('click', function() {
                Adapter.seek(
                    Adapter.resolveSkipTarget(media.currentTime, config.fastforwardstep, state.duration || media.duration),
                    function(target) {
                        startProgrammaticSeek(target);
                    },
                    Log,
                    'HTML5 fast-forward'
                );
            });
            bar.appendChild(ffBtn);
        }

        // ── Current time ─────────────────────────────────────
        if (controls.indexOf('current') >= 0) {
            var currentEl = document.createElement('span');
            currentEl.className = 'videotrack-ctrl-time';
            currentEl.textContent = formatElapsedTime(media.currentTime);
            currentEl.setAttribute('aria-live', 'off');
            bar.appendChild(currentEl);
            bar._currentEl = currentEl;
        }

        // ── Progress bar ─────────────────────────────────────
        if (controls.indexOf('progress') >= 0) {
            var progressWrap = document.createElement('div');
            progressWrap.className = 'videotrack-ctrl-progress-wrap';
            var progressBar = document.createElement('input');
            progressBar.type  = 'range';
            progressBar.className = 'videotrack-ctrl-progress';
            progressBar.min   = '0';
            progressBar.max   = '100';
            progressBar.value = '0';
            progressBar.setAttribute('aria-label',    config.html5seeklabel);
            progressBar.setAttribute('aria-valuemin', '0');
            progressBar.setAttribute('aria-valuemax', '100');
            progressBar.setAttribute('aria-valuenow', '0');
            progressBar.setAttribute('aria-valuetext','0:00');
            progressBar.addEventListener('input', function() {
                if (state.duration) {
                    var requested = (parseFloat(progressBar.value) / 100) * state.duration;
                    var current = safeNumber(media.currentTime, 0);
                    var allowed = requested;
                    var forwardLimit = getAllowedForwardLimit();
                    if (config.allowseekforward === false && requested > forwardLimit + 0.75) {
                        allowed = forwardLimit;
                        blockForwardSeek(requested, allowed);
                        progressBar.value = state.duration ? String((allowed / state.duration) * 100) : '0';
                        progressBar.setAttribute('aria-valuenow',  String(Math.round(progressBar.value)));
                        progressBar.setAttribute('aria-valuetext', formatElapsedTime(allowed));
                        return;
                    }
                    if (config.allowseekforward === false && requested > forwardLimit) {
                        allowed = forwardLimit;
                    }
                    if (config.allowseekbackward === false && requested < current) {
                        allowed = current;
                    }
                    startProgrammaticSeek(allowed);
                    progressBar.value = state.duration ? String((allowed / state.duration) * 100) : '0';
                    progressBar.setAttribute('aria-valuenow',  String(Math.round(progressBar.value)));
                    progressBar.setAttribute('aria-valuetext', formatElapsedTime(allowed));
                }
            });
            progressWrap.appendChild(progressBar);
            bar.appendChild(progressWrap);
            // Store reference for timeupdate.
            bar._progressBar = progressBar;
        }

        // ── Duration ─────────────────────────────────────────
        if (controls.indexOf('duration') >= 0) {
            var durationEl = document.createElement('span');
            durationEl.className = 'videotrack-ctrl-duration text-muted';
            durationEl.textContent = ' / 0:00';
            bar.appendChild(durationEl);
            bar._durationEl = durationEl;
        }

        // ── Mute ─────────────────────────────────────────────
        if (controls.indexOf('mute') >= 0) {
            var muteBtn = makeBtn('videotrack-ctrl-mute', '', config.html5mutelabel);
            var muteIcon = document.createElement('span');
            muteIcon.setAttribute('aria-hidden', 'true');
            muteIcon.textContent = '🔊';
            muteBtn.appendChild(muteIcon);
            muteBtn.addEventListener('click', function() {
                var muted = !Adapter.isMuted(state, function() { return media.muted; }, Log, 'HTML5 mute');
                Adapter.setMuted(muted, function(value) {
                    media.muted = value;
                }, state, Log, 'HTML5 mute');
                var muteLabel = muted ? (config.html5unmutelabel) : (config.html5mutelabel);
                muteIcon.textContent = muted ? '🔇' : '🔊';
                muteBtn.setAttribute('aria-label', muteLabel);
                muteBtn.setAttribute('title', muteLabel);
            });
            bar.appendChild(muteBtn);
        }

        // ── Volume ───────────────────────────────────────────
        if (controls.indexOf('volume') >= 0) {
            var volSlider = document.createElement('input');
            volSlider.type  = 'range';
            volSlider.className = 'videotrack-ctrl-volume';
            volSlider.min   = '0';
            volSlider.max   = '100';
            volSlider.step  = '5';
            var initialVolume = Adapter.isMuted(state, function() { return media.muted; }, Log, 'HTML5 volume') ?
                0 : Adapter.getVolume(state, function() { return media.volume; }, Log, 'HTML5 volume');
            var initialVolumePercent = Math.round(initialVolume * 100 / 5) * 5;
            initialVolumePercent = Math.max(0, Math.min(100, initialVolumePercent));
            volSlider.value = String(initialVolumePercent);
            volSlider.setAttribute('aria-label', config.html5volumelabel);
            volSlider.setAttribute('aria-valuemin', '0');
            volSlider.setAttribute('aria-valuemax', '100');
            volSlider.setAttribute('aria-valuenow', String(initialVolumePercent));
            volSlider.setAttribute('aria-valuetext', initialVolumePercent + '%');
            volSlider.addEventListener('input', function() {
                var volumePercent = Math.max(0, Math.min(100, parseFloat(volSlider.value) || 0));
                Adapter.setVolume(volumePercent / 100, function(volume) {
                    media.volume = volume;
                    media.muted = (volume === 0);
                }, state, Log, 'HTML5 volume');
                volSlider.value = String(volumePercent);
                volSlider.setAttribute('aria-valuenow', String(volumePercent));
                volSlider.setAttribute('aria-valuetext', volumePercent + '%');
            });
            bar.appendChild(volSlider);
        }

        // ── Speed ─────────────────────────────────────────────
        if (controls.indexOf('speed') >= 0 && config.playbackspeeds && config.playbackspeeds.length) {
            var speedWrap = document.createElement('div');
            speedWrap.className = 'videotrack-speed-controls';
            speedWrap.id = 'videotrack-speed-controls';
            config.playbackspeeds.forEach(function(speed) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-outline-secondary videotrack-speed-btn' +
                    (speed === 1 ? ' active' : '');
                btn.textContent = speed + '×';
                btn.dataset.speed = speed;
                btn.setAttribute('aria-label', (config.html5speedlabel) + ' ' + speed + 'x');
                btn.addEventListener('click', function() {
                    writePlaybackRate(speed);
                    enforceMaxPlaybackRate();
                });
                speedWrap.appendChild(btn);
            });
            bar.appendChild(speedWrap);
        }

        // ── PiP ──────────────────────────────────────────────
        if (!isAudio && controls.indexOf('pip') >= 0 && document.pictureInPictureEnabled) {
            var pipBtn = makeBtn('videotrack-ctrl-pip', '⧉', config.html5piplabel);
            pipBtn.setAttribute('aria-pressed', 'false');
            var updatePipPressed = function() {
                pipBtn.setAttribute('aria-pressed', document.pictureInPictureElement === media ? 'true' : 'false');
            };
            pipBtn.addEventListener('click', function() {
                if (document.pictureInPictureElement) {
                    document.exitPictureInPicture().then(updatePipPressed).catch(function() { updatePipPressed(); });
                } else {
                    media.requestPictureInPicture().then(updatePipPressed).catch(function() { updatePipPressed(); });
                }
            });
            var cleanupPipHandler = function() {
                media.removeEventListener('enterpictureinpicture', updatePipPressed);
                media.removeEventListener('leavepictureinpicture', updatePipPressed);
                window.removeEventListener('pagehide', cleanupPipHandler);
                window.removeEventListener('beforeunload', cleanupPipHandler);
            };
            media.addEventListener('enterpictureinpicture', updatePipPressed);
            media.addEventListener('leavepictureinpicture', updatePipPressed);
            window.addEventListener('pagehide', cleanupPipHandler, {once: true});
            window.addEventListener('beforeunload', cleanupPipHandler, {once: true});
            bar.appendChild(pipBtn);
        }

        // ── Fullscreen ────────────────────────────────────────
        if (!isAudio && controls.indexOf('fullscreen') >= 0) {
            var fsWrapper = container.closest('.videotrack-player-wrap') || container;
            var fsBtn = makeBtn('videotrack-ctrl-fs', '⛶', config.html5fullscreenlabel);
            fsBtn.setAttribute('aria-pressed', 'false');
            var updateFullscreenPressed = function() {
                fsBtn.setAttribute('aria-pressed', document.fullscreenElement === fsWrapper ? 'true' : 'false');
            };
            var cleanupFullscreenHandler = function() {
                document.removeEventListener('fullscreenchange', updateFullscreenPressed);
                window.removeEventListener('pagehide', cleanupFullscreenHandler);
                window.removeEventListener('beforeunload', cleanupFullscreenHandler);
            };
            document.addEventListener('fullscreenchange', updateFullscreenPressed);
            window.addEventListener('pagehide', cleanupFullscreenHandler, {once: true});
            window.addEventListener('beforeunload', cleanupFullscreenHandler, {once: true});
            fsBtn.addEventListener('click', function() {
                if (!document.fullscreenElement) {
                    if (fsWrapper.requestFullscreen) {
                        fsWrapper.requestFullscreen().then(updateFullscreenPressed).catch(function() {
                            updateFullscreenPressed();
                        });
                    }
                } else if (document.fullscreenElement === fsWrapper && document.exitFullscreen) {
                    document.exitFullscreen().then(updateFullscreenPressed).catch(function() { updateFullscreenPressed(); });
                }
            });
            bar.appendChild(fsBtn);
        }

        // ── Download ──────────────────────────────────────────
        if (config.allowdownload && controls.indexOf('download') >= 0) {
            var dlBtn = document.createElement('a');
            dlBtn.href     = media.src;
            dlBtn.download = '';
            dlBtn.className = 'btn btn-sm btn-outline-secondary videotrack-ctrl-download';
            dlBtn.textContent = '⬇';
            dlBtn.setAttribute('aria-label', config.html5downloadlabel);
            bar.appendChild(dlBtn);
        }

        // Keep the elapsed time immediately after the command controls and
        // immediately before the progress bar. appendChild() moves existing nodes.
        if (bar._currentEl) {
            bar.appendChild(bar._currentEl);
        }
        if (bar._progressBar && bar._progressBar.parentNode) {
            bar.appendChild(bar._progressBar.parentNode);
        }
        if (bar._durationEl) {
            bar.appendChild(bar._durationEl);
        }

        // ── Wire up timeupdate to control bar ─────────────────
        media.addEventListener('timeupdate', function() {
            if (bar._progressBar && state.duration) {
                var pct = (media.currentTime / state.duration) * 100;
                bar._progressBar.value = pct;
                bar._progressBar.setAttribute('aria-valuenow',  String(Math.round(pct)));
                bar._progressBar.setAttribute('aria-valuetext', formatElapsedTime(media.currentTime));
            }
            updateElapsedDisplays();
        });

        media.addEventListener('loadedmetadata', function() {
            state.duration = Adapter.getDuration(state, function() {
                return media.duration;
            }, Log, 'HTML5 metadata');
            updateElapsedDisplays();
            if (bar._durationEl) {
                bar._durationEl.textContent = ' / ' + formatElapsedTime(state.duration);
            }
            if (bar._progressBar) { bar._progressBar.max = '100'; }
            // Wire up initial speed.
            if (config.playbackspeeds && config.playbackspeeds.length) {
                var speeds  = config.playbackspeeds.slice().sort(function(a, b) { return a - b; });
                var nearest = speeds.reduce(function(prev, curr) {
                    return Math.abs(curr - 1) < Math.abs(prev - 1) ? curr : prev;
                });
                media.playbackRate = nearest;
                state.playbackrate = nearest;
            }
            // Enforce maxplaybackrate when the media loads.
            // config.maxplaybackrate is stored in hundredths (150 = 1.5x); convert it to a float.
            if (config.maxplaybackrate > 0) {
                var maxRateLoad = config.maxplaybackrate / 100;
                if (media.playbackRate > maxRateLoad) {
                    media.playbackRate = maxRateLoad;
                    state.playbackrate = maxRateLoad;
                }
            }
            // An explicit report/replay link takes precedence over the saved resume point.
            if (typeof config.replaystart === 'number' && config.replaystart >= 0) {
                replayHTML5Fragment(
                    config.replaystart,
                    typeof config.replayend === 'number' ? config.replayend : null
                );
            } else {
                var resumePosition = resolveResumePosition(config.resumeposition);
                if (resumePosition > 2 && resumePosition < (state.duration || Infinity)) {
                    startProgrammaticSeek(resumePosition);
                    Tracker.syncTime(state, resumePosition, safeNumber(media.playbackRate, 1));
                    markAllowedForwardTime(Math.max(resumePosition, getMaxWatchedFromIntervals(state.intervaljson)));
                    showResumeNotice(resumePosition);
                }
            }
        });

        media.addEventListener('play', function() {
            var playBtn2 = bar.querySelector('.videotrack-ctrl-play');
            if (playBtn2) { playBtn2.textContent = '⏸'; playBtn2.setAttribute('aria-label', config.html5pauselabel); }
        });
        media.addEventListener('pause', function() {
            Api.cancelPlaybackStart(state);
            var playBtn2 = bar.querySelector('.videotrack-ctrl-play');
            if (playBtn2) { playBtn2.textContent = '▶'; playBtn2.setAttribute('aria-label', config.html5playlabel); }
        });

        // Append bar after the media element, inside the player wrapper.
        var wrapper = container.closest('.videotrack-player-wrap') || container.parentElement;
        wrapper.appendChild(bar);
    }

    /** Creates a button for the custom control bar. */
    function makeBtn(cls, icon, label) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-dark ' + cls;
        btn.textContent = icon;
        btn.setAttribute('aria-label', label);
        btn.setAttribute('title', label);
        return btn;
    }

    // ── Event handlers (tracking) ─────────────────────────────────────────
    // Called by buildPlayer() after the <video>/<audio> element is in the DOM.

    function attachTrackingEvents() {
        media.addEventListener('play', function() {
            if (focusGuard) {
                focusGuard.setPlaying(true);
            }
            state.ended = false;
            markHTML5PlaybackObserved();
            if (state.isProgrammaticSeek) {
                finishProgrammaticSeek(media.currentTime);
            }
            // Start a new segment only when no segment is already open.
            // state.playing is always false when play arrives because closeSegment()
            // resets it first (seeking/pause/ended). The !state.playing branch is the only
            // reachable path; the state.playing=true branch was dead code.
            if (!state.isSeeking && !state.playing) {
                Api.beginPlayback(config, state, media.currentTime, {
                    swallowFailures: true,
                    errorMessage: 'html5-playback-start'
                }).then(function(response) {
                    if (!response || media.paused || state.isSeeking || state.playing) {
                        return;
                    }
                    startSegment();
                    startHeartbeat();
                    setReactionButtons(true);
                }).catch(Log.debug);
            }
        });

        media.addEventListener('pause', function() {
            if (focusGuard) {
                focusGuard.setPlaying(false);
            }
            if (Adapter.isEnded(state, function() { return media.ended; }, Log, 'HTML5')) {
                return;
            }
            if (!state.isSeeking && !state._html5BlockedSeekResume) {
                stopHeartbeat();
                closeSegment('pause');
                setReactionButtons(false);
            }
        });

        media.addEventListener('ended', function() {
            Api.cancelPlaybackStart(state);
            if (focusGuard) {
                focusGuard.setPlaying(false);
            }
            state.ended = true;
            stopHeartbeat();
            closeSegment('ended').then(function() {
                Ui.notifyVideoEnded();
            });
            setReactionButtons(false); // Keep learner controls available while recording the ended state.
        });

        // Seek detection: HTML5 fires 'seeking' then 'seeked'.
        media.addEventListener('seeking', function() {
            if (focusGuard && !state.isProgrammaticSeek) {
                focusGuard.noteAction('seek');
            }
            var requested = safeNumber(media.currentTime, state.lasttime || 0);
            var forwardLimit = getAllowedForwardLimit();
            state.isSeeking = true;
            // Programmatic seek (replay, chapter, resume): the old segment was
            // already closed before currentTime changed in startProgrammaticSeek().
            if (state.isProgrammaticSeek) {
                return;
            }
            if (config.allowseekforward === false && requested > forwardLimit + 0.75) {
                blockForwardSeek(requested);
                return;
            }
            var seek = Tracker.resolveSeek(state, requested, config, 0.5);
            if (seek.blocked) {
                Tracker.blockSeek(state, 1000);
                media.currentTime = seek.fallbackTime;
                Tracker.syncTime(state, seek.fallbackTime);
                markAllowedForwardTime(seek.fallbackTime);
                return;
            }
            if (state.playing && seek.changed) {
                rotatePlayingSegmentForSeek(seek.oldTime, seek.newTime);
            }
        });

        media.addEventListener('seeked', function() {
            var current = safeNumber(media.currentTime, 0);
            state.isSeeking = false;
            finishProgrammaticSeek(current);
            if (state.playing && state.segmentstart === null) { startSegment(); }
            if (Tracker.shouldStopReplay(state, current)) {
                media.pause();
            }
        });

        media.addEventListener('timeupdate', function() {
            var current = safeNumber(media.currentTime, 0);
            if (focusGuard) {
                focusGuard.noteProgress(current);
            }
            var forwardLimit = getAllowedForwardLimit();
            var rate = enforceMaxPlaybackRate();
            if (!media.paused) {
                markHTML5PlaybackObserved();
            }
            if (state.isSeeking || state.seekblocked) {
                return;
            }
            var lastTracked = safeNumber(state.lasttime, forwardLimit);
            var naturalLimit = Math.max(forwardLimit, lastTracked) + getNaturalPlaybackTolerance(rate);
            if (config.allowseekforward === false && current > naturalLimit) {
                blockForwardSeek(current);
                return;
            }
            Tracker.syncTime(state, current, rate);
            markAllowedForwardTime(current);
            Progress.updateLiveProgress(state, current, Utils, PlayerCore, Log);
            if (Tracker.shouldStopReplay(state, current)) {
                Adapter.pause(function() {
                    return media.pause();
                }, Log, 'HTML5 replay pause');
            }
        });

        media.addEventListener('ratechange', function() {
            enforceMaxPlaybackRate();
        });
    }


    // ── Reaction buttons ──────────────────────────────────────────────────────

    /**
     * Enables or disables the reaction buttons based on playback state.
     * Uses only aria-disabled to keep inactive controls keyboard-focusable.
     * The click/keydown handlers block saving and announce why the action is unavailable.
     * By-design: reactions can only be saved while the video is playing.
     *
     * @param {boolean} playing  True = enable buttons; false = disable them.
     */
    function setReactionButtons(playing) {
        Reactions.setButtons(playing, config, reactionState, Ui);
    }


    function announceReactionUnavailable() {
        PlayerCore.announceReactionUnavailable(config, reactionState);
    }

    /**
     * Installs the click handler for reaction buttons and replay buttons.
     * Mirrors the logic of player.js to ensure consistent behaviour across all sources.
     */
    function installReactionHandler() {
        var root = PlayerCore.getPlayerShell(Log);
        if (!root) { return; }

        function appendReactionRow(eventid, reaction, videotime) {
            var tbody = document.getElementById('videotrack-my-reactions');
            if (!tbody) { return; }
            // Remove the 'no reactions yet' placeholder row when the first reaction is added.
            var placeholder = tbody.querySelector('.videotrack-no-reactions-placeholder');
            if (placeholder) { placeholder.parentNode.removeChild(placeholder); }
            var tr = document.createElement('tr');
            tr.setAttribute('data-eventid', eventid);
            // Timestamp cell
            var tdtime = document.createElement('td');
            tdtime.textContent = Utils.formatSeconds(videotime);
            tr.appendChild(tdtime);
            // Icon cell
            var tdicon = document.createElement('td');
            var span = document.createElement('span');
            span.className = 'videotrack-report-icon';
            var iconDescriptor = {
                type: reaction.type || reaction.icontype || 'emoji',
                src: reaction.src || reaction.iconsrc || '',
                iconclass: reaction.iconclass || '',
                text: reaction.text || reaction.icontext || ''
            };
            Ui.appendIconSafe(span, iconDescriptor);
            if (reaction.label) {
                var labelspan = document.createElement('span');
                labelspan.className = 'videotrack-reaction-label';
                labelspan.textContent = reaction.label;
                span.appendChild(document.createTextNode(' '));
                span.appendChild(labelspan);
            }
            tdicon.appendChild(span);
            tr.appendChild(tdicon);
            // Description
            var tddesc = document.createElement('td');
            tddesc.textContent = reaction.description || '';
            tr.appendChild(tddesc);
            // Replay
            var tdreplay = document.createElement('td');
            var replaybtn = document.createElement('button');
            replaybtn.type = 'button';
            replaybtn.className = 'btn btn-secondary btn-sm videotrack-replay';
            replaybtn.textContent = config.replaylabel;
            replaybtn.setAttribute('aria-label',
                (config.replaylabel) + ' — ' + Utils.formatSeconds(videotime));
            replaybtn.dataset.start = Math.max(0, videotime - 30);
            replaybtn.dataset.end   = videotime + 30;
            replaybtn.dataset.time  = videotime;
            tdreplay.appendChild(replaybtn);
            tr.appendChild(tdreplay);
            // Delete
            var tddel = document.createElement('td');
            var delbtn = document.createElement('button');
            delbtn.type = 'button';
            delbtn.className = 'btn btn-link btn-sm videotrack-delete-reaction';
            delbtn.textContent = config.removelabel;
            delbtn.setAttribute('data-eventid', eventid);
            delbtn.setAttribute('aria-label',
                (config.removelabel) + ' — ' + (reaction.label || '') + ' — ' + Utils.formatSeconds(videotime));
            tddel.appendChild(delbtn);
            tr.appendChild(tddel);
            tbody.appendChild(tr);
            return tr;
        }

        // A1 fix: keydown handler for Enter/Space on aria-disabled reaction buttons.
        // Browsers do not consistently fire 'click' for Enter/Space on buttons with
        // aria-disabled=true, so screen reader users got no feedback.
        if (state._reactionRootCleanup) {
            state._reactionRootCleanup();
        }
        var reactionKeydownHandler = function(e) {
            if (e.key !== 'Enter' && e.key !== ' ') { return; }
            var reactionbtn = e.target.closest('.videotrack-reaction-btn');
            if (reactionbtn && reactionbtn.getAttribute('aria-disabled') === 'true') {
                e.preventDefault();
                announceReactionUnavailable();
            }
        };

        var reactionClickHandler = function(e) {
            var reactionbtn = e.target.closest('.videotrack-reaction-btn');
            if (reactionbtn && reactionbtn.getAttribute('aria-disabled') === 'true') {
                e.preventDefault();
                e.stopPropagation();
                announceReactionUnavailable();
                return;
            }
            if (reactionbtn) {
                e.preventDefault();
                if (reactionbtn.getAttribute('aria-busy') === 'true' || state._reactionSavePending) {
                    return;
                }
                state._reactionSavePending = true;
                var currentTime = 0;
                var pendingRow = null;
                var reactionData = {
                    label: reactionbtn.getAttribute('data-reactionlabel') || '',
                    description: reactionbtn.getAttribute('data-reactiondesc') || '',
                    icontype: reactionbtn.getAttribute('data-reactionicontype') || 'emoji',
                    iconclass: reactionbtn.getAttribute('data-reactioniconclass') || '',
                    iconsrc: reactionbtn.getAttribute('data-reactioniconsrc') || '',
                    icontext: reactionbtn.getAttribute('data-reactionicontext') || '',
                };
                reactionbtn.classList.add('videotrack-saving');
                reactionbtn.setAttribute('aria-busy', 'true');
                reactionbtn.disabled = true;
                saveCurrentProgress('reaction').then(function(progressResponse) {
                    return Promise.resolve(getInteractionVideoTime()).then(function(time) {
                        currentTime = resolveReactionTime(progressResponse, time);
                        pendingRow = appendReactionRow('pending-' + Date.now(), reactionData, currentTime);
                        if (pendingRow) {
                            pendingRow.classList.add('videotrack-reaction-pending');
                        }
                    });
                }).then(function() {
                    return Api.call('mod_videotrack_save_reaction', {
                        cmid:       config.cmid,
                        sessionid:  state.sessionid,
                        reactionid: Utils.safeInt(reactionbtn.getAttribute('data-reactionid'), 0),
                        videotime:  currentTime,
                        playbackrate: state.playbackrate || 1,
                    }, {timeout: 60000});
                }).then(function(response) {
                    state._reactionSavePending = false;
                    reactionbtn.classList.remove('videotrack-saving');
                    reactionbtn.removeAttribute('aria-busy');
                    reactionbtn.disabled = false;
                    if (response && Number(response.reactioneventid) <= 0 && pendingRow) {
                        // The server accepted the request but deliberately ignored it, for example because it was
                        // a consecutive duplicate reaction inside the anti-spam window. Do not leave the optimistic
                        // row in the table because no persistent event was created.
                        removeReactionRow(pendingRow);
                        pendingRow = null;
                    } else if (response && response.reaction && pendingRow) {
                        var savedReaction = response.reaction;
                        savedReaction.videotime = Number(savedReaction.videotime || currentTime);
                        var rowEventId = response.reactioneventid;
                        removeReactionRow(pendingRow);
                        pendingRow = appendReactionRow(rowEventId, savedReaction, savedReaction.videotime);
                        if (pendingRow) {
                            pendingRow.classList.remove('videotrack-reaction-pending');
                        }
                    } else if (response && response.reactioneventid && pendingRow) {
                        pendingRow.setAttribute('data-eventid', response.reactioneventid);
                        pendingRow.classList.remove('videotrack-reaction-pending');
                        var deletebtn = pendingRow.querySelector('.videotrack-delete-reaction');
                        if (deletebtn) {
                            deletebtn.setAttribute('data-eventid', response.reactioneventid);
                        }
                    }
                }).catch(function(err) {
                    state._reactionSavePending = false;
                    reactionbtn.classList.remove('videotrack-saving');
                    reactionbtn.removeAttribute('aria-busy');
                    reactionbtn.disabled = false;
                    if (isDefinitiveReactionFailure(err)) {
                        removeReactionRow(pendingRow);
                        PlayerCore.showErrorStatusMessage(err, config.reactionerrorlabel, config.dismisslabel);
                    } else {
                        Debug.log('reactionsaveunknownafteroptimisticappend', {
                            message: err && err.message,
                            errorcode: err && err.errorcode
                        });
                    }
                });
                return;
            }

            var deletebtn = e.target.closest('.videotrack-delete-reaction');
            if (deletebtn) {
                var eventid = Utils.safeInt(deletebtn.getAttribute('data-eventid'), 0);
                if (deletebtn.getAttribute('aria-busy') === 'true' || state._reactionDeletePending === eventid) {
                    return;
                }
                state._reactionDeletePending = eventid;
                deletebtn.setAttribute('aria-busy', 'true');
                deletebtn.disabled = true;
                var row   = deletebtn.closest('tr');
                var tbody = document.getElementById('videotrack-my-reactions');
                var rows  = tbody ? Array.from(tbody.querySelectorAll('tr[data-eventid]')) : [];
                var idx   = rows.indexOf(row);
                Api.call('mod_videotrack_delete_reaction', {
                    cmid: config.cmid,
                    reactioneventid: eventid,
                }).then(updateProgress).then(function(response) {
                    state._reactionDeletePending = null;
                    deletebtn.removeAttribute('aria-busy');
                    deletebtn.disabled = false;
                    if (response && response.deleted) {
                        if (row) { row.remove(); }
                        var remaining = tbody
                            ? Array.from(tbody.querySelectorAll('tr[data-eventid]'))
                            : [];
                        if (remaining.length > 0) {
                            var target = remaining[Math.min(idx, remaining.length - 1)];
                            var focusBtn = target.querySelector('button');
                            if (focusBtn) { focusBtn.focus(); }
                        } else if (tbody) {
                            tbody.setAttribute('tabindex', '-1');
                            tbody.focus();
                            tbody.removeAttribute('tabindex');
                        }
                    }
                }).catch(function(err) {
                    state._reactionDeletePending = null;
                    deletebtn.removeAttribute('aria-busy');
                    deletebtn.disabled = false;
                    PlayerCore.showErrorStatusMessage(err, config.reactionerrorlabel, config.dismisslabel);
                });
            }
        };
        root.addEventListener('keydown', reactionKeydownHandler);
        root.addEventListener('click', reactionClickHandler);
        var cleanupReactionRootHandlers = function() {
            root.removeEventListener('keydown', reactionKeydownHandler);
            root.removeEventListener('click', reactionClickHandler);
            window.removeEventListener('pagehide', cleanupReactionRootHandlers);
            window.removeEventListener('beforeunload', cleanupReactionRootHandlers);
            if (state._reactionRootCleanup === cleanupReactionRootHandlers) {
                state._reactionRootCleanup = null;
            }
        };
        state._reactionRootCleanup = cleanupReactionRootHandlers;
        window.addEventListener('pagehide', cleanupReactionRootHandlers, {once: true});
        window.addEventListener('beforeunload', cleanupReactionRootHandlers, {once: true});
    }


    /** Returns the current video timestamp for the HTML5 player. */
    function getCurrentVideoTime() {
        return Adapter.getCurrentTime(state, function() {
            return media ? safeNumber(media.currentTime, state.lasttime || 0) : state.lasttime;
        }, Log, 'HTML5');
    }

    /**
     * Return the trusted pre-rollback timestamp while a blocked seek is settling.
     *
     * @returns {number|Promise<number>} Safe timestamp for learner interactions.
     */
    function getInteractionVideoTime() {
        if (state.seekblocked || state._html5BlockedSeekResume) {
            return Tracker.normaliseTime(state.lasttime);
        }
        return getCurrentVideoTime();
    }

    /** Initialise privacy-safe integrity indicators and optional focus controls. */
    function initialiseFocusGuard() {
        focusGuard = FocusGuard.create({
            config: config,
            state: state,
            getCurrentTime: getCurrentVideoTime,
            pause: function() {
                return media ? media.pause() : null;
            },
            showMessage: function(message) {
                PlayerCore.showStatusMessage(message, false, config.dismisslabel, config.statusinfotimeoutms);
            }
        });
    }

    /**
     * Toggle the notes panel through the shared helper.
     */
    function installNotesToggle() {
        PlayerCore.installNotesToggle(config, Utils, 'notes panel state');
    }

    /**
     * Feature 11: student personal notes.
     * Handles saving and deleting timestamped text notes.
     * The "Save" button is active only during playback (aria-disabled).
     */
    function installNoteHandler() {
        PlayerCore.installNoteHandler({
            Api: Api,
            Log: Log,
            Utils: Utils,
            config: config,
            state: state,
            getCurrentVideoTime: getInteractionVideoTime,
            saveCurrentProgress: saveCurrentProgress
        });
    }

    /**
     * Install private bookmark handling for the HTML5 player.
     */
    function installBookmarkHandler() {
        PlayerCore.installBookmarkHandler({
            Api: Api,
            Log: Log,
            Utils: Utils,
            config: config,
            state: state,
            getCurrentVideoTime: getInteractionVideoTime,
            saveCurrentProgress: saveCurrentProgress
        });
    }

    /**
     * Navigate from transcript or chapter controls while respecting seek policy.
     *
     * @param {number} target Target timestamp.
     * @returns {boolean} True when navigation was accepted.
     */
    function navigateTimedText(target) {
        if (!hasMedia()) {
            return false;
        }
        var destination = Tracker.normaliseTime(target);
        var current = Tracker.normaliseTime(getCurrentVideoTime());
        if (destination > current + 0.5 && config.allowseekforward === false &&
                destination > getAllowedForwardLimit() + 0.75) {
            return false;
        }
        if (destination < current - 0.5 && config.allowseekbackward === false) {
            return false;
        }
        startProgrammaticSeek(destination);
        return true;
    }

    /**
     * Feature 12: Gestione overlay poster pre-play.
     * Removes the overlay on the first PLAYING event or overlay play button click.
     */
    function installPosterHandler() {
        var overlay = document.getElementById('videotrack-poster-overlay');
        if (!overlay) { return; } // Nessun poster caricato.

        function removePoster() {
            PlayerCore.removePoster(overlay);
        }

        var playBtn = document.getElementById('videotrack-poster-play-btn');
        var posterClickHandler = function() {
            removePoster();
            // Start playback through the HTML5 media element (not a YouTube/Vimeo player).
            if (media) {
                media.play().catch(function(err) {
                    Debug.log('playrequestfailed', {message: err});
                });
            }
        };
        if (playBtn) {
            playBtn.addEventListener('click', posterClickHandler);
        }

        // Remove the poster on the first HTML5 media play event.
        state._posterRemoved = false;
        state._posterPlayListener = function(e) {
            PlayerCore.onFirstPlay(e, state, removePoster);
        };
        document.addEventListener('videotrack:playstate', state._posterPlayListener);

        state._posterCleanup = function() {
            if (playBtn) {
                playBtn.removeEventListener('click', posterClickHandler);
            }
            if (state._posterPlayListener) {
                document.removeEventListener('videotrack:playstate', state._posterPlayListener);
                state._posterPlayListener = null;
            }
            window.removeEventListener('pagehide', state._posterCleanup);
            window.removeEventListener('beforeunload', state._posterCleanup);
            state._posterCleanup = null;
        };
        window.addEventListener('pagehide', state._posterCleanup, {once: true});
        window.addEventListener('beforeunload', state._posterCleanup, {once: true});
    }
    // ── Public API ────────────────────────────────────────────────────────

    return {
        init: function(initConfig) {
            controlBar        = null;
            config             = resolveConfig(initConfig);
            PlayerCore.configureStatus(config);
            // Reaction timing caps match settings.php limits and are centralised in core/reactions.
            var interval = parseInt(config.reactionannouncementinterval, 10);
            reactionState.unavailableInterval = interval === 0 ? Number.MAX_SAFE_INTEGER :
                Math.max(Reactions.MIN_UNAVAILABLE_ANNOUNCE_INTERVAL,
                    Math.min(Reactions.MAX_UNAVAILABLE_ANNOUNCE_INTERVAL,
                        interval || Reactions.DEFAULT_UNAVAILABLE_ANNOUNCE_INTERVAL));
            var debounce = parseInt(config.reactionreadydebouncems, 10);
            reactionState.debounceMs = debounce === 0 ? Reactions.MIN_READY_DEBOUNCE_MS :
                Math.max(Reactions.MIN_READY_DEBOUNCE_MS,
                    Math.min(Reactions.MAX_READY_DEBOUNCE_MS, debounce || Reactions.DEFAULT_READY_DEBOUNCE_MS));
            HEARTBEAT_INTERVAL = Tracker.normaliseHeartbeatInterval(config, 30);
            state.sessionid    = uuid();
            initialiseFocusGuard();
            state.intervaljson = config.intervaljson || state.intervaljson || '[]';
            markAllowedForwardTime(getMaxWatchedFromIntervals(state.intervaljson));
            installGlobalListeners();
            installReactionHandler();
            installNoteHandler();
            installNotesToggle();
            installBookmarkHandler();
            installPosterHandler();
            Forum.install({
                buttonId: config.forumpostbuttonid,
                statusId: config.forumpoststatusid,
                composerUrl: config.forumposturl,
                sessionId: state.sessionid,
                getCurrentTime: getInteractionVideoTime,
                saveCurrentProgress: config.trackingenabled ? saveCurrentProgress : null,
                getDuration: function() {
                    return media ? media.duration : Number(config.duration) || 0;
                },
                errorLabel: config.forumposterrorlabel
            });
            buildPlayer();
            state._timedTextController = TimedText.create({
                config: config,
                getCurrentTime: getCurrentVideoTime,
                navigate: navigateTimedText
            });
        }
    };
});
