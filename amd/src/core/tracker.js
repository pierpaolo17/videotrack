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
    'mod_videotrack/core/segment'
], function(Segment) {



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
     * Mark the next seek as controlled by the plugin rather than the learner.
     *
     * @param {Object} state Mutable player state.
     */
    function markProgrammaticSeek(state) {
        if (state) {
            state.isProgrammaticSeek = true;
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
     * Check replay end state and clear it when playback reached the limit.
     *
     * @param {Object} state Mutable player state.
     * @param {number} currentTime Current media time.
     * @returns {boolean} True when playback should be paused.
     */
    function shouldStopReplay(state, currentTime) {
        if (!state || state.currentReplayEnd === null) {
            return false;
        }
        if (normaliseTime(currentTime) >= state.currentReplayEnd) {
            state.currentReplayEnd = null;
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
        state.playing = true;
        state.segmentstart = start;
        state.wallclockstart = timestamp;
        state.lasttime = start;
        if (typeof playbackRate === 'number' && isFinite(playbackRate) && playbackRate > 0) {
            state.playbackrate = playbackRate;
        }
        resetHeartbeat(state, timestamp);
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
        if (!state || !state.playing || state.segmentstart === null) {
            return null;
        }
        var payload = {
            start: normaliseTime(state.segmentstart),
            end: normaliseTime(end)
        };
        state.playing = false;
        state.segmentstart = null;
        state.wallclockstart = null;
        return payload;
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
        return Math.min(5000, Math.max(2000, interval * 250));
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
     * @param {boolean} hasPlayer Whether the concrete player is available.
     * @returns {Promise|null} Save promise or null-equivalent promise.
     */
    function saveCurrentProgress(state, getCurrentTime, saveSegment, reason, hasPlayer) {
        if (!state || !state.playing || state.segmentstart === null || !hasPlayer) {
            return Promise.resolve(null);
        }
        if (typeof getCurrentTime !== 'function' || typeof saveSegment !== 'function') {
            return Promise.resolve(null);
        }
        var end = Segment.calculateInteractionEnd(state.segmentstart, getCurrentTime(), state.duration, reason);
        if (end <= state.segmentstart) {
            return Promise.resolve(null);
        }
        return saveSegment(state.segmentstart, end, Segment.normaliseSaveReason(reason));
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

        return Promise.resolve(getCurrentTime()).then(function(currentTime) {
            var heartbeat = captureHeartbeatSegment(state, currentTime);
            if (!heartbeat) {
                state.heartbeatPending = false;
                return false;
            }
            return Promise.resolve(saveSegment(heartbeat.start, heartbeat.end, 'heartbeat')).then(function() {
                reopenAfterHeartbeat(state, heartbeat.end, timestamp);
                state.heartbeatPending = false;
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

    return {
        normaliseTime: normaliseTime,
        syncTime: syncTime,
        markProgrammaticSeek: markProgrammaticSeek,
        consumeProgrammaticSeek: consumeProgrammaticSeek,
        resolveSeek: resolveSeek,
        shouldStopReplay: shouldStopReplay,
        openSegment: openSegment,
        closeSegment: closeSegment,
        captureHeartbeatSegment: captureHeartbeatSegment,
        normaliseHeartbeatInterval: normaliseHeartbeatInterval,
        pollInterval: pollInterval,
        startPolling: startPolling,
        stopPolling: stopPolling,
        resetHeartbeat: resetHeartbeat,
        shouldSaveHeartbeat: shouldSaveHeartbeat,
        saveHeartbeatIfDue: saveHeartbeatIfDue,
        reopenAfterHeartbeat: reopenAfterHeartbeat,
        saveCurrentProgress: saveCurrentProgress
    };
});
