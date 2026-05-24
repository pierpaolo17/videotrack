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
define([], function() {

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
        if (!state || !state.playing || state.segmentstart === null) {
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
            state.segmentstart = end;
            state.wallclockstart = timestamp;
        }
    }

    return {
        normaliseHeartbeatInterval: normaliseHeartbeatInterval,
        pollInterval: pollInterval,
        startPolling: startPolling,
        stopPolling: stopPolling,
        resetHeartbeat: resetHeartbeat,
        shouldSaveHeartbeat: shouldSaveHeartbeat,
        reopenAfterHeartbeat: reopenAfterHeartbeat
    };
});
