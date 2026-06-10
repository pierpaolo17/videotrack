/**
 * Provider-neutral heartbeat helpers for tracker modules.
 *
 * @module mod_videotrack/core/tracker/heartbeat
 */
define([
    'mod_videotrack/core/tracker/events',
    'mod_videotrack/core/tracker/state',
    'mod_videotrack/core/tracker/time'
], function(TrackerEvents, TrackerState, TrackerTime) {
    'use strict';

    var emit = TrackerEvents.emit;
    var markPaused = TrackerState.markPaused;
    var normaliseTime = TrackerState.normaliseTime;
    var getTransitionToken = TrackerState.getTransitionToken;
    var isTransitionCurrent = TrackerState.isTransitionCurrent;
    var resolveCurrentTime = TrackerTime.resolveCurrentTime;

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
     * Capture the current open segment for a heartbeat save.
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
        var base = Math.min(1000, Math.max(750, interval * 100));
        if (typeof document !== 'undefined' && document.hidden) {
            return Math.min(5000, Math.max(base, interval * 250));
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
     * Serialise a heartbeat save through the shared tracker queue.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} enqueueSegmentSave Queue helper supplied by the main tracker module.
     * @param {number} heartbeatInterval Heartbeat interval in seconds.
     * @param {Function} getCurrentTime Current-time provider.
     * @param {Function} saveSegment Segment persistence callback.
     * @param {number=} now Optional wallclock timestamp in seconds.
     * @returns {Promise<boolean>} True when a heartbeat segment was saved.
     */
    function saveHeartbeatIfDue(state, enqueueSegmentSave, heartbeatInterval, getCurrentTime, saveSegment, now) {
        return enqueueSegmentSave(state, function() {
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
        });
    }

    /**
     * Run one provider-neutral heartbeat check.
     *
     * @param {Object} options Heartbeat options.
     * @param {Function} enqueueSegmentSave Queue helper supplied by the main tracker module.
     * @returns {Promise<boolean>} True when a heartbeat segment was saved.
     */
    function runHeartbeat(options, enqueueSegmentSave) {
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

        /**
         * Clear the running flag after a heartbeat attempt completes.
         *
         * @param {boolean} saved Whether the heartbeat segment was saved.
         * @returns {boolean} Original saved value.
         */
        function clearHeartbeatRunning(saved) {
            if (state) {
                state._heartbeatRunning = false;
            }
            return saved;
        }

        emit(state, 'heartbeat:start', {});

        return saveHeartbeatIfDue(
            state,
            enqueueSegmentSave,
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

    return {
        captureHeartbeatSegment: captureHeartbeatSegment,
        normaliseHeartbeatInterval: normaliseHeartbeatInterval,
        pollInterval: pollInterval,
        startPolling: startPolling,
        stopPolling: stopPolling,
        resetHeartbeat: resetHeartbeat,
        shouldSaveHeartbeat: shouldSaveHeartbeat,
        saveHeartbeatIfDue: saveHeartbeatIfDue,
        runHeartbeat: runHeartbeat,
        safeBooleanCallback: safeBooleanCallback,
        reopenAfterHeartbeat: reopenAfterHeartbeat
    };
});
