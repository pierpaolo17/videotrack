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
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define([
    'mod_videotrack/core/segment',
    'mod_videotrack/core/tracker/events',
    'mod_videotrack/core/tracker/state',
    'mod_videotrack/core/tracker/time',
    'mod_videotrack/core/tracker/heartbeat'
], function(Segment, TrackerEvents, TrackerState, TrackerTime, TrackerHeartbeat) {
    'use strict';

    /**
     * Mutable provider-neutral tracker state shared by concrete player modules.
     *
     * The object is intentionally open-ended because YouTube, Vimeo and HTML5
     * adapters keep provider-specific handles next to the common tracker fields.
     *
     * @typedef {Object} TrackerState
     * @property {number|null} segmentstart Start of the currently open segment.
     * @property {number|null} wallclockstart Wallclock timestamp for the open segment.
     * @property {boolean} playing Whether playback is currently active.
     * @property {number} duration Known video duration in seconds.
     * @property {string=} trackerstate Current normalised tracker lifecycle state.
     * @property {Object=} events State-bound event bus.
     * @property {Object=} ajaxRequestScope Stale-continuation guard for AJAX requests.
     */

    /**
     * Persist a segment that has already been closed by the tracker.
     *
     * @callback SegmentSaveCallback
     * @param {number} start Segment start in seconds.
     * @param {number} end Segment end in seconds.
     * @param {string} reason Normalised save reason.
     * @returns {Promise|boolean|null} Save result.
     */


    var on = TrackerEvents.on;
    var once = TrackerEvents.once;
    var off = TrackerEvents.off;
    var countEvents = TrackerEvents.count;
    var clearEvents = TrackerEvents.clear;
    var emit = TrackerEvents.emit;

    var STATES = TrackerState.STATES;
    var normaliseTime = TrackerState.normaliseTime;
    var normaliseTrackerState = TrackerState.normaliseTrackerState;
    var isKnownTrackerState = TrackerState.isKnownTrackerState;
    var getTrackerState = TrackerState.getTrackerState;
    var getTransitionToken = TrackerState.getTransitionToken;
    var isTransitionCurrent = TrackerState.isTransitionCurrent;
    var canTransition = TrackerState.canTransition;
    var applyTrackerStateFlags = TrackerState.applyTrackerStateFlags;
    var setTrackerState = TrackerState.setTrackerState;
    var markIdle = TrackerState.markIdle;
    var markPlaying = TrackerState.markPlaying;
    var markPaused = TrackerState.markPaused;
    var markSeeking = TrackerState.markSeeking;
    var markEnded = TrackerState.markEnded;
    var markDestroyed = TrackerState.markDestroyed;

    var syncTime = TrackerTime.syncTime;
    var resolveCurrentTime = TrackerTime.resolveCurrentTime;
    var markProgrammaticSeek = TrackerTime.markProgrammaticSeek;
    var consumeProgrammaticSeek = TrackerTime.consumeProgrammaticSeek;
    var resolveSeek = TrackerTime.resolveSeek;
    var blockSeek = TrackerTime.blockSeek;
    var clearSeekBlock = TrackerTime.clearSeekBlock;
    var shouldStopReplay = TrackerTime.shouldStopReplay;

    var captureHeartbeatSegment = TrackerHeartbeat.captureHeartbeatSegment;
    var normaliseHeartbeatInterval = TrackerHeartbeat.normaliseHeartbeatInterval;
    var pollInterval = TrackerHeartbeat.pollInterval;
    var startPolling = TrackerHeartbeat.startPolling;
    var stopPolling = TrackerHeartbeat.stopPolling;
    var resetHeartbeat = TrackerHeartbeat.resetHeartbeat;
    var shouldSaveHeartbeat = TrackerHeartbeat.shouldSaveHeartbeat;
    var trackerSaveHeartbeatIfDue = TrackerHeartbeat.saveHeartbeatIfDue;
    var trackerRunHeartbeat = TrackerHeartbeat.runHeartbeat;
    var reopenAfterHeartbeat = TrackerHeartbeat.reopenAfterHeartbeat;

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
     * @param {TrackerState} state Mutable player state.
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
     * Serialise segment persistence that mutates the open segment.
     *
     * Heartbeat, pause/seek/tab and interaction saves can be triggered very
     * close to each other. Running them in parallel lets a later request reopen
     * the segment from stale data after an earlier one has already advanced it.
     * Queueing only these tracker-level saves preserves their existing order and
     * avoids changing heartbeat frequency or the pedagogical tracking rules.
     *
     * @param {TrackerState} state Mutable player state.
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
     * Close the currently open segment and persist it through the supplied callback.
     *
     * This provider-neutral helper keeps close/persist error handling in the
     * tracker layer. The current-time callback may return either a number or a
     * Promise resolving to a number, which lets YouTube/HTML5 and Vimeo use the
     * same lifecycle path.
     *
     * @param {TrackerState} state Mutable player state.
     * @param {Function} getCurrentTime Function returning current media time.
     * @param {SegmentSaveCallback} saveSegment Function used to persist the closed segment.
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
                return Promise.resolve(saveSegment(closed.start, closed.end, saveReason))
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
     * Capture and persist a heartbeat segment when due.
     *
     * @param {Object} state Mutable player state.
     * @param {number} heartbeatInterval Heartbeat interval in seconds.
     * @param {Function} getCurrentTime Current-time provider.
     * @param {Function} saveSegment Segment persistence callback.
     * @param {number=} now Optional wallclock timestamp in seconds.
     * @returns {Promise<boolean>} True when a heartbeat segment was saved.
     */
    function saveHeartbeatIfDue(state, heartbeatInterval, getCurrentTime, saveSegment, now) {
        return trackerSaveHeartbeatIfDue(state, enqueueSegmentSave, heartbeatInterval, getCurrentTime, saveSegment, now);
    }

    /**
     * Run one provider-neutral heartbeat check.
     *
     * @param {Object} options Heartbeat options.
     * @returns {Promise<boolean>} True when a heartbeat segment was saved.
     */
    function runHeartbeat(options) {
        return trackerRunHeartbeat(options, enqueueSegmentSave);
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
     * Cancel pending request continuations associated with a player state.
     *
     * This is a cleanup guard for dynamic teardown/reinitialisation. It does not
     * abort already-dispatched Moodle AJAX calls; it prevents their late promise
     * continuations from mutating stale player state.
     *
     * @param {TrackerState} state Mutable player state.
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
        isKnownTrackerState: isKnownTrackerState,
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
        enqueueSegmentSave: enqueueSegmentSave,
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
