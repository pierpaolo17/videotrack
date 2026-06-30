/**
 * Vimeo player module for mod_videotrack.
 *
 * Wraps the Vimeo Player SDK (loaded from player.vimeo.com) and mirrors
 * the segment-tracking contract of the YouTube player module:
 *  - heartbeat every HEARTBEAT_INTERVAL wallclock seconds
 *  - segment saved on pause, seek, tab-change and page-hide
 *  - seek blocking when allowseekforward / allowseekbackward are off
 *
 * @module mod_videotrack/vimeo_player
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
    'mod_videotrack/core/debug'
], function(Log, Api, Adapter, Utils, Ui, Progress, State, Reactions, Tracker, PlayerCore, Debug) {
    'use strict';


    var player  = null;
    var config  = null;
    var reactionState = Reactions.createState();
    var HEARTBEAT_INTERVAL = 30;

    var state = State.create();
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


    function saveSegment(start, end, reason) {
        return Api.saveSegment(config, state, start, end, reason, {
            swallowFailures: true,
            errorMessage: 'vimeo-player-event',
            requestScope: state.ajaxRequestScope
        }).then(updateProgress);
    }

    function hasPlayer(methods, capability) {
        return Adapter.hasCapability(player, 'vimeo', capability, methods);
    }

    function saveCurrentProgress(reason) {
        return PlayerCore.saveCurrentProgress(state, getCurrentVideoTime, saveSegment, reason, hasPlayer(null, 'currentTime'));
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
        var allowed;
        try {
            stored = window.localStorage ? window.localStorage.getItem(getResumeStorageKey()) : null;
        } catch (e) {
            stored = null;
        }
        stored = Number(stored);
        if (!isFinite(stored) || stored <= 2) {
            return 0;
        }
        if (config && config.allowseekforward === false) {
            allowed = getMaxWatchedFromIntervals(config.intervaljson || state.intervaljson);
            if (allowed > 0 && stored > allowed + 0.75) {
                return allowed;
            }
        }
        return stored;
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

    function startVimeoRuntimePolling() {
        if (state._vimeoRuntimePollId) {
            return;
        }
        state._vimeoRuntimePollId = window.setInterval(pollVimeoRuntime, 1000);
        pollVimeoRuntime();
    }

    function stopVimeoRuntimePolling() {
        if (state._vimeoRuntimePollId) {
            window.clearInterval(state._vimeoRuntimePollId);
            state._vimeoRuntimePollId = null;
        }
        cleanupPlaybackRateGuard();
    }

    function readVimeoValue(method, fallback) {
        if (!player || typeof player[method] !== 'function') {
            return Promise.resolve(fallback);
        }
        return player[method]().catch(function(error) {
            Log.debug(error);
            return fallback;
        });
    }

    function pauseRuntimeSegment(current) {
        if (!state.playing) {
            return;
        }
        stopHeartbeat();
        rememberResumePosition(current);
        closeSegment('pause');
        setReactionButtons(false);
    }

    function pollVimeoRuntime() {
        if (!player || state.seekblocked) {
            return;
        }
        Promise.all([
            readVimeoValue('getCurrentTime', state.lasttime),
            readVimeoValue('getPaused', !state.playing),
            readVimeoValue('getPlaybackRate', state.playbackrate || 1),
            readVimeoValue('getDuration', state.duration || config.duration || 0)
        ]).then(function(values) {
            var current = Tracker.normaliseTime(values[0]);
            var paused = values[1] !== false;
            var rate = Number(values[2]) || state.playbackrate || 1;
            var duration = Number(values[3]) || state.duration || config.duration || 0;
            if (duration > 0) {
                state.duration = duration;
            }
            rate = enforcePlaybackRateValue(rate, 'Vimeo runtime playback rate');
            if (paused) {
                if (state._vimeoBlockedSeekInProgress || state._vimeoBlockedSeekResume) {
                    return;
                }
                handleVimeoTime(current, rate, duration);
                pauseRuntimeSegment(current);
                return;
            }
            if (!state.playing || state.segmentstart === null || typeof state.segmentstart === 'undefined') {
                startSegment(current);
                startHeartbeat();
                setReactionButtons(true);
            }
            handleVimeoTime(current, rate, duration);
            Tracker.runHeartbeat({
                state: state,
                heartbeatInterval: HEARTBEAT_INTERVAL,
                getCurrentTime: function() {
                    return current;
                },
                saveSegment: saveSegment,
                hasPlayer: function() {
                    return hasPlayer(['getCurrentTime']);
                },
                log: Log
            });
        }).catch(Log.debug);
    }

    function handleVimeoTime(current, playbackRate, duration) {
        var previous = Tracker.normaliseTime(state.lasttime);
        var now = Date.now();
        var elapsed = state.lastSeekPollAt ? Math.max(0, (now - state.lastSeekPollAt) / 1000) : 0;
        var rate = enforcePlaybackRateValue(playbackRate, 'Vimeo timeupdate playback rate');
        var expectedDelta;
        var threshold;
        if (duration) {
            state.duration = Number(duration) || state.duration;
        }
        state.lastSeekPollAt = now;
        if (state.seekblocked) {
            return;
        }
        expectedDelta = state.playing && elapsed > 0 ? elapsed * Math.max(rate, 1) : 0;
        threshold = state.playing ? Math.max(1.5, expectedDelta + 1.0) : 0.5;
        var allowedLimit = getAllowedForwardLimit();
        var blockedFallback = Tracker.normaliseTime(state._vimeoBlockedForwardSeekFallback);
        if (config.allowseekforward === false && state._vimeoBlockedForwardSeekUntil) {
            if (now <= state._vimeoBlockedForwardSeekUntil && current > blockedFallback + 0.75) {
                recoverBlockedSeek(blockedFallback, !!state.wasPlayingBeforeSeekBlock, 'Vimeo blocked forward seek resume');
                return;
            }
            if (now > state._vimeoBlockedForwardSeekUntil) {
                state._vimeoBlockedForwardSeekUntil = 0;
                state._vimeoBlockedForwardSeekFallback = 0;
            }
        }
        var looksLikePlayback = current > previous && current <= previous + threshold &&
                (isNormalForwardPlayback(current, rate) || isForwardSeekRecoveryPlayback(current, previous, threshold, now));
        if (config.allowseekforward === false && current > previous + 0.75 && !looksLikePlayback &&
                blockForwardSeek(current, previous)) {
            return;
        }
        if (config.allowseekforward === false && current > allowedLimit + 0.75 && looksLikePlayback &&
                current > previous + threshold) {
            blockForwardSeek(current, allowedLimit);
            return;
        }
        if (Math.abs(current - previous) > threshold) {
            if (config.allowseekforward === false && current > previous &&
                    blockForwardSeek(current, previous)) {
                return;
            }
            if (config.allowseekbackward === false && current < previous) {
                recoverBlockedSeek(previous, !!state.playing, 'Vimeo blocked backward seek resume');
                return;
            }
            if (state.playing) {
                saveSegment(state.segmentstart, previous, 'seek');
                startSegment(current);
            }
        }
        Tracker.syncTime(state, current, rate);
        rememberResumePosition(current);
        if (current >= previous && (!config || config.allowseekforward !== false || current <= getAllowedForwardLimit() + 0.75)) {
            markAllowedForwardTime(current);
        }
        updateLiveIntervalBar(current);
        if (Tracker.shouldStopReplay(state, current)) {
            Adapter.pause(function() {
                return player.pause();
            }, Log, 'Vimeo replay pause');
        }
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

    function scheduleBlockedSeekResume(wasPlaying, label) {
        if (!wasPlaying || !player || typeof player.play !== 'function') {
            return;
        }
        if (state._vimeoBlockedSeekResumeTimer) {
            window.clearTimeout(state._vimeoBlockedSeekResumeTimer);
            state._vimeoBlockedSeekResumeTimer = null;
        }
        state._vimeoBlockedSeekResume = {
            attempts: 0,
            label: label || 'Vimeo blocked seek resume',
            until: Date.now() + 6000
        };

        function retry() {
            var request = state._vimeoBlockedSeekResume;
            if (!request || Date.now() > request.until || request.attempts >= 8) {
                state._vimeoBlockedSeekResume = null;
                state._vimeoBlockedSeekResumeTimer = null;
                state.wasPlayingBeforeSeekBlock = false;
                return;
            }
            request.attempts++;
            player.play().then(function() {
                state._vimeoBlockedSeekResumeTimer = null;
            }).catch(function(error) {
                Log.debug(request.label + ': ' + error);
                state._vimeoBlockedSeekResumeTimer = window.setTimeout(retry, 650);
            });
        }

        state._vimeoBlockedSeekResumeTimer = window.setTimeout(retry, 900);
    }

    function recoverBlockedSeek(fallback, wasPlaying, label) {
        var completed = false;
        var timeoutid = null;
        fallback = Math.max(0, Tracker.normaliseTime(fallback));
        if (state._vimeoBlockedSeekInProgress) {
            return Promise.resolve();
        }
        state._vimeoBlockedSeekInProgress = true;
        Tracker.markProgrammaticSeek(state);
        Tracker.blockSeek(state, 900);

        function finish(error) {
            if (completed) {
                return;
            }
            completed = true;
            if (timeoutid) {
                window.clearTimeout(timeoutid);
                timeoutid = null;
            }
            if (error) {
                Log.debug(error);
            }
            Tracker.consumeProgrammaticSeek(state, fallback);
            resetForwardSeekRecovery(fallback);
            state._vimeoBlockedForwardSeekUntil = 0;
            state._vimeoBlockedForwardSeekFallback = 0;
            Tracker.clearSeekBlock(state);
            state._vimeoBlockedSeekInProgress = false;
            scheduleBlockedSeekResume(wasPlaying, label);
        }

        timeoutid = window.setTimeout(function() {
            finish('Vimeo blocked seek rollback timed out');
        }, 1000);

        player.setCurrentTime(fallback).then(function() {
            finish();
        }).catch(finish);
        return Promise.resolve();
    }

    function blockForwardSeek(target, fallbackTime) {
        var fallback = typeof fallbackTime === 'number' ? fallbackTime : Tracker.normaliseTime(state.lasttime);
        var wasPlaying = !!state.playing || !!state.wasPlayingBeforeSeekBlock;
        fallback = Math.max(0, Tracker.normaliseTime(fallback));
        state.wasPlayingBeforeSeekBlock = wasPlaying;
        if (target <= fallback + 0.75) {
            return false;
        }
        state._vimeoBlockedForwardSeekUntil = Date.now() + 5000;
        state._vimeoBlockedForwardSeekFallback = fallback;
        recoverBlockedSeek(fallback, wasPlaying, 'Vimeo blocked forward seek resume');
        return true;
    }


    function getConfiguredMaxPlaybackRate() {
        var configured = Number(config && config.maxplaybackrate ? config.maxplaybackrate : 0) / 100;
        if (isFinite(configured) && configured > 0) {
            return configured;
        }
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
        state.playbackrate = safeRate;
        if (player && typeof player.setPlaybackRate === 'function') {
            return player.setPlaybackRate(safeRate).catch(function(error) {
                Log.debug((label || 'Vimeo playback rate limit') + ': ' + error);
            });
        }
        return null;
    }

    function retryPlaybackRateLimit(maxRate, resetRate, label) {
        [50, 150, 300, 600, 1000].forEach(function(delay) {
            window.setTimeout(function() {
                if (!player || typeof player.getPlaybackRate !== 'function') {
                    return;
                }
                player.getPlaybackRate().then(function(rate) {
                    if (Number(rate) > maxRate) {
                        writePlaybackRate(resetRate, (label || 'Vimeo playback rate limit') + ' retry');
                    }
                }).catch(Log.debug);
            }, delay);
        });
    }

    function enforcePlaybackRateValue(currentRate, label) {
        var maxRate = getConfiguredMaxPlaybackRate();
        var rate = Number(currentRate);
        var resetRate;
        if (!isFinite(rate) || rate <= 0) {
            rate = state.playbackrate || 1;
        }
        if (maxRate > 0 && rate > maxRate) {
            resetRate = getPlaybackRatePenalty();
            writePlaybackRate(resetRate, label || 'Vimeo playback rate limit');
            retryPlaybackRateLimit(maxRate, resetRate, label || 'Vimeo playback rate limit');
            return resetRate;
        }
        state.playbackrate = rate;
        return rate;
    }

    function enforceMaxPlaybackRate(label) {
        if (!player || typeof player.getPlaybackRate !== 'function') {
            return Promise.resolve(state.playbackrate || 1);
        }
        return player.getPlaybackRate().then(function(rate) {
            return enforcePlaybackRateValue(rate, label || 'Vimeo playback rate guard');
        }).catch(function(error) {
            Log.debug(error);
            return state.playbackrate || 1;
        });
    }

    function installPlaybackRateGuard() {
        if (state._playbackRateGuard || getConfiguredMaxPlaybackRate() <= 0) {
            return;
        }
        state._playbackRateGuard = window.setInterval(function() {
            enforceMaxPlaybackRate('Vimeo playback-rate guard');
        }, 250);
    }

    function cleanupPlaybackRateGuard() {
        if (state._playbackRateGuard) {
            window.clearInterval(state._playbackRateGuard);
            state._playbackRateGuard = null;
        }
    }

    // Segment lifecycle.

    function startSegment(currentTime) {
        Tracker.openSegment(state, currentTime, Math.floor(Date.now() / 1000));
        state.lastSeekPollAt = Date.now();
        markAllowedForwardTime(currentTime);
        updateLiveIntervalBar(currentTime);
    }

    function closeSegment(reason) {
        return Tracker.closeAndSaveSegment(state, function() {
            return player ? player.getCurrentTime() : state.lasttime;
        }, saveSegment, reason, hasPlayer(null, 'currentTime')).catch(Log.debug);
    }

    // ── Heartbeat ─────────────────────────────────────────────────────────

    function startHeartbeat() {
        Tracker.startPolling(state, function() {
            Tracker.runHeartbeat({
                state: state,
                heartbeatInterval: HEARTBEAT_INTERVAL,
                getCurrentTime: function() {
                    return player.getCurrentTime();
                },
                saveSegment: saveSegment,
                hasPlayer: function() {
                    return hasPlayer(['getCurrentTime']);
                },
                log: Log
            });
        }, HEARTBEAT_INTERVAL);
    }

    function stopHeartbeat() {
        Tracker.stopPolling(state);
    }

    // ── Global listeners ──────────────────────────────────────────────────

    /**
     * Shows a temporary banner informing the student about automatic resume.
     * @param {number} seconds Resume position in seconds.
     */
    function showResumeNotice(seconds) {
        PlayerCore.showResumeNotice(seconds, config, Utils);
    }

    function installGlobalListeners() {
        Tracker.installLifecycleHandlers({
            state: state,
            closeSegment: closeSegment,
            stopPolling: stopHeartbeat,
            onHidden: function() {
                setReactionButtons(false);
            },
            hasPlayer: function() { return hasPlayer(['getCurrentTime']); },
            sendBeacon: function() {
                return Tracker.sendUnloadBeacon({
                    state: state,
                    hasPlayer: function() { return hasPlayer(['getCurrentTime']); },
                    sendSegment: function(start, end) {
                        return PlayerCore.sendBeaconSegment(config, state, start, end, Utils, Log);
                    }
                });
            }
        });
    }

    // ── Vimeo SDK ─────────────────────────────────────────────────────────

    function loadVimeoSDK(callback) {
        if (window.Vimeo && window.Vimeo.Player) {
            callback();
            return;
        }
        var script = document.createElement('script');
        var amdDefine = window.define;
        var restoreDefine = function() {
            if (amdDefine && window.define !== amdDefine) {
                window.define = amdDefine;
            }
        };
        script.src = 'https://player.vimeo.com/api/player.js';
        script.async = true;
        // Vimeo's SDK is a UMD script. In Moodle, RequireJS can interpret its
        // anonymous AMD define() call as a Moodle module and stop initialisation
        // with a "Mismatched anonymous define()" error. Load it as a plain
        // browser global so window.Vimeo.Player is available to this module.
        if (amdDefine && amdDefine.amd) {
            window.define = undefined;
        }
        // crossorigin='anonymous' prevents credential leakage.
        // Note: Vimeo does not publish stable SRI hashes as the SDK is updated
        // dynamically; if your CSP blocks external scripts, add
        // 'player.vimeo.com' to the script-src directive.
        script.onload = function() {
            restoreDefine();
            if (window.Vimeo && window.Vimeo.Player) {
                callback();
                return;
            }
            Debug.log('vimeosdkmissingafterload');
        };
        script.onerror = function() {
            restoreDefine();
            Debug.log('vimeosdkfailed');
            // Show a readable user message: likely CSP or network blocking.
            var wrap = document.getElementById('mod-videotrack-player');
            if (wrap) {
                var notice = document.createElement('div');
                notice.className = 'alert alert-warning mt-2';
                notice.setAttribute('role', 'alert');
                notice.textContent = config.sdkerrorlabel;
                wrap.parentNode.insertBefore(notice, wrap.nextSibling);
            }
        };
        document.head.appendChild(script);
    }

    /**
     * Extracts a Vimeo id and optional privacy hash from plugin configuration.
     *
     * @returns {{id: string, hash: string}} Normalised Vimeo source data.
     */
    function resolveVimeoSource() {
        var result = {id: '', hash: ''};
        var rawurl = (config.videourl || '').toString().trim();
        var rawid = (config.videoid || '').toString().trim();
        var source = rawurl || rawid;
        var match = source.match(/(?:vimeo\.com\/(?:video\/)?)(\d+)(?:[/?#]|$)/) || source.match(/^(\d+)$/);
        if (match) {
            result.id = match[1];
        }
        try {
            if (rawurl && window.URL) {
                var parsed = new URL(rawurl, window.location.href);
                result.hash = parsed.searchParams.get('h') || '';
                if (!result.hash && result.id) {
                    var parts = parsed.pathname.split('/').filter(Boolean);
                    var idindex = parts.indexOf(result.id);
                    if (idindex >= 0 && parts[idindex + 1]) {
                        result.hash = parts[idindex + 1];
                    }
                }
            }
        } catch (e) {
            var hashmatch = rawurl.match(/[?&]h=([a-zA-Z0-9]+)/);
            result.hash = hashmatch ? hashmatch[1] : '';
        }
        return result;
    }

    /**
     * Builds an explicit Vimeo iframe, avoiding SDK URL parsing differences.
     *
     * @param {HTMLElement} container Player container.
     * @param {{id: string, hash: string}} source Vimeo source data.
     * @returns {HTMLIFrameElement|null} Created iframe, or null when no id exists.
     */
    function buildVimeoIframe(container, source) {
        if (!source.id) {
            return null;
        }
        var params = [];
        if (source.hash) {
            params.push('h=' + encodeURIComponent(source.hash));
        }
        params.push('api=1');
        params.push('dnt=1');
        params.push('autoplay=' + (config.autoplay ? '1' : '0'));
        params.push('loop=' + (config.loop ? '1' : '0'));
        params.push('muted=' + ((config.autoplay || config.startmuted) ? '1' : '0'));
        params.push('controls=' + (config.showcontrols === false ? '0' : '1'));
        params.push('playsinline=1');
        var iframe = document.createElement('iframe');
        iframe.src = 'https://player.vimeo.com/video/' + encodeURIComponent(source.id) + '?' + params.join('&');
        iframe.setAttribute('allow', 'autoplay; fullscreen; picture-in-picture');
        iframe.setAttribute('allowfullscreen', 'allowfullscreen');
        iframe.setAttribute('title', config.title || 'Vimeo video');
        iframe.setAttribute('frameborder', '0');
        iframe.className = 'videotrack-vimeo-iframe';
        container.textContent = '';
        container.appendChild(iframe);
        return iframe;
    }

    function buildPlayer() {
        var container = document.getElementById('mod-videotrack-player');
        if (!container) { return; }

        var vimeosource = resolveVimeoSource();
        var iframe = buildVimeoIframe(container, vimeosource);
        if (iframe) {
            player = new window.Vimeo.Player(iframe);
        } else {
            player = new window.Vimeo.Player(container, {
                responsive:  true,
                controls:    config.showcontrols !== false,
                autoplay:    !!config.autoplay,
                loop:        !!config.loop,
                muted:       !!(config.autoplay || config.startmuted),
                fullscreen:  config.showfullscreen !== false,
                speed:       true,
                playsinline: true,
                dnt:         true
            });
        }

        // Set allowed playback speeds if the Vimeo player supports it.
        if (config.playbackspeeds && config.playbackspeeds.length) {
            // Vimeo does not have a "restrict speeds" API, but we can
            // set the initial speed to the closest available value.
            var defaultspeed = config.playbackspeeds.indexOf(1) >= 0 ? 1 :
                config.playbackspeeds[Math.floor(config.playbackspeeds.length / 2)];
            player.setPlaybackRate(defaultspeed).catch(Log.debug);
        }

        player.ready().then(function() {
            startVimeoRuntimePolling();
            return player.getDuration();
        }).then(function(d) {
            state.duration = Adapter.getDuration(state, function() {
                return d;
            }, Log, 'Vimeo duration');
            state.intervaljson = config.intervaljson || state.intervaljson || '[]';
            if (config.intervaljson && state.duration) {
                PlayerCore.updateIntervalBar(config.intervaljson, state.duration, Log);
            }
            var resumePosition = resolveResumePosition();
            initialiseKnownProgress(resumePosition || 0);
            // Automatically resume from the last saved position (lastposition > 2s).
            if (resumePosition > 2) {
                Tracker.markProgrammaticSeek(state);
                markAllowedForwardTime(resumePosition);
                player.setCurrentTime(resumePosition).then(function() {
                    Tracker.consumeProgrammaticSeek(state, resumePosition);
                    showResumeNotice(resumePosition);
                }).catch(function() {
                    // iOS Safari may silently fail on setCurrentTime before play.
                    // The seek will be retried on the first 'play' event.
                    state.isProgrammaticSeek = false;
                    state._pendingResume = resumePosition;
                });
            }
            // Enforce maxplaybackrate when the media loads.
            installPlaybackRateGuard();
            enforceMaxPlaybackRate('Vimeo loaded playback rate');
        });

        // Captions: activate the pre-loaded Vimeo track matching the language code.
        if (config.captions && config.captionslang) {
            player.enableTextTrack(config.captionslang).catch(function() {
                // Silently ignore: track may not exist on this video.
            });
        }

        // Rewind / fast-forward overlay buttons.
        buildVimeoSkipButtons();

        player.on('play', function() {
            state.ended = false;
            if (state._vimeoBlockedSeekResumeTimer) {
                window.clearTimeout(state._vimeoBlockedSeekResumeTimer);
                state._vimeoBlockedSeekResumeTimer = null;
            }
            state._vimeoBlockedSeekResume = null;
            state.wasPlayingBeforeSeekBlock = false;
            player.getCurrentTime().then(function(t) {
                // Retry an explicit replay seek if Vimeo deferred setCurrentTime until playback.
                if (state._pendingReplayStart !== null && typeof state._pendingReplayStart !== 'undefined') {
                    var replayStart = state._pendingReplayStart;
                    state._pendingReplayStart = null;
                    Tracker.markProgrammaticSeek(state);
                    markAllowedForwardTime(replayStart);
                    player.setCurrentTime(replayStart).then(function() {
                        Tracker.syncTime(state, replayStart, state.playbackrate || 1);
                        Tracker.consumeProgrammaticSeek(state, replayStart);
                        state.isProgrammaticSeek = false;
                        startSegment(replayStart);
                        startHeartbeat();
                        startVimeoRuntimePolling();
                        setReactionButtons(true);
                    }).catch(function() {
                        state.isProgrammaticSeek = false;
                    });
                    return;
                }
                // iOS Safari workaround: setCurrentTime may fail before play.
                // Retry the seek on first playback when it was pending.
                if (state._pendingResume && state._pendingResume > 2) {
                    var resumePos = state._pendingResume;
                    state._pendingResume = null;
                    Tracker.markProgrammaticSeek(state);
                    markAllowedForwardTime(resumePos);
                    player.setCurrentTime(resumePos).then(function() {
                        Tracker.consumeProgrammaticSeek(state, resumePos);
                        showResumeNotice(resumePos);
                    }).catch(function() {
                        state.isProgrammaticSeek = false;
                    });
                    return;
                }
                startSegment(t);
                startHeartbeat();
                startVimeoRuntimePolling();
                setReactionButtons(true);
                // Enforce max rate on every play event because the student may have changed it.
                installPlaybackRateGuard();
                enforceMaxPlaybackRate('Vimeo play playback rate');
            });
        });

        player.on('pause', function() {
            if (state.ended || state.seekblocked || state.isProgrammaticSeek || state._vimeoBlockedSeekResume) {
                return;
            }
            stopHeartbeat();
            rememberResumePosition(state.lasttime);
            closeSegment('pause');
            setReactionButtons(false);
        });

        player.on('ended', function() {
            state.ended = true;
            reactionState.readyAnnounced = false;
            stopHeartbeat();
            rememberResumePosition(state.lasttime);
            closeSegment('ended');
            setReactionButtons(false); // Disable buttons at the end of the video.
        });

        player.on('seeked', function(data) {
            // Ignore programmatic seeks (replay, resume): they must not trigger
            // the anti-skip block or close the current segment.
            if (Tracker.consumeProgrammaticSeek(state, data.seconds)) { return; }
            if (state.seekblocked) { return; }
            if (config.allowseekforward === false && data.seconds > Tracker.normaliseTime(state.lasttime) &&
                    blockForwardSeek(data.seconds, Tracker.normaliseTime(state.lasttime))) {
                return;
            }
            var seek = Tracker.resolveSeek(state, data.seconds, config, 0);

            if (seek.blocked) {
                recoverBlockedSeek(seek.fallbackTime, !!state.playing, 'Vimeo blocked seek resume');
                return;
            }
            if (state.playing && seek.changed) {
                // Valid seek: close current segment, open new one.
                saveSegment(state.segmentstart, seek.oldTime, 'seek');
                startSegment(seek.newTime);
            }
        });

        player.on('playbackratechange', function(data) {
            var rate = data && typeof data === 'object' ? data.playbackRate || data.playbackrate : data;
            enforcePlaybackRateValue(rate, 'Vimeo playback-rate change');
        });

        player.on('timeupdate', function(data) {
            handleVimeoTime(Tracker.normaliseTime(data.seconds), data.playbackRate, data.duration);
        });

        window.addEventListener('pagehide', stopVimeoRuntimePolling, {once: true});
        window.addEventListener('beforeunload', stopVimeoRuntimePolling, {once: true});

        var root = PlayerCore.getPlayerShell(Log);
        if (!root) { return; }

        // Replay buttons.
        root.addEventListener('click', function(e) {
            var btn = e.target.closest('.videotrack-replay');
            if (btn && player) {
                var start = parseFloat(btn.dataset.start) || 0;
                var end   = parseFloat(btn.dataset.end)   || 0;
                state.currentReplayEnd = end > 0 ? end : null;
                // Explicit replay must win over automatic resume. Otherwise Vimeo
                // may retry the resume position on the following play event and
                // jump back to the last watched second instead of the reaction.
                state._pendingResume = null;
                try {
                    if (window.localStorage) {
                        window.localStorage.removeItem(getResumeStorageKey());
                    }
                } catch (storageError) {
                    // Browser storage may be disabled; ignore.
                }
                // Mark the seek as programmatic to avoid triggering the anti-skip block.
                Tracker.markProgrammaticSeek(state);
                state.isProgrammaticSeek = true;
                state._pendingReplayStart = start;
                markAllowedForwardTime(start);
                player.setCurrentTime(start).then(function() {
                    Tracker.syncTime(state, start, state.playbackrate || 1);
                    Tracker.consumeProgrammaticSeek(state, start);
                    state.isProgrammaticSeek = false;
                    startSegment(start);
                    startHeartbeat();
                    startVimeoRuntimePolling();
                    setReactionButtons(true);
                    player.play();
                }).catch(function() {
                    state.isProgrammaticSeek = false;
                    state._pendingReplayStart = null;
                });
            }
        });
    }

    /**
     * Builds rewind and fast-forward overlay buttons for the Vimeo player.
     *
     * Visibility rules (AND logic):
     *  - Rewind:       rewindstep > 0  AND  allowseekbackward !== false
     *  - Fast-forward: fastforwardstep > 0  AND  allowseekforward !== false
     */
    function buildVimeoSkipButtons() {
        var showRewind = (config.rewindstep > 0) && (config.allowseekbackward !== false);
        var showFF     = (config.fastforwardstep > 0) && (config.allowseekforward !== false);
        if (!showRewind && !showFF) { return; }

        var container = document.getElementById('mod-videotrack-player');
        if (!container) { return; }
        var wrap = container.closest('.videotrack-player-wrap') || container.parentElement;

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
                (config.rewindlabel) + ' ' + config.rewindstep +
                ' ' + (config.secondslabel));
            rwBtn.addEventListener('click', function() {
                player.getCurrentTime().then(function(t) {
                    Adapter.seek(
                        Adapter.resolveSkipTarget(t, -config.rewindstep, state.duration),
                        function(target) {
                            return player.setCurrentTime(target);
                        },
                        Log,
                        'Vimeo rewind'
                    );
                });
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
                (config.fastforwardlabel) + ' ' + config.fastforwardstep +
                ' ' + (config.secondslabel));
            ffBtn.addEventListener('click', function() {
                player.getCurrentTime().then(function(t) {
                    Adapter.seek(
                        Adapter.resolveSkipTarget(t, config.fastforwardstep, state.duration),
                        function(target) {
                            return player.setCurrentTime(target);
                        },
                        Log,
                        'Vimeo fast-forward'
                    );
                });
            });
            bar.appendChild(ffBtn);
        }

        if (wrap.parentNode) {
            wrap.parentNode.insertBefore(bar, wrap.nextSibling);
        } else {
            wrap.appendChild(bar);
        }
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
            // Timestamp cell, formatted as MM:SS for readability.
            var tdtime = document.createElement('td');
            tdtime.textContent = Utils.formatSeconds(videotime);
            tr.appendChild(tdtime);
            // Icon cell
            var tdicon = document.createElement('td');
            var span = document.createElement('span');
            span.className = 'videotrack-report-icon';
            Ui.appendIconSafe(span, reaction);
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
            tdreplay.appendChild(replaybtn);
            tr.appendChild(tdreplay);
            // Delete
            var tddel = document.createElement('td');
            var delbtn = document.createElement('button');
            delbtn.type = 'button';
            delbtn.className = 'btn btn-link btn-sm videotrack-delete-reaction';
            delbtn.textContent = config.removelabel;
            delbtn.setAttribute('data-eventid', eventid);
            // Descriptive aria-label for screen readers: contextualises the action.
            delbtn.setAttribute('aria-label',
                (config.removelabel) + ' — ' + (reaction.label || '') + ' — ' + Utils.formatSeconds(videotime));
            tddel.appendChild(delbtn);
            tr.appendChild(tddel);
            tbody.appendChild(tr);
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
                reactionbtn.classList.add('videotrack-saving');
                reactionbtn.setAttribute('aria-busy', 'true');
                reactionbtn.disabled = true;
                saveCurrentProgress('reaction').then(function(progressResponse) {
                    return Promise.resolve(getCurrentVideoTime()).then(function(time) {
                        currentTime = resolveReactionTime(progressResponse, time);
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
                    if (response && response.reactioneventid) {
                        try {
                            appendReactionRow(response.reactioneventid, {
                                label: reactionbtn.getAttribute('data-reactionlabel') || '',
                                description: reactionbtn.getAttribute('data-reactiondesc') || '',
                                icontype: reactionbtn.getAttribute('data-reactionicontype') || 'emoji',
                                iconclass: reactionbtn.getAttribute('data-reactioniconclass') || '',
                                iconsrc: reactionbtn.getAttribute('data-reactioniconsrc') || '',
                                icontext: reactionbtn.getAttribute('data-reactionicontext') || '',
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
                    PlayerCore.showErrorStatusMessage(err, config.reactionerrorlabel, config.dismisslabel);
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






    /** Returns the current video timestamp for the Vimeo player, using lasttime as sync fallback. */
    function getCurrentVideoTime() {
        if (player && typeof player.getCurrentTime === 'function') {
            return player.getCurrentTime().then(function(seconds) {
                return Tracker.syncTime(state, seconds);
            }).catch(function(error) {
                Log.debug(error);
                return Tracker.normaliseTime(state.lasttime);
            });
        }
        return Tracker.normaliseTime(state.lasttime);
    }


    /**
     * Toggle the notes panel through the shared helper.
     */
    function installNotesToggle() {
        PlayerCore.installNotesToggle(config, Utils, 'Vimeo notes panel state');
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
            // Start playback with the Vimeo SDK API (not player.playVideo, which is YouTube).
            if (player && player.play) {
                var posterPlay = Adapter.play(function() {
                    return player.play();
                }, Log, 'Vimeo poster play');
                if (posterPlay && typeof posterPlay.catch === 'function') {
                    posterPlay.catch(function(err) {
                        Debug.log('playrequestfailed', {message: err});
                    });
                }
            }
        };
        if (playBtn) {
            playBtn.addEventListener('click', posterClickHandler);
        }

        // Remove the poster on the first Vimeo play event.
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
            if (config.intervaljson && config.duration) {
                state.duration = Number(config.duration) || state.duration;
                PlayerCore.updateIntervalBar(config.intervaljson, state.duration, Log);
            }
            installGlobalListeners();
            installReactionHandler();
            installNoteHandler();
            installNotesToggle();
            installPosterHandler();
            loadVimeoSDK(buildPlayer);
        }
    };
});
