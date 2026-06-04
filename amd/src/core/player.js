/**
 * Shared player UI helpers for mod_videotrack AMD player modules.
 *
 * This module contains helper functions used by the HTML5, YouTube and Vimeo
 * player entrypoints. It intentionally avoids player-API-specific code.
 *
 * @module mod_videotrack/core/player
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define([
    'mod_videotrack/core/segment',
    'mod_videotrack/core/session',
    'mod_videotrack/core/tracker',
    'mod_videotrack/core/beacon',
    'mod_videotrack/core/reactions',
    'mod_videotrack/core/player/intervalbar',
    'mod_videotrack/core/player/resume',
    'mod_videotrack/core/player/poster',
    'mod_videotrack/core/player/status',
    'mod_videotrack/core/player/notes'
], function(Segment, Session, Tracker, Beacon, Reactions, IntervalBar, Resume, Poster, PlayerStatus, PlayerNotes) {
    'use strict';


    /**
     * Backwards-compatible facade for the shared session helper.
     *
     * @returns {string} Session identifier.
     */
    function uuid() {
        return Session.uuid();
    }


    /**
     * Clamp segment times before delegating persistence.
     *
     * @param {*} start Segment start candidate.
     * @param {*} end Segment end candidate.
     * @param {*} duration Optional duration candidate.
     * @returns {{start: number, end: number}} Clamped segment times.
     */
    function clampSegmentTimes(start, end, duration) {
        return Segment.clampSegmentTimes(start, end, duration);
    }

    /**
     * Normalise a segment save reason.
     *
     * @param {string} reason Candidate save reason.
     * @returns {string} Whitelisted save reason.
     */
    function normaliseSaveReason(reason) {
        return Segment.normaliseSaveReason(reason);
    }

    /**
     * Persist the current progress before note/reaction interactions.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getCurrentTime Current-time provider.
     * @param {Function} saveSegment Segment persistence callback.
     * @param {string} reason Segment save reason.
     * @param {boolean|Function} hasPlayer Player availability flag or provider.
     * @returns {Promise} Save promise.
     */
    function saveCurrentProgress(state, getCurrentTime, saveSegment, reason, hasPlayer) {
        return Tracker.saveCurrentProgress(state, getCurrentTime, saveSegment, reason, hasPlayer);
    }

    /**
     * Read a CSS colour used by the interval canvas.
     *
     * @param {HTMLCanvasElement} canvas Canvas element.
     * @param {string} property CSS custom property name.
     * @param {string} fallback Fallback colour.
     * @returns {string} CSS colour.
     */
    function getIntervalBarColor(canvas, property, fallback) {
        return IntervalBar.getColor(canvas, property, fallback);
    }

    /**
     * Parse stored watched intervals for the interval bar.
     *
     * @param {string|Array} intervaljson JSON encoded list of [start, end] pairs.
     * @param {Object} Log Moodle log module.
     * @returns {Array} Parsed interval list.
     */
    function parseIntervals(intervaljson, Log) {
        return IntervalBar.parse(intervaljson, Log);
    }

    /**
     * Draw the watched-interval canvas and keep its text alternative in sync.
     *
     * @param {string} intervaljson JSON encoded list of [start, end] pairs.
     * @param {number} duration Video duration in seconds.
     * @param {Object} Log Moodle log module.
     */
    function updateIntervalBar(intervaljson, duration, Log) {
        IntervalBar.update(intervaljson, duration, Log);
    }


    /**
     * Persist the currently open segment with sendBeacon during page unload.
     *
     * Kept as a backwards-compatible facade for concrete player modules while
     * the implementation lives in core/beacon.
     *
     * @param {Object} config Player configuration.
     * @param {Object} state Mutable player state.
     * @param {*} start Segment start candidate.
     * @param {*} end Segment end candidate.
     * @param {Object} Utils Shared utility module.
     * @param {Object} Log Moodle log module.
     * @returns {boolean} True when the beacon was queued.
     */
    function sendBeaconSegment(config, state, start, end, Utils, Log) {
        return Beacon.sendSegment(config, state, start, end, Utils, Log);
    }


    /**
     * Show the resume-position notice.
     *
     * Kept as a backwards-compatible facade while DOM creation lives in
     * core/player/resume.
     *
     * @param {number} seconds Resume position in seconds.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     */
    function showResumeNotice(seconds, config, Utils) {
        Resume.showNotice(seconds, config, Utils);
    }

    /**
     * Configure shared player UI helpers with labels provided by PHP.
     *
     * @param {Object=} config Player configuration.
     */
    function configureStatus(config) {
        PlayerStatus.configure(config);
    }

    /**
     * Show an accessible temporary status message in the player shell.
     *
     * @param {string} message Message text.
     * @param {boolean} isError Whether the message should be announced as an error.
     * @param {string} dismissLabel Accessible label for the optional dismiss button.
     * @param {number=} timeoutMs Optional auto-dismiss timeout in milliseconds.
     */
    function showStatusMessage(message, isError, dismissLabel, timeoutMs) {
        PlayerStatus.showMessage(message, isError, dismissLabel, timeoutMs);
    }

    /**
     * Show a user-safe error status message without exposing low-level AJAX details.
     *
     * @param {*} error Raw or normalised error object.
     * @param {string} fallbackMessage Localised generic error message.
     * @param {string} dismissLabel Accessible dismiss label.
     * @param {number=} timeoutMs Optional auto-dismiss timeout in milliseconds.
     */
    function showErrorStatusMessage(error, fallbackMessage, dismissLabel, timeoutMs) {
        PlayerStatus.showErrorMessage(error, fallbackMessage, dismissLabel, timeoutMs);
    }

    /**
     * Announce a non-visual status message through the shared live region.
     *
     * @param {string} message Message text.
     * @param {boolean=} isError Whether the message should be assertive.
     */
    function announceStatusMessage(message, isError) {
        PlayerStatus.announce(message, isError);
    }


    /**
     * Announce when reactions become available or unavailable.
     *
     * Kept as a backwards-compatible facade for concrete player modules while
     * the implementation lives in core/reactions.
     *
     * @param {boolean} playing Whether playback is active.
     * @param {Object} config Player configuration.
     * @param {Object} reactionState Mutable reaction announcement state.
     */
    function announceReactionAvailability(playing, config, reactionState) {
        Reactions.announceAvailability(playing, config, reactionState);
    }

    /**
     * Announce that reactions are unavailable immediately.
     *
     * Kept as a backwards-compatible facade for concrete player modules while
     * the implementation lives in core/reactions.
     *
     * @param {Object} config Player configuration.
     * @param {Object} reactionState Mutable reaction announcement state.
     */
    function announceReactionUnavailable(config, reactionState) {
        Reactions.announceUnavailable(config, reactionState);
    }

    /**
     * Remove a poster overlay on first playback event.
     *
     * Kept as a backwards-compatible facade while poster state handling lives in
     * core/player/poster.
     *
     * @param {Event} e Custom playstate event.
     * @param {Object} state Player mutable state.
     * @param {Function} removePosterFn Callback that removes the poster overlay.
     */
    function onFirstPlay(e, state, removePosterFn) {
        Poster.onFirstPlay(e, state, removePosterFn);
    }

    /**
     * Install the personal note save/delete handlers shared by all player types.
     *
     * @param {Object} deps Dependencies and callbacks from the concrete player.
     * @param {Object} deps.Api Shared AJAX hardening module.
     * @param {Object} deps.Log Log module.
     * @param {Object} deps.Utils Utility module.
     * @param {Object} deps.config Player configuration.
     * @param {Object} deps.state Player mutable state.
     * @param {Function} deps.getCurrentVideoTime Current video time callback.
     * @param {Function} deps.saveCurrentProgress Progress persistence callback.
     */
    function installNoteHandler(deps) {
        PlayerNotes.installHandler(deps, showStatusMessage, showErrorStatusMessage);
    }


    /**
     * Remove the poster overlay with the existing fade-out transition.
     *
     * Kept as a backwards-compatible facade while DOM removal lives in
     * core/player/poster.
     *
     * @param {HTMLElement} overlay Poster overlay.
     */
    function removePoster(overlay) {
        Poster.remove(overlay);
    }

    /**
     * Returns the player shell used to scope delegated UI events.
     *
     * @param {Object} Log Optional Moodle log module.
     * @returns {HTMLElement|null} The scoped player shell, when available.
     */
    function getPlayerShell(Log) {
        return PlayerStatus.getShell(Log);
    }

    return {
        uuid: uuid,
        clampSegmentTimes: clampSegmentTimes,
        getIntervalBarColor: getIntervalBarColor,
        normaliseSaveReason: normaliseSaveReason,
        saveCurrentProgress: saveCurrentProgress,
        parseIntervals: parseIntervals,
        updateIntervalBar: updateIntervalBar,
        sendBeaconSegment: sendBeaconSegment,
        showResumeNotice: showResumeNotice,
        configureStatus: configureStatus,
        showStatusMessage: showStatusMessage,
        showErrorStatusMessage: showErrorStatusMessage,
        announceStatusMessage: announceStatusMessage,
        setNoteButtonState: PlayerNotes.setButtonState,
        announceReactionAvailability: announceReactionAvailability,
        announceReactionUnavailable: announceReactionUnavailable,
        getPlayerShell: getPlayerShell,
        onFirstPlay: onFirstPlay,
        appendNoteRow: PlayerNotes.appendRow,
        getRemainingNoteChars: PlayerNotes.getRemainingChars,
        updateNoteCharCounter: PlayerNotes.updateCharCounter,
        installNoteHandler: installNoteHandler,
        installNotesToggle: PlayerNotes.installToggle,
        removePoster: removePoster
    };
});
