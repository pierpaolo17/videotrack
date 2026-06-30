/* global YT */
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
    'mod_videotrack/core/debug'
], function(Log, Api, Adapter, Utils, Ui, Progress, State, Reactions, Tracker, PlayerCore, Debug) {
    'use strict';

    var player = null;
    var config = null;
    var reactionState = Reactions.createState();
    // HEARTBEAT_INTERVAL is initialised in init() from the value configured
    // by the administrator in Site administration > Plugins > Activity modules > Video track.
    var HEARTBEAT_INTERVAL = 30; // Fallback value, overridden by config.heartbeatinterval
    var state = State.create();
    state.ajaxRequestScope = Api.createRequestScope();


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

    function loadApi(callback) {
        if (window.YT && window.YT.Player) {
            callback();
            return;
        }
        var previous = window.onYouTubeIframeAPIReady;
        window.onYouTubeIframeAPIReady = function() {
            if (typeof previous === 'function') {
                previous();
            }
            callback();
        };
        var tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        document.head.appendChild(tag);
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

    /**
     * Draw the coloured canvas bar representing watched intervals.
     * Green = watched, light grey = not watched.
     *
     * @param {string} intervaljson  JSON array di [start,end] pairs.
     * @param {number} duration Total video duration in seconds.
     */
    function updateIntervalBar(intervaljson, duration) {
        PlayerCore.updateIntervalBar(intervaljson, duration, Log);
    }

    function updateLiveIntervalBar(current) {
        Progress.updateLiveProgress(state, current, Utils, PlayerCore, Log);
    }

    function markAllowedForwardTime(current) {
        current = Tracker.normaliseTime(current);
        state.maxallowedtime = Math.max(Number(state.maxallowedtime) || 0, current);
    }

    function getAllowedForwardLimit() {
        return Math.max(Number(state.maxallowedtime) || 0, getMaxWatchedFromIntervals(state.intervaljson));
    }

    function isForwardTargetAlreadyWatched(target, tolerance) {
        var allowed = getAllowedForwardLimit();
        return Tracker.normaliseTime(target) <= allowed + (typeof tolerance === 'number' ? tolerance : 0.75);
    }

    function isNormalForwardPlayback(current, rate) {
        var start = Number(state.segmentstart);
        var wallclock = Number(state.wallclockstart);
        var now = Math.floor(Date.now() / 1000);
        var elapsed;
        if (!state.playing || !isFinite(start) || !isFinite(wallclock) || wallclock <= 0) {
            return false;
        }
        elapsed = Math.max(0, now - wallclock) * Math.max(Number(rate) || state.playbackrate || 1, 1);
        return Tracker.normaliseTime(current) <= start + elapsed + 2;
    }

    function isForwardSeekRecoveryPlayback(current, previous, threshold, now) {
        return state.forwardseekrecoveryuntil && now <= state.forwardseekrecoveryuntil && state.playing &&
                current >= previous && current <= previous + threshold;
    }

    function resetForwardSeekRecovery(fallback) {
        var safeFallback = Tracker.normaliseTime(fallback);
        Tracker.syncTime(state, safeFallback);
        state.lastSeekPollAt = Date.now();
        state.forwardseekrecoveryuntil = Date.now() + 4000;
        if (state.playing) {
            Tracker.openSegment(state, safeFallback, Math.floor(Date.now() / 1000), state.playbackrate);
        }
        markAllowedForwardTime(safeFallback);
        updateLiveIntervalBar(safeFallback);
    }

    function getMaxWatchedFromIntervals(intervaljson) {
        var max = 0;
        var intervals;
        try {
            intervals = JSON.parse(intervaljson || '[]');
        } catch (e) {
            return 0;
        }
        if (!Array.isArray(intervals)) {
            return 0;
        }
        intervals.forEach(function(interval) {
            if (Array.isArray(interval) && interval.length > 1) {
                var end = Number(interval[1]);
                if (isFinite(end) && end > max) {
                    max = end;
                }
            }
        });
        return Tracker.normaliseTime(max);
    }


    function getResumeStorageKey() {
        return 'videotrack:lastposition:' + String(config && config.cmid ? config.cmid : '0');
    }

    function readStoredResumePosition() {
        var stored;
        try {
            stored = window.localStorage ? window.localStorage.getItem(getResumeStorageKey()) : null;
        } catch (e) {
            stored = null;
        }
        stored = Number(stored);
        return isFinite(stored) && stored > 2 ? stored : 0;
    }

    function rememberResumePosition(position) {
        position = Tracker.normaliseTime(position);
        if (position <= 2) {
            return;
        }
        try {
            if (window.localStorage) {
                window.localStorage.setItem(getResumeStorageKey(), String(position));
            }
        } catch (e) {
            // Browser storage may be disabled; DB resume remains authoritative.
        }
    }

    function resolveResumePosition() {
        var serverPosition = Number(config && config.resumeposition) || 0;
        var storedPosition = readStoredResumePosition();
        return Math.max(serverPosition, storedPosition);
    }

    function initialiseKnownProgress(position) {
        var current = Tracker.normaliseTime(position);
        state.lasttime = current;
        state.intervaljson = config.intervaljson || state.intervaljson || '[]';
        if (config.duration && !state.duration) {
            state.duration = Number(config.duration) || 0;
        }
        markAllowedForwardTime(Math.max(current, getMaxWatchedFromIntervals(state.intervaljson)));
        updateLiveIntervalBar(current);
    }

    function blockForwardSeek(target, fallbackTime) {
        var allowed = getAllowedForwardLimit();
        var fallback = typeof fallbackTime === 'number' ? fallbackTime : Tracker.normaliseTime(state.lasttime);
        fallback = Math.max(0, Tracker.normaliseTime(Math.max(fallback, allowed)));
        if (target <= fallback + 0.75 || isForwardTargetAlreadyWatched(target, 0.75)) {
            return false;
        }
        Tracker.blockSeek(state, 1000);
        Adapter.seek(fallback, function(safeTarget) {
            player.seekTo(safeTarget, true);
            resetForwardSeekRecovery(safeTarget);
        }, Log, 'YouTube blocked forward seek');
        return true;
    }

    function saveSegment(start, end, reason) {
        return Api.saveSegment(config, state, start, end, reason, {
            swallowFailures: true,
            errorMessage: 'youtube-player-event',
            requestScope: state.ajaxRequestScope
        }).then(updateProgress);
    }

    function hasPlayer(methods, capability) {
        return Adapter.hasCapability(player, 'youtube', capability, methods);
    }

    function saveCurrentProgress(reason) {
        return PlayerCore.saveCurrentProgress(state, getCurrentVideoTime, saveSegment, reason, hasPlayer(null, 'currentTime'));
    }

    function closeCurrentSegment(reason) {
        return Tracker.closeAndSaveSegment(state, getCurrentVideoTime, saveSegment, reason, hasPlayer(null, 'currentTime'))
            .catch(Log.debug);
    }



    function getConfiguredMaxPlaybackRate() {
        var configured = Number(config && config.maxplaybackrate ? config.maxplaybackrate : 0) / 100;
        if (isFinite(configured) && configured > 0) {
            return configured;
        }
        // Some courses disable speed changes without setting an explicit maximum.
        // Treat that as a 1x ceiling so native YouTube controls cannot bypass
        // the teacher's intent.
        if (config && config.allowplaybackratechange === false) {
            return 1;
        }
        return 0;
    }

    function getPlaybackRatePenalty() {
        return 0.5;
    }

    function writePlaybackRate(rate, label) {
        var safeRate = Number(rate);
        if (!isFinite(safeRate) || safeRate <= 0) {
            safeRate = getPlaybackRatePenalty();
        }
        if (player && typeof player.setPlaybackRate === 'function') {
            try {
                player.setPlaybackRate(safeRate);
            } catch (error) {
                Log.debug((label || 'YouTube playback rate limit') + ': ' + error);
            }
        }
        state.playbackrate = safeRate;
        return Adapter.setPlaybackRate(safeRate, function(adapterRate) {
            if (player && typeof player.setPlaybackRate === 'function') {
                return player.setPlaybackRate(adapterRate);
            }
            return null;
        }, state, Log, label || 'YouTube playback rate limit');
    }

    function enforceMaxPlaybackRate(label) {
        var maxRate;
        var currentRate;
        var resetRate;
        if (!player || typeof player.getPlaybackRate !== 'function' || typeof player.setPlaybackRate !== 'function') {
            return state.playbackrate || 1;
        }
        maxRate = getConfiguredMaxPlaybackRate();
        currentRate = Adapter.getPlaybackRate(state, function() {
            return player.getPlaybackRate();
        }, Log, label || 'YouTube playback rate');
        if (maxRate > 0 && currentRate > maxRate) {
            resetRate = getPlaybackRatePenalty();
            writePlaybackRate(resetRate, label || 'YouTube playback rate limit');
            [50, 150, 300, 600, 1000].forEach(function(delay) {
                window.setTimeout(function() {
                    if (player && typeof player.getPlaybackRate === 'function' && player.getPlaybackRate() > maxRate) {
                        writePlaybackRate(resetRate, (label || 'YouTube playback rate limit') + ' retry');
                    }
                }, delay);
            });
            return resetRate;
        }
        state.playbackrate = currentRate;
        return currentRate;
    }

    function installPlaybackRateGuard() {
        if (state._playbackRateGuard || getConfiguredMaxPlaybackRate() <= 0) {
            return;
        }
        state._playbackRateGuard = window.setInterval(function() {
            if (player && typeof player.getPlaybackRate === 'function' && typeof player.setPlaybackRate === 'function') {
                enforceMaxPlaybackRate('YouTube playback-rate guard');
            }
        }, 100);
    }

    function cleanupPlaybackRateGuard() {
        if (state._playbackRateGuard) {
            window.clearInterval(state._playbackRateGuard);
            state._playbackRateGuard = null;
        }
    }

    function startCurrentSegment() {
        var currentTime = player.getCurrentTime();
        var wallclock = Math.floor(Date.now() / 1000);
        // Feature 6: apply the maximum speed limit when configured.
        var currentRate = enforceMaxPlaybackRate('YouTube segment start playback rate');
        Tracker.openSegment(state, currentTime, wallclock, currentRate);
        state.lastSeekPollAt = Date.now();
        state._ignoreNextSeekPoll = true;
        markAllowedForwardTime(currentTime);
        updateLiveIntervalBar(currentTime);
        setReactionButtons(true);
        // Restart polling if it was suspended (hidden tab becomes visible again).
        if (!state.heartbeatid) {
            state.heartbeatid = Tracker.startPolling(state, function() {
                if (player) { handleSeekByPolling(); }
            }, HEARTBEAT_INTERVAL);
        }
    }

    function setReactionButtons(playing) {
        Reactions.setButtons(playing, config, reactionState, Ui);
    }


    function announceReactionUnavailable() {
        PlayerCore.announceReactionUnavailable(config, reactionState);
    }

    function replayFragment(start, end, autoplay) {
        if (!hasPlayer(['seekTo'])) {
            return;
        }
        state.currentReplayEnd = typeof end === 'number' ? end : null;
        // Mark as programmatic so handleSeekByPolling ignores this seek.
        Tracker.markProgrammaticSeek(state);
        Adapter.seek(start || 0, function(target) {
            player.seekTo(target, true);
        }, Log, 'YouTube replay seek');
        if (autoplay !== false) {
            Adapter.play(function() {
                return player.playVideo();
            }, Log, 'YouTube replay play');
        }
    }

    function handleSeekByPolling() {
        if (!hasPlayer(['getCurrentTime'])) {
            return;
        }
        // Ignore polling during programmatic seeks (replay, resume, skip buttons).
        // Reset the flag here so it stays active for exactly one polling cycle.
        var polledTime = player.getCurrentTime();
        if (state._ignoreNextSeekPoll) {
            state._ignoreNextSeekPoll = false;
            Tracker.syncTime(state, polledTime);
            rememberResumePosition(polledTime);
            markAllowedForwardTime(polledTime);
            updateLiveIntervalBar(polledTime);
            return;
        }
        if (Tracker.consumeProgrammaticSeek(state, polledTime)) {
            markAllowedForwardTime(polledTime);
            updateLiveIntervalBar(polledTime);
            return;
        }
        // If a seek was just blocked, ignore polling while the provider
        // completes the bounce back to the allowed fallback time. Do not sync
        // lasttime here: the current provider time may still be the forbidden
        // target for one or more polling cycles.
        if (state.seekblocked) {
            return;
        }
        var current = Tracker.normaliseTime(polledTime);
        var previous = Tracker.normaliseTime(state.lasttime);
        var delta = current - previous;
        var now = Date.now();
        var elapsed = state.lastSeekPollAt ? Math.max(0, (now - state.lastSeekPollAt) / 1000) : 0;
        state.lastSeekPollAt = now;
        if (Math.abs(delta) < 0.2) {
            state.lasttime = current;
            updateLiveIntervalBar(current);
            return;
        }
        var rate = enforceMaxPlaybackRate('YouTube seek polling playback rate');
        var expectedDelta = state.playing && elapsed > 0 ? elapsed * Math.max(rate || 1, 1) : 0;
        var threshold = state.playing ? Math.max(1.5, expectedDelta + 1.0) : 0.5;
        var allowedLimit = getAllowedForwardLimit();
        var looksLikePlayback = current > previous && current <= previous + threshold &&
                (isNormalForwardPlayback(current, rate) || isForwardSeekRecoveryPlayback(current, previous, threshold, now));
        if (config.allowseekforward === false && current > allowedLimit + 0.75 && !looksLikePlayback &&
                blockForwardSeek(current, allowedLimit)) {
            return;
        }
        if (config.allowseekforward === false && current > allowedLimit + 0.75 && looksLikePlayback) {
            Tracker.syncTime(state, current);
            rememberResumePosition(current);
            markAllowedForwardTime(current);
            updateLiveIntervalBar(current);
            return;
        }
        if (Math.abs(delta) > threshold) {
            if (config.allowseekforward === false && current > previous && !isForwardTargetAlreadyWatched(current, 0.75) &&
                    blockForwardSeek(current, allowedLimit)) {
                return;
            }
            var seekconfig = config;
            if (config.allowseekforward === false && current > previous && isForwardTargetAlreadyWatched(current, 0.75)) {
                seekconfig = Object.assign({}, config, {allowseekforward: true});
            }
            var seek = Tracker.resolveSeek(state, current, seekconfig, 0);
            var oldtime = seek.oldTime;
            if (seek.blocked && seek.forward) {
                Tracker.blockSeek(state, 1000);
                Adapter.seek(oldtime, function(target) {
                    player.seekTo(target, true);
                    Tracker.syncTime(state, target);
                }, Log, 'YouTube blocked forward seek');
                return;
            }
            if (seek.blocked && seek.backward) {
                Tracker.blockSeek(state, 1000);
                Adapter.seek(oldtime, function(target) {
                    player.seekTo(target, true);
                    Tracker.syncTime(state, target);
                }, Log, 'YouTube blocked backward seek');
                return;
            }
            if (state.playing) {
                closeCurrentSegment('seek');
                // Seek permesso: apre nuovo segmento dalla posizione corrente.
                Tracker.openSegment(state, player.getCurrentTime(), Math.floor(Date.now() / 1000), state.playbackrate);
            }
        }
        Tracker.syncTime(state, current);
        rememberResumePosition(current);
        if (current >= previous && (!config || config.allowseekforward !== false || current <= getAllowedForwardLimit() + 0.75)) {
            markAllowedForwardTime(current);
        }
        updateLiveIntervalBar(current);
        if (Tracker.shouldStopReplay(state, current)) {
            Adapter.pause(function() {
                return player.pauseVideo();
            }, Log, 'YouTube replay pause');
        }

        // Heartbeat: provider-neutral guard and error handling live in core/tracker.
        Tracker.runHeartbeat({
            state: state,
            heartbeatInterval: HEARTBEAT_INTERVAL,
            getCurrentTime: getCurrentVideoTime,
            saveSegment: saveSegment,
            hasPlayer: function() {
                return hasPlayer(['getCurrentTime']);
            },
            log: Log
        });
    }

    function onPlayerStateChange(event) {
        state.duration = Adapter.getDuration(state, function() {
            return player.getDuration ? player.getDuration() : state.duration;
        }, Log, 'YouTube');
        if (event.data === YT.PlayerState.PLAYING) {
            enforceMaxPlaybackRate('YouTube state-change playback rate');
            // Do not treat the first PLAYING notification as a learner seek.
            // Some providers fire it only after a few seconds of normal playback;
            // blocking here caused the opening seconds to be replayed.
            if (!state.playing) {
                startCurrentSegment();
            }
        } else if (event.data === YT.PlayerState.PAUSED) {
            setReactionButtons(false); // CRIT-2: disable buttons on pause
            rememberResumePosition(player && player.getCurrentTime ? player.getCurrentTime() : state.lasttime);
            closeCurrentSegment('pause');
        } else if (event.data === YT.PlayerState.ENDED) {
            rememberResumePosition(player && player.getCurrentTime ? player.getCurrentTime() : state.lasttime);
            state.ended = true;
            reactionState.readyAnnounced = false;
            setReactionButtons(false); // CRIT-2: disable buttons at video end
            closeCurrentSegment('ended');
        }
    }

    /**
     * Shows a temporary banner informing the student about automatic resume.
     * @param {number} seconds Resume position in seconds.
     */
    function showResumeNotice(seconds) {
        PlayerCore.showResumeNotice(seconds, config, Utils);
    }

    /**
     * Install lifecycle listeners used for tracking, visibility changes and unload beacons.
     */
    function installGlobalListeners() {
        Tracker.installLifecycleHandlers({
            state: state,
            closeSegment: closeCurrentSegment,
            onHidden: function() {
                setReactionButtons(false);
            },
            hasPlayer: function() { return hasPlayer(['getCurrentTime']); },
            sendBeacon: function() {
                return Tracker.sendUnloadBeacon({
                    state: state,
                    hasPlayer: function() { return hasPlayer(['getCurrentTime']); },
                    getCurrentTime: getCurrentVideoTime,
                    sendSegment: function(start, end) {
                        return PlayerCore.sendBeaconSegment(config, state, start, end, Utils, Log);
                    }
                });
            }
        });
        var root = PlayerCore.getPlayerShell(Log);
        if (!root) { return; }
        // A1 fix: keydown handler for Enter/Space on aria-disabled reaction buttons.
        // Browsers do not consistently fire 'click' for Enter/Space on buttons with
        // aria-disabled=true, so screen reader users got no feedback when pressing
        // these keys. The handler mirrors the click guard and calls the same
        // announceReactionUnavailable() function.
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
                // Immediate visual feedback: disable the button while the AJAX save is running.
                reactionbtn.classList.add('videotrack-saving');
                reactionbtn.setAttribute('aria-busy', 'true');
                reactionbtn.disabled = true;
                saveCurrentProgress('reaction').then(function(progressResponse) {
                    return Promise.resolve(getCurrentVideoTime()).then(function(time) {
                        currentTime = resolveReactionTime(progressResponse, time);
                    });
                }).then(function() {
                    return Api.call('mod_videotrack_save_reaction', {
                        cmid: config.cmid,
                        sessionid: state.sessionid,
                        reactionid: Utils.safeInt(reactionbtn.getAttribute('data-reactionid'), 0),
                        videotime: currentTime,
                        playbackrate: Adapter.getPlaybackRate(state, function() {
                            return player.getPlaybackRate ? player.getPlaybackRate() : state.playbackrate;
                        }, Log, 'YouTube reaction')
                    }, {timeout: 60000});
                }).then(updateProgress).then(function(response) {
                    state._reactionSavePending = false;
                    reactionbtn.classList.remove('videotrack-saving');
                    reactionbtn.removeAttribute('aria-busy');
                    reactionbtn.disabled = false;
                    if (response && response.reactioneventid) {
                        try {
                            appendReactionRow(response.reactioneventid, {
                                label: reactionbtn.getAttribute('data-reactionlabel') || '',
                                description: reactionbtn.getAttribute('data-reactiondesc') || '',
                                icontype: reactionbtn.getAttribute('data-reactionicontype') || 'emoji',
                                iconclass: reactionbtn.getAttribute('data-reactioniconclass') || '',
                                iconsrc: reactionbtn.getAttribute('data-reactioniconsrc') || '',
                                icontext: reactionbtn.getAttribute('data-reactionicontext') || ''
                            }, currentTime);
                        } catch (appendError) {
                            Debug.log('reactionrowappendfailed', {message: appendError && appendError.message});
                        }
                    }
                }).catch(function(err) {
                    state._reactionSavePending = false;
                    reactionbtn.classList.remove('videotrack-saving');
                    reactionbtn.removeAttribute('aria-busy');
                    reactionbtn.disabled = false;
                    // Use the server message when available (for example, playback is required).
                    PlayerCore.showErrorStatusMessage(err, config.reactionerrorlabel, config.dismisslabel);
                });
                return;
            }
            var replaybtn = e.target.closest('.videotrack-replay');
            if (replaybtn && player) {
                replayFragment(
                    parseFloat(replaybtn.getAttribute('data-start')),
                    parseFloat(replaybtn.getAttribute('data-end')),
                    true
                );
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
                var row = deletebtn.closest('tr');
                // Move focus before removing the row so it is not lost.
                var tbody = document.getElementById('videotrack-my-reactions');
                var rows  = tbody ? Array.from(tbody.querySelectorAll('tr[data-eventid]')) : [];
                var idx   = rows.indexOf(row);
                Api.call('mod_videotrack_delete_reaction', {
                    cmid: config.cmid,
                    reactioneventid: eventid
                }).then(updateProgress).then(function(response) {
                    state._reactionDeletePending = null;
                    deletebtn.removeAttribute('aria-busy');
                    deletebtn.disabled = false;
                    if (response && response.deleted) {
                        var delrow = deletebtn.closest('tr');
                        if (delrow) { delrow.remove(); }
                        // WCAG 2.4.3: restore focus after removing the row.
                        var remaining = tbody
                            ? Array.from(tbody.querySelectorAll('tr[data-eventid]'))
                            : [];
                        if (remaining.length > 0) {
                            var target = remaining[Math.min(idx, remaining.length - 1)];
                            var focusBtn = target.querySelector('button');
                            if (focusBtn) { focusBtn.focus(); }
                        } else {
                            // No rows left: move focus to the section.
                            var heading = root.querySelector('[id*="videotrack-my-reactions"]');
                            if (heading) {
                                heading.setAttribute('tabindex', '-1');
                                heading.focus();
                            }
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
            cleanupPlaybackRateGuard();
        };
        state._reactionRootCleanup = cleanupReactionRootHandlers;
        window.addEventListener('pagehide', cleanupReactionRootHandlers, {once: true});
        window.addEventListener('beforeunload', cleanupReactionRootHandlers, {once: true});
    }





    function appendReactionRow(eventid, reaction, videotime) {
        var tbody = document.getElementById('videotrack-my-reactions');
        if (!tbody) {
            return;
        }
        // Remove the 'no reactions yet' placeholder row when the first reaction is added.
        var placeholder = tbody.querySelector('.videotrack-no-reactions-placeholder');
        if (placeholder) { placeholder.parentNode.removeChild(placeholder); }
        var start = Math.max(0, videotime - 30);
        var end   = videotime + 30;

        // Build via DOM APIs instead of innerHTML to avoid XSS.
        // nel caso in cui iconhtml contenesse markup non atteso.
        var tr = document.createElement('tr');
        tr.dataset.eventid = eventid;

        // Cella timestamp.
        var tdTime = document.createElement('td');
        tdTime.textContent = Utils.formatSeconds(videotime);
        tr.appendChild(tdTime);

        // Icon + label cell: iconhtml comes from the PHP server already rendered as HTML
        // sicuro; rimuoviamo comunque eventuali tag <script> prima di inserirlo.
        var tdReaction = document.createElement('td');
        var iconSpan = document.createElement('span');
        iconSpan.className = 'videotrack-report-icon';
        var iconDescriptor = {
            type: reaction.type || reaction.icontype || 'emoji',
            src: reaction.src || reaction.iconsrc || '',
            iconclass: reaction.iconclass || '',
            text: reaction.text || reaction.icontext || ''
        };
        Ui.appendIconSafe(iconSpan, iconDescriptor);
        if (reaction.label) {
            var labelSpan = document.createElement('span');
            labelSpan.className = 'videotrack-reaction-label';
            labelSpan.textContent = reaction.label;
            iconSpan.appendChild(document.createTextNode(' '));
            iconSpan.appendChild(labelSpan);
        }
        tdReaction.appendChild(iconSpan);
        tr.appendChild(tdReaction);

        // Cella descrizione.
        var tdDesc = document.createElement('td');
        tdDesc.textContent = reaction.description || '';
        tr.appendChild(tdDesc);

        // Cella replay.
        var tdReplay = document.createElement('td');
        var replayBtn = document.createElement('button');
        replayBtn.type = 'button';
        replayBtn.className = 'btn btn-secondary btn-sm videotrack-replay';
        replayBtn.dataset.start = start;
        replayBtn.dataset.end   = end;
        replayBtn.textContent = config.replaylabel;
        replayBtn.setAttribute('aria-label',
            (config.replaylabel) + ' — ' + Utils.formatSeconds(videotime));
        tdReplay.appendChild(replayBtn);
        tr.appendChild(tdReplay);

        // Cella elimina.
        var tdDel = document.createElement('td');
        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'btn btn-link btn-sm videotrack-delete-reaction';
        delBtn.dataset.eventid = eventid;
        delBtn.textContent = config.removelabel;
        // WCAG 2.4.6: contextual aria-label distinguishes identical buttons for screen readers.
        delBtn.setAttribute('aria-label',
            (config.removelabel) + ' — ' + (reaction.label || '') + ' — ' + Utils.formatSeconds(videotime));
        tdDel.appendChild(delBtn);
        tr.appendChild(tdDel);

        tbody.appendChild(tr);
    }

    function buildPlayer() {
        var initialStart = 0;
        if (typeof config.replaystart === 'number' && config.replaystart >= 0) {
            initialStart = Math.floor(config.replaystart);
        } else {
            initialStart = Math.floor(resolveResumePosition());
        }
        player = new YT.Player('mod-videotrack-player', {
            videoId: config.videoid,
            playerVars: {
                autoplay:       config.autoplay ? 1 : 0,
                mute:           (config.autoplay || config.startmuted) ? 1 : 0,
                loop:           config.loop ? 1 : 0,
                playlist:       config.loop ? config.videoid : undefined,
                start:          initialStart > 2 ? initialStart : undefined,
                controls:       config.showcontrols ? 1 : 0,
                disablekb:      config.disablekeyboard ? 1 : 0,
                fs:             config.showfullscreen ? 1 : 0,
                rel:            0,
                iv_load_policy: 3,
                playsinline:    1,
                enablejsapi:    1,
                origin:         config.origin,
                // Captions: cc_load_policy=1 shows captions by default.
                cc_load_policy: config.captions ? 1 : 0,
                cc_lang_pref:   config.captionslang || '',
            },
            events: {
                onReady: function() {
                    state.duration = Adapter.getDuration(state, function() {
                        return player.getDuration ? player.getDuration() : state.duration;
                    }, Log, 'YouTube ready');
                    state.playbackrate = enforceMaxPlaybackRate('YouTube ready playback rate');
                    installPlaybackRateGuard();
                    setReactionButtons(false); // Disabled until playback starts.
                    // Add rewind/ff overlay buttons if configured.
                    buildYouTubeSkipButtons();
                    // replaystart (direct link to a fragment) takes precedence over resume.
                    // If both are configured, respect the user's explicit navigation.
                    if (typeof config.replaystart === 'number' && config.replaystart >= 0) {
                        replayFragment(config.replaystart,
                            typeof config.replayend === 'number' ? config.replayend : null, true);
                    } else {
                        var resumePosition = resolveResumePosition();
                        if (resumePosition > 2) {
                            initialiseKnownProgress(resumePosition);
                            // The YouTube iframe receives the resume position through
                            // playerVars.start. Calling seekTo() again during onReady can
                            // race with the iframe initialisation and make the next visit
                            // start from 0 instead of the saved position.
                            showResumeNotice(resumePosition);
                        } else {
                            initialiseKnownProgress(0);
                        }
                    }
                },
                onStateChange: onPlayerStateChange,
                onPlaybackRateChange: function() {
                    enforceMaxPlaybackRate('YouTube playback-rate change');
                },
                onAutoplayBlocked: function() {
                    // Browser has blocked autoplay. Show a visible play button
                    // so the student can start manually without confusion.
                    var wrap = document.querySelector('.videotrack-player-wrap');
                    if (wrap && !wrap.querySelector('.videotrack-autoplay-notice')) {
                        var notice = document.createElement('div');
                        notice.className = 'videotrack-autoplay-notice alert alert-info mt-1';
                        notice.setAttribute('role', 'status');
                        notice.setAttribute('aria-live', 'polite');
                        notice.textContent = config.autoblockedlabel;
                        wrap.appendChild(notice);
                        // Remove notice once the user starts playing.
                        // YouTube IFrame API addEventListener uses 'on' prefix: 'onStateChange'.
                        var noticeTimeout = null;
                        var removeNotice = function() {
                            if (noticeTimeout) {
                                window.clearTimeout(noticeTimeout);
                                noticeTimeout = null;
                            }
                            if (notice && notice.parentNode) {
                                notice.parentNode.removeChild(notice);
                            }
                            try {
                                player.removeEventListener('onStateChange', removeNotice);
                            } catch (error) {
                                Debug.log('autoplaycleanupfailed', {message: error});
                            }
                        };
                        player.addEventListener('onStateChange', removeNotice);
                        noticeTimeout = window.setTimeout(removeNotice, 30000);
                    }
                },
                onError: function() { Debug.log('youtubeplayererror'); }
            }
        });
    }

    /**
     * Builds rewind and fast-forward overlay buttons for the YouTube player.
     *
     * Visibility rules (AND logic — both conditions must be true to show a button):
     *  - Rewind:       rewindstep > 0  AND  allowseekbackward === true
     *  - Fast-forward: fastforwardstep > 0  AND  allowseekforward === true
     *
     * This ensures that if the teacher has disabled forward seeking (to prevent
     * skipping ahead) the FF button never appears, even if a step is configured.
     * The same applies to rewind and allowseekbackward.
     */
    function buildYouTubeSkipButtons() {
        var showRewind = (config.rewindstep > 0) && (config.allowseekbackward !== false);
        var showFF     = (config.fastforwardstep > 0) && (config.allowseekforward !== false);
        if (!showRewind && !showFF) { return; }

        var playerNode = document.getElementById('mod-videotrack-player');
        if (!playerNode) { return; }
        var container = playerNode.closest('.videotrack-player-wrap');
        if (!container) { return; }

        var bar = document.createElement('div');
        bar.className = 'videotrack-skip-bar';

        if (showRewind) {
            var rwBtn = document.createElement('button');
            rwBtn.type = 'button';
            rwBtn.className = 'btn btn-sm btn-dark videotrack-skip-btn';
            var rwIcon = document.createElement('span');
            rwIcon.setAttribute('aria-hidden', 'true');
            rwIcon.textContent = '⏪ ';
            rwBtn.appendChild(rwIcon);
            rwBtn.appendChild(document.createTextNode(config.rewindstep + 's'));
            rwBtn.setAttribute('aria-label',
                (config.rewindlabel) + ' ' + config.rewindstep + ' ' + (config.secondslabel));
            rwBtn.addEventListener('click', function() {
                if (player && player.getCurrentTime) {
                    Adapter.seek(
                        Adapter.resolveSkipTarget(player.getCurrentTime(), -config.rewindstep, state.duration),
                        function(target) {
                            player.seekTo(target, true);
                        },
                        Log,
                        'YouTube rewind'
                    );
                }
            });
            bar.appendChild(rwBtn);
        }

        if (showFF) {
            var ffBtn = document.createElement('button');
            ffBtn.type = 'button';
            ffBtn.className = 'btn btn-sm btn-dark videotrack-skip-btn';
            ffBtn.appendChild(document.createTextNode(config.fastforwardstep + 's '));
            var ffIcon = document.createElement('span');
            ffIcon.setAttribute('aria-hidden', 'true');
            ffIcon.textContent = '⏩';
            ffBtn.appendChild(ffIcon);
            ffBtn.setAttribute('aria-label',
                (config.fastforwardlabel) + ' ' + config.fastforwardstep + ' ' + (config.secondslabel));
            ffBtn.addEventListener('click', function() {
                if (player && player.getCurrentTime && player.getDuration) {
                    Adapter.seek(
                        Adapter.resolveSkipTarget(player.getCurrentTime(), config.fastforwardstep, player.getDuration()),
                        function(target) {
                            player.seekTo(target, true);
                        },
                        Log,
                        'YouTube fast-forward'
                    );
                }
            });
            bar.appendChild(ffBtn);
        }

        if (container.parentNode) {
            container.parentNode.insertBefore(bar, container.nextSibling);
        } else {
            container.appendChild(bar);
        }
    }


    /** Returns the current video timestamp for the YouTube player. */
    function getCurrentVideoTime() {
        return Adapter.getCurrentTime(state, function() {
            return player && player.getCurrentTime ? player.getCurrentTime() : state.lasttime;
        }, Log, 'YouTube');
    }


    /**
     * Registers the reaction button click handler and replay button click handler.
     * In player.js (YouTube), reaction clicks are handled within installGlobalListeners
     * via event delegation on document. This function is a documented no-op that exists
     * to match the interface of vimeo_player.js and html5_player.js, where
     * installReactionHandler is a standalone function called from init().
     *
     * See the document.addEventListener('click', ...) block inside installGlobalListeners
     * for the actual YouTube reaction handling logic.
     */
    function installReactionHandler() {
        // Reactions for YouTube are handled via event delegation in installGlobalListeners.
        // This function intentionally left empty for API consistency across all player modules.
    }


    /**
     * Toggle the notes panel through the shared helper.
     */
    function installNotesToggle() {
        PlayerCore.installNotesToggle(config, Utils, 'YouTube notes panel state');
    }

    /**
     * Feature 11: student personal notes.
     * Handle saving and deleting timestamped text notes.
     * The "Save" button is active only during playback (aria-disabled).
     */
    function installNoteHandler() {
        PlayerCore.installNoteHandler({
            Api: Api,
            Log: Log,
            Utils: Utils,
            config: config,
            state: state,
            getCurrentVideoTime: getCurrentVideoTime,
            saveCurrentProgress: saveCurrentProgress
        });
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
            // Start playback if the player is ready.
            if (player && player.playVideo) {
                Adapter.play(function() {
                    return player.playVideo();
                }, Log, 'YouTube poster play');
            }
        };
        if (playBtn) {
            playBtn.addEventListener('click', posterClickHandler);
        }

        // Remove the poster on the first YouTube PLAYING state,
        // by listening to the custom event already emitted by setReactionButtons.
        // Non riassegniamo onPlayerStateChange (function declaration, non variabile).
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
    return {
        init: function(initConfig) {
            config = resolveConfig(initConfig);
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
            // Read the heartbeat interval from the administrator configuration.
            HEARTBEAT_INTERVAL = Tracker.normaliseHeartbeatInterval(config, 30);
            state.sessionid = uuid();
            // Draw the interval bar with data already saved from previous sessions.
            if (config.intervaljson && config.duration) {
                updateIntervalBar(config.intervaljson, config.duration);
            }
            installGlobalListeners();
            installReactionHandler();
            installNoteHandler();
            installNotesToggle();
            installPosterHandler();
            loadApi(buildPlayer);
        }
    };
});
