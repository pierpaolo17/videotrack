/**
 * Provider-neutral segment lifecycle helpers for tracker modules.
 *
 * @module mod_videotrack/core/tracker/segment
 */
define([
    'mod_videotrack/core/segment',
    'mod_videotrack/core/tracker/events',
    'mod_videotrack/core/tracker/state',
    'mod_videotrack/core/tracker/time',
    'mod_videotrack/core/tracker/heartbeat'
], function(Segment, TrackerEvents, TrackerState, TrackerTime, TrackerHeartbeat) {
    'use strict';

    var emit = TrackerEvents.emit;
    var markPlaying = TrackerState.markPlaying;
    var markPaused = TrackerState.markPaused;
    var normaliseTime = TrackerState.normaliseTime;
    var getTransitionToken = TrackerState.getTransitionToken;
    var isTransitionCurrent = TrackerState.isTransitionCurrent;
    var resolveCurrentTime = TrackerTime.resolveCurrentTime;
    var resetHeartbeat = TrackerHeartbeat.resetHeartbeat;
    var safeBooleanCallback = TrackerHeartbeat.safeBooleanCallback;

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
     * @returns {{start: number, end: number, wallclockstart: number}|null} Closed segment payload.
     */
    function closeSegment(state, end) {
        if (!state || state.segmentstart === null) {
            return null;
        }
        var payload = {
            start: normaliseTime(state.segmentstart),
            end: normaliseTime(end),
            wallclockstart: Number(state.wallclockstart) || Math.floor(Date.now() / 1000)
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
     * Serialise segment persistence that mutates the open segment.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} callback Save callback.
     * @returns {Promise} Promise resolved with the callback result.
     */
    function enqueueSegmentSave(state, callback) {
        if (!state || typeof callback !== 'function') {
            return Promise.resolve(false);
        }

        var previous = state._segmentSaveQueue || Promise.resolve();
        var queued = previous.catch(function() {
            return null;
        }).then(callback);

        state._segmentSaveQueue = queued.catch(function() {
            return null;
        });

        return queued;
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
     * Close the currently open segment and persist it through the supplied callback.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getCurrentTime Function returning current media time.
     * @param {Function} saveSegment Function used to persist the closed segment.
     * @param {string} reason Save reason.
     * @param {boolean} hasPlayer Whether the concrete player is available.
     * @returns {Promise<boolean>} True when a segment was closed and queued for saving.
     */
    function closeAndSaveSegment(state, getCurrentTime, saveSegment, reason, hasPlayer) {
        return enqueueSegmentSave(state, function() {
            if (!state || state.segmentstart === null || !isPlayerAvailable(hasPlayer)) {
                return Promise.resolve(false);
            }
            if (typeof getCurrentTime !== 'function' || typeof saveSegment !== 'function') {
                return Promise.resolve(false);
            }

            var transitionToken = getTransitionToken(state);

            return resolveCurrentTime(getCurrentTime, state).then(function(currentTime) {
                if (!isTransitionCurrent(state, transitionToken)) {
                    emit(state, 'segment:skipped', {reason: 'stale-state'});
                    return false;
                }

                var closed = closeSegment(state, currentTime);
                if (!closed || closed.end <= closed.start) {
                    return false;
                }
                var saveReason = Segment.normaliseSaveReason(reason);
                return Promise.resolve(saveSegment(closed.start, closed.end, saveReason, closed.wallclockstart))
                    .then(function() {
                        emit(state, 'segment:saved', {start: closed.start, end: closed.end, reason: saveReason});
                        return true;
                    }, function(error) {
                        emit(state, 'segment:error', {start: closed.start, end: closed.end, reason: saveReason, error: error});
                        throw error;
                    });
            });
        });
    }

    /**
     * Persist the currently open segment up to a caller-supplied known-good time.
     *
     * The start/end snapshot is captured immediately, but persistence is serialised
     * through the shared segment queue. This is used before rolling back an illegal
     * forward seek: the provider may already report the forbidden target, while
     * state.lasttime/fallback still identifies the last legitimately watched point.
     *
     * @param {Object} state Mutable player state.
     * @param {number} end Known-good segment end in seconds.
     * @param {Function} saveSegment Function used to persist the captured segment.
     * @param {string} reason Segment save reason.
     * @returns {Promise} Save result, or a null-equivalent promise when no segment exists.
     */
    function saveOpenSegmentSnapshot(state, end, saveSegment, reason) {
        if (!state || state.segmentstart === null || typeof saveSegment !== 'function') {
            return Promise.resolve(null);
        }

        var start = normaliseTime(state.segmentstart);
        var knownEnd = normaliseTime(end);
        if (knownEnd <= start) {
            return Promise.resolve(null);
        }
        var saveReason = Segment.normaliseSaveReason(reason);

        return enqueueSegmentSave(state, function() {
            return Promise.resolve(saveSegment(start, knownEnd, saveReason)).then(function(result) {
                emit(state, 'segment:snapshot-saved', {start: start, end: knownEnd, reason: saveReason});
                return result;
            }, function(error) {
                emit(state, 'segment:snapshot-error', {
                    start: start,
                    end: knownEnd,
                    reason: saveReason,
                    error: error
                });
                throw error;
            });
        });
    }

    /**
     * Move the open segment start after a successful interaction save.
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
     * Save progress for a currently playing segment before an interaction.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getCurrentTime Function returning the current video time.
     * @param {Function} saveSegment Function used to persist the segment.
     * @param {string} reason Save reason.
     * @param {boolean|Function} hasPlayer Whether the concrete player is available.
     * @returns {Promise} Save promise or null-equivalent promise.
     */
    function saveCurrentProgress(state, getCurrentTime, saveSegment, reason, hasPlayer) {
        return enqueueSegmentSave(state, function() {
            if (!state || !state.playing || state.segmentstart === null || !isPlayerAvailable(hasPlayer)) {
                return Promise.resolve(null);
            }
            if (typeof getCurrentTime !== 'function' || typeof saveSegment !== 'function') {
                return Promise.resolve(null);
            }

            var start = normaliseTime(state.segmentstart);
            var transitionToken = getTransitionToken(state);

            return resolveCurrentTime(getCurrentTime, state).then(function(currentTime) {
                var saveReason = Segment.normaliseSaveReason(reason);
                if (!isTransitionCurrent(state, transitionToken)) {
                    emit(state, 'progress:skipped', {reason: saveReason, start: start, end: start, stale: true});
                    return null;
                }

                var end = Segment.calculateInteractionEnd(start, currentTime, state.duration, reason);
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
        });
    }

    return {
        openSegment: openSegment,
        closeSegment: closeSegment,
        enqueueSegmentSave: enqueueSegmentSave,
        closeAndSaveSegment: closeAndSaveSegment,
        saveOpenSegmentSnapshot: saveOpenSegmentSnapshot,
        reopenAfterInteractionSave: reopenAfterInteractionSave,
        isPlayerAvailable: isPlayerAvailable,
        saveCurrentProgress: saveCurrentProgress
    };
});
