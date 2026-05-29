/**
 * Shared tracking helpers for mod_videotrack AMD player modules.
 *
 * The concrete YouTube, HTML5 and Vimeo modules still own their player API
 * calls. This module centralises provider-neutral segment lifecycle and
 * heartbeat decisions so the 1.3 branch can move toward a single tracker
 * engine without changing the public AMD entrypoints in one large step.
 *
 * @module mod_videotrack/core/tracker
 */
define([
    'mod_videotrack/core/segment',
    'mod_videotrack/core/events'
], function(Segment, Events) {
    'use strict';




    /**
     * Convert an arbitrary media time to a safe non-negative number.
     *
     * @param {*} value Candidate media time.
     * @returns {number} Safe media time in seconds.
     */
    function normaliseTime(value) {
        var time = Number(value);
        if (!isFinite(time) || time < 0) {
            return 0;
        }
        return time;
    }

    /**
     * Register a tracker event handler bound to a player state.
     *
     * @param {Object} state Mutable player state.
     * @param {string} name Event name.
     * @param {Function} handler Event handler.
     * @returns {Function} Unsubscribe callback.
     */
    function on(state, name, handler) {
        return Events.ensure(state).on(name, handler);
    }

    /**
     * Register a tracker event handler that runs at most once.
     *
     * @param {Object} state Mutable player state.
     * @param {string} name Event name.
     * @param {Function} handler Event handler.
     * @returns {Function} Unsubscribe callback.
     */
    function once(state, name, handler) {
        return Events.ensure(state).once(name, handler);
    }

    /**
     * Remove a tracker event handler from a player state.
     *
     * @param {Object} state Mutable player state.
     * @param {string} name Event name.
     * @param {Function} handler Event handler.
     */
    function off(state, name, handler) {
        if (state && state.events && typeof state.events.off === 'function') {
            state.events.off(name, handler);
        }
    }

    /**
     * Count tracker event handlers bound to a player state.
     *
     * @param {Object} state Mutable player state.
     * @param {string=} name Optional event name.
     * @returns {number} Registered handler count.
     */
    function countEvents(state, name) {
        if (!state || !state.events || typeof state.events.count !== 'function') {
            return 0;
        }
        return state.events.count(name);
    }

    /**
     * Remove tracker event handlers bound to a player state.
     *
     * @param {Object} state Mutable player state.
     */
    function clearEvents(state) {
        if (state && state.events && typeof state.events.clear === 'function') {
            state.events.clear();
        }
    }

    /**
     * Emit a tracker event when a state-bound event bus exists.
     *
     * @param {Object} state Mutable player state.
     * @param {string} name Event name.
     * @param {Object=} payload Event payload.
     */
    function emit(state, name, payload) {
        Events.emit(state, name, payload);
    }


    /**
     * Tracker state constants used by the 1.3 state machine.
     *
     * Concrete players may still expose their own SDK states. This state
     * machine keeps the provider-neutral lifecycle explicit in the tracker
     * layer so future phases can reason about transitions without reading
     * YouTube, HTML5 and Vimeo event details directly.
     *
     * @type {Object<string, string>}
     */
    var STATES = {
        IDLE: 'idle',
        PLAYING: 'playing',
        PAUSED: 'paused',
        SEEKING: 'seeking',
        ENDED: 'ended',
        DESTROYED: 'destroyed'
    };

    /**
     * Allowed provider-neutral state transitions.
     *
     * @type {Object<string, Array<string>>}
     */
    var TRANSITIONS = {};
    TRANSITIONS[STATES.IDLE] = [STATES.PLAYING, STATES.PAUSED, STATES.SEEKING, STATES.ENDED, STATES.DESTROYED];
    TRANSITIONS[STATES.PLAYING] = [STATES.PAUSED, STATES.SEEKING, STATES.ENDED, STATES.DESTROYED];
    TRANSITIONS[STATES.PAUSED] = [STATES.PLAYING, STATES.SEEKING, STATES.ENDED, STATES.DESTROYED];
    TRANSITIONS[STATES.SEEKING] = [STATES.PLAYING, STATES.PAUSED, STATES.ENDED, STATES.DESTROYED];
    TRANSITIONS[STATES.ENDED] = [STATES.IDLE, STATES.PLAYING, STATES.DESTROYED];
    TRANSITIONS[STATES.DESTROYED] = [];

    /**
     * Normalise a candidate tracker state.
     *
     * @param {*} value Candidate state.
     * @returns {string} Known tracker state.
     */
    function normaliseTrackerState(value) {
        var stateName = String(value || '').toLowerCase();
        return Object.keys(STATES).some(function(key) {
            return STATES[key] === stateName;
        }) ? stateName : STATES.IDLE;
    }

    /**
     * Read the provider-neutral tracker state.
     *
     * @param {Object} state Mutable player state.
     * @returns {string} Current tracker state.
     */
    function getTrackerState(state) {
        if (!state) {
            return STATES.IDLE;
        }
        return normaliseTrackerState(state.trackerstate);
    }

    /**
     * Read the current transition token for async race guards.
     *
     * @param {Object} state Mutable player state.
     * @returns {number} Monotonic transition token.
     */
    function getTransitionToken(state) {
        return state && typeof state._transitionSerial === 'number' ? state._transitionSerial : 0;
    }

    /**
     * Check whether an async continuation still belongs to the active state.
     *
     * @param {Object} state Mutable player state.
     * @param {number} token Token captured before async work started.
     * @returns {boolean} True when no newer transition happened.
     */
    function isTransitionCurrent(state, token) {
        return !!state && getTransitionToken(state) === token;
    }

    /**
     * Check whether a transition is allowed.
     *
     * @param {string} from Current state.
     * @param {string} to Next state.
     * @returns {boolean} True when transition is valid.
     */
    function canTransition(from, to) {
        var current = normaliseTrackerState(from);
        var next = normaliseTrackerState(to);
        if (current === next) {
            return true;
        }
        return (TRANSITIONS[current] || []).indexOf(next) !== -1;
    }

    /**
     * Synchronise legacy boolean flags with the provider-neutral tracker state.
     *
     * @param {Object} state Mutable player state.
     * @param {string} trackerState Normalised tracker state.
     */
    function applyTrackerStateFlags(state, trackerState) {
        if (!state) {
            return;
        }
        if (trackerState === STATES.PLAYING) {
            state.playing = true;
            state.ended = false;
            state.isSeeking = false;
        } else if (trackerState === STATES.PAUSED || trackerState === STATES.IDLE) {
            state.playing = false;
            state.ended = false;
            state.isSeeking = false;
        } else if (trackerState === STATES.SEEKING) {
            state.isSeeking = true;
            state.ended = false;
        } else if (trackerState === STATES.ENDED) {
            state.playing = false;
            state.ended = true;
            state.isSeeking = false;
        } else if (trackerState === STATES.DESTROYED) {
            state.playing = false;
            state.isSeeking = false;
        }
    }

    /**
     * Apply a provider-neutral tracker state transition.
     *
     * @param {Object} state Mutable player state.
     * @param {string} nextState Target state.
     * @param {Object=} meta Optional transition metadata.
     * @returns {boolean} True when state was changed or already matched.
     */
    function setTrackerState(state, nextState, meta) {
        if (!state) {
            return false;
        }
        var previous = getTrackerState(state);
        var next = normaliseTrackerState(nextState);
        if (!canTransition(previous, next)) {
            emit(state, 'state:blocked', {from: previous, to: next, meta: meta || {}});
            return false;
        }
        if (previous === next) {
            applyTrackerStateFlags(state, next);
            return true;
        }

        state.trackerstate = next;
        state._transitionSerial = getTransitionToken(state) + 1;
        applyTrackerStateFlags(state, next);

        emit(state, 'state:change', {from: previous, to: next, meta: meta || {}});
        return true;
    }

    function markIdle(state, meta) {
        return setTrackerState(state, STATES.IDLE, meta);
    }

    function markPlaying(state, meta) {
        return setTrackerState(state, STATES.PLAYING, meta);
    }

    function markPaused(state, meta) {
        return setTrackerState(state, STATES.PAUSED, meta);
    }

    function markSeeking(state, meta) {
        return setTrackerState(state, STATES.SEEKING, meta);
    }

    function markEnded(state, meta) {
        return setTrackerState(state, STATES.ENDED, meta);
    }

    function markDestroyed(state, meta) {
        return setTrackerState(state, STATES.DESTROYED, meta);
    }

    /**
     * Update the shared last-known playback position.
     *
     * @param {Object} state Mutable player state.
     * @param {number} currentTime Current media time.
     * @param {number=} playbackRate Optional playback rate.
     * @returns {number} Normalised current media time.
     */
    function syncTime(state, currentTime, playbackRate) {
        var time = normaliseTime(currentTime);
        if (state) {
            state.lasttime = time;
            if (typeof playbackRate === 'number' && isFinite(playbackRate) && playbackRate > 0) {
                state.playbackrate = playbackRate;
            }
        }
        return time;
    }


    /**
     * Resolve a current-time provider safely and normalise its result.
     *
     * Providers differ: YouTube/HTML5 usually return synchronously while Vimeo
     * returns a Promise. Keeping that contract in the tracker prevents small
     * provider differences from leaking into every lifecycle helper.
     *
     * @param {Function} getCurrentTime Function returning current media time.
     * @param {Object=} state Optional mutable state used as fallback context.
     * @returns {Promise<number>} Promise resolving to a safe media time.
     */
    function resolveCurrentTime(getCurrentTime, state) {
        if (typeof getCurrentTime !== 'function') {
            return Promise.resolve(normaliseTime(state && state.lasttime));
        }
        return Promise.resolve().then(function() {
            return getCurrentTime();
        }).then(function(currentTime) {
            return normaliseTime(currentTime);
        });
    }

    /**
     * Mark the next seek as controlled by the plugin rather than the learner.
     *
     * @param {Object} state Mutable player state.
     */
    function markProgrammaticSeek(state) {
        if (state) {
            state.isProgrammaticSeek = true;
            state.wasPlayingBeforeProgrammaticSeek = !!state.playing;
            markSeeking(state, {reason: 'programmatic-seek'});
        }
    }

    /**
     * Consume and clear a pending programmatic seek flag.
     *
     * @param {Object} state Mutable player state.
     * @param {number=} currentTime Optional media time to sync while consuming.
     * @returns {boolean} True when a programmatic seek was consumed.
     */
    function consumeProgrammaticSeek(state, currentTime) {
        if (!state || !state.isProgrammaticSeek) {
            return false;
        }
        state.isProgrammaticSeek = false;
        if (typeof currentTime !== 'undefined') {
            syncTime(state, currentTime);
        }
        if (state.wasPlayingBeforeProgrammaticSeek) {
            markPlaying(state, {reason: 'programmatic-seek-complete'});
        } else {
            markPaused(state, {reason: 'programmatic-seek-complete'});
        }
        delete state.wasPlayingBeforeProgrammaticSeek;
        return true;
    }

    /**
     * Resolve whether a learner seek is allowed by the configuration.
     *
     * @param {Object} state Mutable player state.
     * @param {number} newTime Requested media time.
     * @param {Object} config Player configuration.
     * @param {number=} tolerance Seconds ignored as playback drift.
     * @returns {Object} Seek decision.
     */
    function resolveSeek(state, newTime, config, tolerance) {
        var oldTime = normaliseTime(state && state.lasttime);
        var target = normaliseTime(newTime);
        var drift = typeof tolerance === 'number' ? Math.max(0, tolerance) : 0;
        var delta = target - oldTime;
        var forward = delta > drift;
        var backward = delta < -drift;
        var blocked = false;

        if (forward && config && config.allowseekforward === false) {
            blocked = true;
        }
        if (backward && config && config.allowseekbackward === false) {
            blocked = true;
        }

        return {
            oldTime: oldTime,
            newTime: target,
            delta: delta,
            forward: forward,
            backward: backward,
            changed: forward || backward,
            blocked: blocked,
            fallbackTime: oldTime
        };
    }


    /**
     * Temporarily block seek handling while a provider bounces back to the
     * allowed fallback time. Reusing one helper avoids slightly different
     * timeout behaviour between YouTube, HTML5 and Vimeo, and clears any older
     * timer before installing a new one.
     *
     * @param {Object} state Mutable player state.
     * @param {number=} delay Timeout in milliseconds.
     */
    function blockSeek(state, delay) {
        if (!state) {
            return;
        }
        state.seekblocked = true;
        state.wasPlayingBeforeSeekBlock = !!state.playing;
        markSeeking(state, {reason: 'seek-blocked'});
        if (state.seekblocktimer) {
            window.clearTimeout(state.seekblocktimer);
        }
        state.seekblocktimer = window.setTimeout(function() {
            state.seekblocked = false;
            if (state.isSeeking) {
                if (state.wasPlayingBeforeSeekBlock) {
                    markPlaying(state, {reason: 'seek-block-cleared'});
                } else {
                    markPaused(state, {reason: 'seek-block-cleared'});
                }
            }
            delete state.wasPlayingBeforeSeekBlock;
            state.seekblocktimer = null;
        }, typeof delay === 'number' && delay >= 0 ? delay : 500);
    }

    /**
     * Clear a pending seek block and its timeout.
     *
     * @param {Object} state Mutable player state.
     */
    function clearSeekBlock(state) {
        if (!state) {
            return;
        }
        if (state.seekblocktimer) {
            window.clearTimeout(state.seekblocktimer);
            state.seekblocktimer = null;
        }
        state.seekblocked = false;
        if (state.isSeeking) {
            if (state.wasPlayingBeforeSeekBlock) {
                markPlaying(state, {reason: 'seek-block-cleared'});
            } else {
                markPaused(state, {reason: 'seek-block-cleared'});
            }
        }
        delete state.wasPlayingBeforeSeekBlock;
    }

    /**
     * Check replay end state and clear it when playback reached the limit.
     *
     * @param {Object} state Mutable player state.
     * @param {number} currentTime Current media time.
     * @returns {boolean} True when playback should be paused.
     */
    function shouldStopReplay(state, currentTime) {
        if (!state || state.currentReplayEnd == null) {
            return false;
        }
        if (normaliseTime(currentTime) >= state.currentReplayEnd) {
            state.currentReplayEnd = null;
            markPaused(state, {reason: 'replay-limit'});
            emit(state, 'replay:limit', {currentTime: normaliseTime(currentTime)});
            return true;
        }
        return false;
    }

    /**
     * Open a new watched segment from the current media time.
     *
     * @param {Object} state Mutable player state.
     * @param {number} currentTime Current media time.
     * @param {number=} wallclock Optional wallclock timestamp in seconds.
     * @param {number=} playbackRate Optional current playback rate.
     * @returns {Object|null} Updated state or null when state is missing.
     */
    function openSegment(state, currentTime, wallclock, playbackRate) {
        if (!state) {
            return null;
        }
        var start = Number(currentTime);
        if (!isFinite(start) || start < 0) {
            start = 0;
        }
        var timestamp = typeof wallclock === 'number' ? wallclock : Math.floor(Date.now() / 1000);
        markPlaying(state, {reason: 'segment-open'});
        state.segmentstart = start;
        state.wallclockstart = timestamp;
        state.lasttime = start;
        if (typeof playbackRate === 'number' && isFinite(playbackRate) && playbackRate > 0) {
            state.playbackrate = playbackRate;
        }
        resetHeartbeat(state, timestamp);
        emit(state, 'segment:open', {start: start, wallclock: timestamp});
        return state;
    }

    /**
     * Close the current watched segment and clear the mutable lifecycle state.
     *
     * @param {Object} state Mutable player state.
     * @param {number} end Current media time.
     * @returns {{start: number, end: number}|null} Closed segment payload.
     */
    function closeSegment(state, end) {
        if (!state || state.segmentstart === null) {
            return null;
        }
        var payload = {
            start: normaliseTime(state.segmentstart),
            end: normaliseTime(end)
        };
        if (payload.end <= payload.start) {
            var wasPlaying = !!state.playing;
            state.segmentstart = null;
            state.wallclockstart = null;
            if (!wasPlaying) {
                markPaused(state, {reason: 'segment-close-zero-duration'});
            }
            emit(state, 'segment:skipped', {reason: 'zero-duration', start: payload.start, end: payload.end});
            return null;
        }
        state.segmentstart = null;
        state.wallclockstart = null;
        markPaused(state, {reason: 'segment-close'});
        emit(state, 'segment:close', payload);
        return payload;
    }

    /**
     * Close the currently open segment and persist it through the supplied callback.
     *
     * This provider-neutral helper keeps close/persist error handling in the
     * tracker layer. The current-time callback may return either a number or a
     * Promise resolving to a number, which lets YouTube/HTML5 and Vimeo use the
     * same lifecycle path.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getCurrentTime Function returning current media time.
     * @param {Function} saveSegment Function used to persist the closed segment.
     * @param {string} reason Save reason.
     * @param {boolean} hasPlayer Whether the concrete player is available.
     * @returns {Promise<boolean>} True when a segment was closed and queued for saving.
     */
    function closeAndSaveSegment(state, getCurrentTime, saveSegment, reason, hasPlayer) {
        if (!state || state.segmentstart === null || !isPlayerAvailable(hasPlayer)) {
            return Promise.resolve(false);
        }
        if (typeof getCurrentTime !== 'function' || typeof saveSegment !== 'function') {
            return Promise.resolve(false);
        }

        return resolveCurrentTime(getCurrentTime, state).then(function(currentTime) {
            var closed = closeSegment(state, currentTime);
            if (!closed || closed.end <= closed.start) {
                return false;
            }
            var saveReason = Segment.normaliseSaveReason(reason);
            return Promise.resolve(saveSegment(closed.start, closed.end, saveReason))
                .then(function() {
                    emit(state, 'segment:saved', {start: closed.start, end: closed.end, reason: saveReason});
                    return true;
                }, function(error) {
                    emit(state, 'segment:error', {start: closed.start, end: closed.end, reason: saveReason, error: error});
                    throw error;
                });
        });
    }

    /**
     * Capture the current open segment for a heartbeat save.
     *
     * The segment is not reopened here. Reopening before the persistence
     * promise resolves can silently lose watch time when the request fails.
     * Call reopenAfterHeartbeat only after a successful save.
     *
     * @param {Object} state Mutable player state.
     * @param {number} end Current media time.
     * @returns {{start: number, end: number}|null} Segment payload to persist.
     */
    function captureHeartbeatSegment(state, end) {
        if (!state || !state.playing || state.segmentstart === null) {
            return null;
        }
        return {
            start: normaliseTime(state.segmentstart),
            end: normaliseTime(end)
        };
    }

    /**
     * Resolve the configured heartbeat interval in seconds.
     *
     * @param {Object} config Player configuration.
     * @param {number} fallback Fallback interval in seconds.
     * @returns {number} Positive heartbeat interval.
     */
    function normaliseHeartbeatInterval(config, fallback) {
        var candidate = config && Number(config.heartbeatinterval);
        if (candidate > 0 && isFinite(candidate)) {
            return candidate;
        }
        return fallback > 0 ? fallback : 30;
    }

    /**
     * Resolve the polling interval used to check heartbeats and seeks.
     *
     * @param {number} heartbeatInterval Heartbeat interval in seconds.
     * @returns {number} Polling interval in milliseconds.
     */
    function pollInterval(heartbeatInterval) {
        var interval = Number(heartbeatInterval) || 30;
        var base = Math.min(5000, Math.max(2000, interval * 250));
        if (typeof document !== 'undefined' && document.hidden) {
            return Math.min(15000, Math.max(base, interval * 500));
        }
        return base;
    }

    /**
     * Start a shared interval and store its id in the mutable state object.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} callback Polling callback.
     * @param {number} heartbeatInterval Heartbeat interval in seconds.
     * @returns {number|null} Interval id or existing interval id.
     */
    function startPolling(state, callback, heartbeatInterval) {
        if (!state || state.heartbeatid) {
            return state ? state.heartbeatid : null;
        }
        if (typeof callback !== 'function') {
            return null;
        }
        state.heartbeatid = window.setInterval(callback, pollInterval(heartbeatInterval));
        return state.heartbeatid;
    }

    /**
     * Stop the shared interval stored in the mutable state object.
     *
     * @param {Object} state Mutable player state.
     */
    function stopPolling(state) {
        if (state && state.heartbeatid) {
            window.clearInterval(state.heartbeatid);
            state.heartbeatid = null;
            if (state.playing && state.segmentstart === null) {
                markPaused(state, {reason: 'polling-stopped'});
            }
        }
    }

    /**
     * Mark the current wallclock as heartbeat baseline.
     *
     * @param {Object} state Mutable player state.
     * @param {number=} now Optional wallclock timestamp in seconds.
     * @returns {number} Wallclock timestamp used.
     */
    function resetHeartbeat(state, now) {
        var timestamp = typeof now === 'number' ? now : Math.floor(Date.now() / 1000);
        if (state) {
            state.lastHeartbeatWallclock = timestamp;
        }
        return timestamp;
    }

    /**
     * Whether the current open segment should be saved as a heartbeat.
     *
     * @param {Object} state Mutable player state.
     * @param {number} heartbeatInterval Heartbeat interval in seconds.
     * @param {number=} now Optional wallclock timestamp in seconds.
     * @returns {boolean} True when a heartbeat save is due.
     */
    function shouldSaveHeartbeat(state, heartbeatInterval, now) {
        if (!state || !state.playing || state.segmentstart === null || state.heartbeatPending) {
            return false;
        }
        var timestamp = typeof now === 'number' ? now : Math.floor(Date.now() / 1000);
        var last = Number(state.lastHeartbeatWallclock) || 0;
        return timestamp - last >= normaliseHeartbeatInterval({heartbeatinterval: heartbeatInterval}, 30);
    }






    /**
     * Execute a boolean callback without allowing provider errors to break tracking.
     *
     * @param {Function} callback Callback to execute.
     * @param {boolean} fallback Fallback value used when the callback throws.
     * @param {Object=} state Mutable player state for diagnostics.
     * @param {string=} eventName Event name emitted on callback error.
     * @returns {boolean} Normalised callback result.
     */
    function safeBooleanCallback(callback, fallback, state, eventName) {
        try {
            return !!callback();
        } catch (error) {
            if (eventName) {
                emit(state, eventName, {error: error});
            }
            return !!fallback;
        }
    }

    /**
     * Run one provider-neutral heartbeat check.
     *
     * Concrete player modules only provide media availability, current-time and
     * persistence callbacks. This keeps the guard clauses, async error handling
     * and logging behaviour in the tracker layer, which reduces the chance that
     * YouTube, HTML5 and Vimeo drift apart again.
     *
     * @param {Object} options Heartbeat options.
     * @param {Object} options.state Mutable player state.
     * @param {number} options.heartbeatInterval Heartbeat interval in seconds.
     * @param {Function} options.getCurrentTime Function returning current media time.
     * @param {Function} options.saveSegment Segment persistence callback.
     * @param {Function=} options.hasPlayer Optional player availability callback.
     * @param {Function=} options.shouldSkip Optional extra provider-specific skip callback.
     * @param {Object=} options.log Optional Moodle log module.
     * @returns {Promise<boolean>} True when a heartbeat segment was saved.
     */
    function runHeartbeat(options) {
        options = options || {};
        var state = options.state;
        var hasPlayer = typeof options.hasPlayer === 'function' ? options.hasPlayer : function() {
            return true;
        };
        var shouldSkip = typeof options.shouldSkip === 'function' ? options.shouldSkip : function() {
            return false;
        };

        if (!state || !state.playing || state.segmentstart === null ||
                !safeBooleanCallback(hasPlayer, false, state, 'heartbeat:providererror') ||
                safeBooleanCallback(shouldSkip, true, state, 'heartbeat:skiperror')) {
            return Promise.resolve(false);
        }
        if (state._heartbeatRunning) {
            return Promise.resolve(false);
        }
        state._heartbeatRunning = true;

        function clearHeartbeatRunning(saved) {
            if (state) {
                state._heartbeatRunning = false;
            }
            return saved;
        }

        emit(state, 'heartbeat:start', {});

        return saveHeartbeatIfDue(
            state,
            options.heartbeatInterval,
            options.getCurrentTime,
            options.saveSegment
        ).then(function(saved) {
            emit(state, 'heartbeat:complete', {saved: !!saved});
            return saved;
        }).catch(function(error) {
            emit(state, 'heartbeat:error', {error: error});
            if (options.log && typeof options.log.debug === 'function') {
                options.log.debug(error);
            }
            return false;
        }).then(clearHeartbeatRunning, function(error) {
            clearHeartbeatRunning(false);
            throw error;
        });
    }


    /**
     * Queue the current open segment through sendBeacon during page unload.
     *
     * Browser unload handlers cannot wait for asynchronous player APIs, so the
     * end time is resolved only from synchronous data: either an explicit
     * getCurrentTime callback or the shared last-known state. Keeping this in
     * the tracker makes YouTube, HTML5 and Vimeo use the same beforeunload
     * guard and payload shape while concrete modules keep their provider
     * specific time source.
     *
     * @param {Object} options Beacon options.
     * @param {Object} options.state Mutable player state.
     * @param {Function} options.sendSegment Callback that queues the beacon.
     * @param {Function=} options.getCurrentTime Optional synchronous time getter.
     * @param {Function=} options.hasPlayer Optional player availability callback.
     * @returns {boolean} True when the callback accepted the beacon.
     */
    function sendUnloadBeacon(options) {
        options = options || {};
        var state = options.state;
        var hasPlayer = typeof options.hasPlayer === 'function' ? options.hasPlayer : function() {
            return true;
        };

        if (!state || state.segmentstart === null ||
                !safeBooleanCallback(hasPlayer, false, state, 'beacon:providererror')) {
            return false;
        }
        if (state.unloadBeaconQueued) {
            emit(state, 'beacon:skipped', {reason: 'already-queued'});
            return false;
        }
        if (typeof options.sendSegment !== 'function') {
            return false;
        }

        var end = state.lasttime;
        if (typeof options.getCurrentTime === 'function') {
            try {
                end = options.getCurrentTime();
            } catch (error) {
                end = state.lasttime;
            }
        }

        var start = normaliseTime(state.segmentstart);
        var finish = normaliseTime(end);
        if (finish <= start) {
            emit(state, 'beacon:skipped', {reason: 'zero-duration', start: start, end: finish});
            return false;
        }

        var queued = !!options.sendSegment(start, finish);
        if (queued) {
            state.unloadBeaconQueued = true;
            emit(state, 'beacon:queued', {start: start, end: finish});
        }
        return queued;
    }

    /**
     * Install shared page lifecycle handlers for player modules.
     *
     * The concrete player modules still provide provider-specific close and
     * beacon callbacks, but the visibility/pagehide/beforeunload wiring now
     * lives in one place so behaviour stays consistent across YouTube, HTML5
     * and Vimeo.
     *
     * @param {Object} options Handler options.
     * @param {Object} options.state Mutable player state.
     * @param {Function} options.closeSegment Function called with the close reason.
     * @param {Function=} options.stopPolling Optional polling stop callback.
     * @param {Function=} options.onHidden Optional callback after hidden close.
     * @param {Function=} options.sendBeacon Optional beforeunload beacon callback.
     * @param {Function=} options.hasPlayer Optional player availability callback.
     * @returns {boolean} True when handlers were installed, false when already installed.
     */
    function installLifecycleHandlers(options) {
        options = options || {};
        var state = options.state;

        if (state && state.lifecycleHandlersInstalled) {
            uninstallLifecycleHandlers(state);
        }
        if (state) {
            state.lifecycleHandlersInstalled = true;
        }

        var closeSegment = typeof options.closeSegment === 'function' ? options.closeSegment : null;
        var stop = typeof options.stopPolling === 'function' ? options.stopPolling : function() {
            stopPolling(state);
        };
        var onHidden = typeof options.onHidden === 'function' ? options.onHidden : null;
        var sendBeacon = typeof options.sendBeacon === 'function' ? options.sendBeacon : null;
        var hasPlayer = typeof options.hasPlayer === 'function' ? options.hasPlayer : function() {
            return true;
        };

        function closeThenStop(reason, options) {
            options = options || {};
            var hasOpenSegment = state && state.segmentstart !== null;
            if (!hasOpenSegment) {
                stop();
                if (options.afterStop) {
                    options.afterStop();
                }
                return;
            }
            if (options.preferBeacon && sendBeacon) {
                if (sendBeacon()) {
                    stop();
                    if (options.afterStop) {
                        options.afterStop();
                    }
                    return;
                }
                emit(state, 'lifecycle:beaconfallback', {reason: reason});
            }
            if (closeSegment) {
                Promise.resolve(closeSegment(reason)).catch(function(error) {
                    emit(state, 'lifecycle:closeerror', {error: error, reason: reason});
                }).then(function() {
                    stop();
                    if (options.afterStop) {
                        options.afterStop();
                    }
                });
                return;
            }
            stop();
            if (options.afterStop) {
                options.afterStop();
            }
        }

        var onVisibilityChange = function() {
            if (!document.hidden) {
                return;
            }
            emit(state, 'lifecycle:hidden', {});
            closeThenStop('tab', {afterStop: onHidden});
        };

        var onPageHide = function() {
            emit(state, 'lifecycle:pagehide', {});
            closeThenStop('pagehide', {preferBeacon: true});
        };

        var onBeforeUnload = function() {
            if (!state || state.segmentstart === null ||
                    !safeBooleanCallback(hasPlayer, false, state, 'lifecycle:providererror')) {
                return;
            }
            emit(state, 'lifecycle:beforeunload', {});
            if (sendBeacon) {
                sendBeacon();
            }
        };

        document.addEventListener('visibilitychange', onVisibilityChange);
        window.addEventListener('pagehide', onPageHide);
        window.addEventListener('beforeunload', onBeforeUnload);

        emit(state, 'lifecycle:installed', {});

        if (state) {
            state.lifecycleHandlers = {
                visibilitychange: onVisibilityChange,
                pagehide: onPageHide,
                beforeunload: onBeforeUnload
            };
        }
        return true;
    }

    /**
     * Remove lifecycle handlers previously installed for a player state.
     *
     * Moodle pages normally initialise each AMD entrypoint once, but keeping a
     * cleanup helper makes the tracker safer for dynamic reinitialisation and
     * for future automated tests that mount and unmount players repeatedly.
     *
     * @param {Object} state Mutable player state.
     * @returns {boolean} True when handlers were removed.
     */
    function uninstallLifecycleHandlers(state) {
        if (!state || !state.lifecycleHandlers) {
            return false;
        }

        document.removeEventListener('visibilitychange', state.lifecycleHandlers.visibilitychange);
        window.removeEventListener('pagehide', state.lifecycleHandlers.pagehide);
        window.removeEventListener('beforeunload', state.lifecycleHandlers.beforeunload);

        state.lifecycleHandlers = null;
        state.lifecycleHandlersInstalled = false;
        cancelPendingRequests(state, 'lifecycle-uninstall');
        emit(state, 'lifecycle:uninstalled', {});
        return true;
    }

    /**
     * Resolve a boolean or callback-based availability check.
     *
     * @param {boolean|Function} hasPlayer Player availability flag or callback.
     * @returns {boolean} True when the concrete player can be used.
     */
    function isPlayerAvailable(hasPlayer) {
        if (typeof hasPlayer === 'function') {
            return safeBooleanCallback(hasPlayer, false, null, null);
        }
        return !!hasPlayer;
    }

    /**
     * Save progress for a currently playing segment before an interaction.
     *
     * This keeps interaction-triggered persistence in the tracker layer rather
     * than in the DOM/UI facade. It is intentionally Promise-based so callers
     * can chain reaction or note saves after the current segment has been
     * persisted, while still returning a resolved promise when there is no
     * active segment to save.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getCurrentTime Function returning the current video time.
     * @param {Function} saveSegment Function used to persist the segment.
     * @param {string} reason Save reason.
     * @param {boolean|Function} hasPlayer Whether the concrete player is available.
     * @returns {Promise} Save promise or null-equivalent promise.
     */
    function saveCurrentProgress(state, getCurrentTime, saveSegment, reason, hasPlayer) {
        if (!state || !state.playing || state.segmentstart === null || !isPlayerAvailable(hasPlayer)) {
            return Promise.resolve(null);
        }
        if (typeof getCurrentTime !== 'function' || typeof saveSegment !== 'function') {
            return Promise.resolve(null);
        }

        var start = normaliseTime(state.segmentstart);

        return resolveCurrentTime(getCurrentTime, state).then(function(currentTime) {
            var end = Segment.calculateInteractionEnd(start, currentTime, state.duration, reason);
            var saveReason = Segment.normaliseSaveReason(reason);
            if (end <= start) {
                emit(state, 'progress:skipped', {reason: saveReason, start: start, end: end});
                return null;
            }
            return Promise.resolve(saveSegment(start, end, saveReason)).then(function(result) {
                reopenAfterInteractionSave(state, end);
                emit(state, 'progress:saved', {reason: saveReason, start: start, end: end});
                return result;
            }, function(error) {
                emit(state, 'progress:error', {reason: saveReason, start: start, end: end, error: error});
                throw error;
            });
        });
    }

    /**
     * Move the open segment start after a successful interaction save.
     *
     * Reaction and note handlers save the current segment before persisting
     * their own event. Keeping the original segment start after that save makes
     * the next pause or heartbeat persist the same watched time again. Reopening
     * from the saved end keeps the tracker monotonic without changing concrete
     * player code.
     *
     * @param {Object} state Mutable player state.
     * @param {number} end Segment end/current time.
     * @param {number=} now Optional wallclock timestamp in seconds.
     */
    function reopenAfterInteractionSave(state, end, now) {
        var timestamp = resetHeartbeat(state, now);
        if (state && state.playing) {
            state.segmentstart = normaliseTime(end);
            state.wallclockstart = timestamp;
        }
    }

    /**
     * Capture and persist a heartbeat segment when due.
     *
     * The concrete player modules provide the current media time and persistence
     * callback; this helper keeps the heartbeat decision, capture and reason
     * naming in one place. The current-time provider may return either a number
     * or a Promise resolving to a number.
     *
     * @param {Object} state Mutable player state.
     * @param {number} heartbeatInterval Heartbeat interval in seconds.
     * @param {Function} getCurrentTime Current-time provider.
     * @param {Function} saveSegment Segment persistence callback.
     * @param {number=} now Optional wallclock timestamp in seconds.
     * @returns {Promise<boolean>} True when a heartbeat segment was saved.
     */
    function saveHeartbeatIfDue(state, heartbeatInterval, getCurrentTime, saveSegment, now) {
        var timestamp = typeof now === 'number' ? now : Math.floor(Date.now() / 1000);

        if (!shouldSaveHeartbeat(state, heartbeatInterval, timestamp)) {
            return Promise.resolve(false);
        }

        if (typeof getCurrentTime !== 'function' || typeof saveSegment !== 'function') {
            return Promise.resolve(false);
        }

        state.heartbeatPending = true;
        state._heartbeatSerial = (typeof state._heartbeatSerial === 'number' ? state._heartbeatSerial : 0) + 1;
        var heartbeatSerial = state._heartbeatSerial;
        var transitionToken = getTransitionToken(state);

        return resolveCurrentTime(getCurrentTime, state).then(function(currentTime) {
            if (!isTransitionCurrent(state, transitionToken) || state._heartbeatSerial !== heartbeatSerial) {
                state.heartbeatPending = false;
                emit(state, 'heartbeat:skipped', {reason: 'stale-state'});
                return false;
            }
            var heartbeat = captureHeartbeatSegment(state, currentTime);
            if (!heartbeat) {
                state.heartbeatPending = false;
                emit(state, 'heartbeat:skipped', {reason: 'empty'});
                return false;
            }
            if (heartbeat.end <= heartbeat.start) {
                resetHeartbeat(state, timestamp);
                state.heartbeatPending = false;
                emit(state, 'heartbeat:skipped', {reason: 'zero-duration'});
                return false;
            }
            return Promise.resolve(saveSegment(heartbeat.start, heartbeat.end, 'heartbeat')).then(function() {
                if (!isTransitionCurrent(state, transitionToken) || state._heartbeatSerial !== heartbeatSerial) {
                    state.heartbeatPending = false;
                    emit(state, 'heartbeat:skipped', {reason: 'stale-state'});
                    return false;
                }
                reopenAfterHeartbeat(state, heartbeat.end, timestamp);
                state.heartbeatPending = false;
                emit(state, 'heartbeat:saved', {start: heartbeat.start, end: heartbeat.end});
                return true;
            }, function(error) {
                state.heartbeatPending = false;
                throw error;
            });
        }, function(error) {
            state.heartbeatPending = false;
            throw error;
        });
    }

    /**
     * Move the open segment start after a successful heartbeat save.
     *
     * @param {Object} state Mutable player state.
     * @param {number} end Segment end/current time.
     * @param {number=} now Optional wallclock timestamp in seconds.
     */
    function reopenAfterHeartbeat(state, end, now) {
        var timestamp = resetHeartbeat(state, now);
        if (state) {
            state.segmentstart = normaliseTime(end);
            state.wallclockstart = timestamp;
        }
    }

    /**
     * Cancel pending request continuations associated with a player state.
     *
     * This is a cleanup guard for dynamic teardown/reinitialisation. It does not
     * abort already-dispatched Moodle AJAX calls; it prevents their late promise
     * continuations from mutating stale player state.
     *
     * @param {Object} state Mutable player state.
     * @param {string=} reason Cleanup reason.
     * @returns {boolean} True when a request scope was cancelled.
     */
    function cancelPendingRequests(state, reason) {
        if (!state || !state.ajaxRequestScope || typeof state.ajaxRequestScope.cancel !== 'function') {
            return false;
        }
        state.ajaxRequestScope.cancel(reason || 'cleanup');
        emit(state, 'ajax:cancelled', {reason: reason || 'cleanup'});
        return true;
    }

    return {
        STATES: STATES,
        normaliseTrackerState: normaliseTrackerState,
        getTrackerState: getTrackerState,
        getTransitionToken: getTransitionToken,
        isTransitionCurrent: isTransitionCurrent,
        canTransition: canTransition,
        applyTrackerStateFlags: applyTrackerStateFlags,
        setTrackerState: setTrackerState,
        markIdle: markIdle,
        markPlaying: markPlaying,
        markPaused: markPaused,
        markSeeking: markSeeking,
        markEnded: markEnded,
        markDestroyed: markDestroyed,
        normaliseTime: normaliseTime,
        on: on,
        once: once,
        off: off,
        countEvents: countEvents,
        clearEvents: clearEvents,
        emit: emit,
        resolveCurrentTime: resolveCurrentTime,
        syncTime: syncTime,
        markProgrammaticSeek: markProgrammaticSeek,
        consumeProgrammaticSeek: consumeProgrammaticSeek,
        resolveSeek: resolveSeek,
        shouldStopReplay: shouldStopReplay,
        blockSeek: blockSeek,
        clearSeekBlock: clearSeekBlock,
        openSegment: openSegment,
        closeSegment: closeSegment,
        captureHeartbeatSegment: captureHeartbeatSegment,
        closeAndSaveSegment: closeAndSaveSegment,
        normaliseHeartbeatInterval: normaliseHeartbeatInterval,
        pollInterval: pollInterval,
        startPolling: startPolling,
        stopPolling: stopPolling,
        resetHeartbeat: resetHeartbeat,
        shouldSaveHeartbeat: shouldSaveHeartbeat,
        saveHeartbeatIfDue: saveHeartbeatIfDue,
        runHeartbeat: runHeartbeat,
        sendUnloadBeacon: sendUnloadBeacon,
        safeBooleanCallback: safeBooleanCallback,
        reopenAfterHeartbeat: reopenAfterHeartbeat,
        installLifecycleHandlers: installLifecycleHandlers,
        uninstallLifecycleHandlers: uninstallLifecycleHandlers,
        cancelPendingRequests: cancelPendingRequests,
        reopenAfterInteractionSave: reopenAfterInteractionSave,
        isPlayerAvailable: isPlayerAvailable,
        saveCurrentProgress: saveCurrentProgress
    };
});
