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
        if (!state || !state.playing || state.segmentstart === null || !hasPlayer) {
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

        if (!state || !state.playing || state.segmentstart === null || !hasPlayer() || shouldSkip()) {
            return Promise.resolve(false);
        }

        return saveHeartbeatIfDue(
            state,
            options.heartbeatInterval,
            options.getCurrentTime,
            options.saveSegment
        ).catch(function(error) {
            emit(state, 'heartbeat:error', {error: error});
            if (options.log && typeof options.log.debug === 'function') {
                options.log.debug(error);
            }
            return false;
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

        if (!state || !state.playing || state.segmentstart === null || !hasPlayer()) {
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

        return !!options.sendSegment(state.segmentstart, normaliseTime(end));
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
            return false;
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

        var onVisibilityChange = function() {
            if (!document.hidden) {
                return;
            }
            stop();
            emit(state, 'lifecycle:hidden', {});
            if (closeSegment) {
                closeSegment('tab');
            }
            if (onHidden) {
                onHidden();
            }
        };

        var onPageHide = function() {
            stop();
            emit(state, 'lifecycle:pagehide', {});
            if (closeSegment) {
                closeSegment('pagehide');
            }
        };

        var onBeforeUnload = function() {
            if (!state || !state.playing || state.segmentstart === null || !hasPlayer()) {
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
            return !!hasPlayer();
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

        return resolveCurrentTime(getCurrentTime, state).then(function(currentTime) {
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

    return {
        normaliseTime: normaliseTime,
        on: on,
        emit: emit,
        resolveCurrentTime: resolveCurrentTime,
        syncTime: syncTime,
        markProgrammaticSeek: markProgrammaticSeek,
        consumeProgrammaticSeek: consumeProgrammaticSeek,
        resolveSeek: resolveSeek,
        shouldStopReplay: shouldStopReplay,
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
        reopenAfterHeartbeat: reopenAfterHeartbeat,
        installLifecycleHandlers: installLifecycleHandlers,
        uninstallLifecycleHandlers: uninstallLifecycleHandlers,
        reopenAfterInteractionSave: reopenAfterInteractionSave,
        isPlayerAvailable: isPlayerAvailable,
        saveCurrentProgress: saveCurrentProgress
    };
});
