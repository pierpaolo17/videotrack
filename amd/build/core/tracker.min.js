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
    'mod_videotrack/core/tracker/events',
    'mod_videotrack/core/tracker/state',
    'mod_videotrack/core/tracker/time',
    'mod_videotrack/core/tracker/heartbeat',
    'mod_videotrack/core/tracker/lifecycle',
    'mod_videotrack/core/tracker/segment'
], function(TrackerEvents, TrackerState, TrackerTime, TrackerHeartbeat, TrackerLifecycle, TrackerSegment) {
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
    var safeBooleanCallback = TrackerHeartbeat.safeBooleanCallback;

    var trackerSendUnloadBeacon = TrackerLifecycle.sendUnloadBeacon;
    var trackerInstallLifecycleHandlers = TrackerLifecycle.installLifecycleHandlers;
    var trackerUninstallLifecycleHandlers = TrackerLifecycle.uninstallLifecycleHandlers;

    var openSegment = TrackerSegment.openSegment;
    var closeSegment = TrackerSegment.closeSegment;
    var enqueueSegmentSave = TrackerSegment.enqueueSegmentSave;
    var closeAndSaveSegment = TrackerSegment.closeAndSaveSegment;
    var reopenAfterInteractionSave = TrackerSegment.reopenAfterInteractionSave;
    var isPlayerAvailable = TrackerSegment.isPlayerAvailable;
    var saveCurrentProgress = TrackerSegment.saveCurrentProgress;

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
     * @param {Object} options Beacon options.
     * @returns {boolean} True when the callback accepted the beacon.
     */
    function sendUnloadBeacon(options) {
        return trackerSendUnloadBeacon(options);
    }

    /**
     * Install shared page lifecycle handlers for player modules.
     *
     * @param {Object} options Handler options.
     * @returns {boolean} True when handlers were installed, false when already installed.
     */
    function installLifecycleHandlers(options) {
        options = options || {};
        options.cancelPendingRequests = cancelPendingRequests;
        return trackerInstallLifecycleHandlers(options);
    }

    /**
     * Remove lifecycle handlers previously installed for a player state.
     *
     * @param {Object} state Mutable player state.
     * @returns {boolean} True when handlers were removed.
     */
    function uninstallLifecycleHandlers(state) {
        return trackerUninstallLifecycleHandlers(state, cancelPendingRequests);
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
