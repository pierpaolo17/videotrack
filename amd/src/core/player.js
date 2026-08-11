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
    'mod_videotrack/core/session',
    'mod_videotrack/core/player/intervalbar',
    'mod_videotrack/core/player/resume',
    'mod_videotrack/core/player/poster',
    'mod_videotrack/core/player/status',
    'mod_videotrack/core/player/notes',
    'mod_videotrack/core/player/bookmarks',
    'mod_videotrack/core/player/reactions',
    'mod_videotrack/core/player/progress'
], function(Session, IntervalBar, Resume, Poster, PlayerStatus, PlayerNotes, PlayerBookmarks, PlayerReactions, PlayerProgress) {
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
     * Show the activity's forward-seek policy before the learner attempts to skip.
     *
     * The configured recovery rate is included when it differs from 1x. Runtime
     * blocked-seek notices still report the actual rate used for each attempt.
     *
     * @param {Object} config Player configuration.
     */
    function showForwardSeekPolicyNotice(config) {
        if (!config || config.allowseekforward !== false || !config.seekforwarddisabledlabel) {
            return;
        }
        var message = String(config.seekforwarddisabledlabel);
        var configuredRate = Number(config.blockedseekplaybackrate) / 100;
        if (Number.isFinite(configuredRate) && Math.abs(configuredRate - 1) > 0.001 &&
                config.seekforwarddisabledspeedlabel) {
            message += ' ' + String(config.seekforwarddisabledspeedlabel).replace('__RATE__', String(configuredRate));
        }
        PlayerStatus.showPolicyMessage(message, config.dismisslabel);
    }

    /**
     * Tell a learner that a forward seek was blocked by the activity policy.
     *
     * When the configured recovery rate differs from normal playback, include
     * that rate in the same notice so the resulting player behaviour is clear.
     *
     * @param {Object} config Player configuration.
     * @param {number} playbackRate Recovery playback rate.
     */
    function showBlockedForwardSeekNotice(config, playbackRate) {
        if (!config || !config.seekforwardblockedlabel) {
            return;
        }
        var message = String(config.seekforwardblockedlabel);
        var rate = Number(playbackRate);
        if (Number.isFinite(rate) && Math.abs(rate - 1) > 0.001 && config.seekforwardblockedspeedlabel) {
            message += ' ' + String(config.seekforwardblockedspeedlabel).replace('__RATE__', String(rate));
        }
        showStatusMessage(message, false, config.dismisslabel, config.statusinfotimeoutms);
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
     * Install private bookmark save/delete handlers shared by all player types.
     *
     * @param {Object} deps Dependencies and callbacks from the concrete player.
     */
    function installBookmarkHandler(deps) {
        PlayerBookmarks.installHandler(deps, showStatusMessage, showErrorStatusMessage);
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
        clampSegmentTimes: PlayerProgress.clampSegmentTimes,
        getIntervalBarColor: getIntervalBarColor,
        normaliseSaveReason: PlayerProgress.normaliseSaveReason,
        saveCurrentProgress: PlayerProgress.saveCurrentProgress,
        parseIntervals: parseIntervals,
        updateIntervalBar: updateIntervalBar,
        sendBeaconSegment: PlayerProgress.sendBeaconSegment,
        showResumeNotice: showResumeNotice,
        configureStatus: configureStatus,
        showStatusMessage: showStatusMessage,
        showForwardSeekPolicyNotice: showForwardSeekPolicyNotice,
        showBlockedForwardSeekNotice: showBlockedForwardSeekNotice,
        showErrorStatusMessage: showErrorStatusMessage,
        announceStatusMessage: announceStatusMessage,
        setNoteButtonState: PlayerNotes.setButtonState,
        announceReactionAvailability: PlayerReactions.announceAvailability,
        announceReactionUnavailable: PlayerReactions.announceUnavailable,
        getPlayerShell: getPlayerShell,
        onFirstPlay: onFirstPlay,
        appendNoteRow: PlayerNotes.appendRow,
        getRemainingNoteChars: PlayerNotes.getRemainingChars,
        updateNoteCharCounter: PlayerNotes.updateCharCounter,
        installNoteHandler: installNoteHandler,
        installNotesToggle: PlayerNotes.installToggle,
        installBookmarkHandler: installBookmarkHandler,
        removePoster: removePoster
    };
});
