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
    'mod_videotrack/core/notes',
    'mod_videotrack/core/reactions',
    'mod_videotrack/core/status',
    'mod_videotrack/core/player/intervalbar'
], function(Segment, Session, Tracker, Beacon, Notes, Reactions, Status, IntervalBar) {
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
     * @param {number} seconds Resume position in seconds.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     */
    function showResumeNotice(seconds, config, Utils) {
        var existing = document.getElementById('videotrack-resume-notice');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
        var formatted = Utils.formatSeconds(seconds);
        var notice = document.createElement('div');
        notice.id = 'videotrack-resume-notice';
        notice.className = 'videotrack-resume-notice alert alert-info alert-dismissible mt-1';
        notice.setAttribute('role', 'status');
        notice.setAttribute('aria-live', 'polite');

        var text = document.createElement('span');
        text.textContent = config.resumelabel + ' ' + formatted + '.';
        notice.appendChild(text);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-close ms-2';
        btn.setAttribute('aria-label', config.dismisslabel);
        btn.addEventListener('click', function() {
            if (notice.parentNode) {
                notice.parentNode.removeChild(notice);
            }
        });
        notice.appendChild(btn);

        var shell = document.querySelector('.videotrack-player-shell');
        var suffix = String(Math.round(seconds * 1000));
        text.id = 'videotrack-resume-notice-text-' + suffix;
        notice.setAttribute('aria-describedby', text.id);
        if (shell) {
            shell.insertBefore(notice, shell.firstChild);
        }
        // Keep the resume notice visible until the user dismisses it or starts interacting.
    }



    /**
     * Configure shared player UI helpers with labels provided by PHP.
     *
     * @param {Object=} config Player configuration.
     */
    function configureStatus(config) {
        Status.configure(config || {});
    }

    /**
     * Show an accessible temporary status message in the player shell.
     *
     * Kept as a backwards-compatible facade while timer ownership and DOM
     * creation live in core/status.
     *
     * @param {string} message Message text.
     * @param {boolean} isError Whether the message should be announced as an error.
     * @param {string} dismissLabel Accessible label for the optional dismiss button.
     * @param {number=} timeoutMs Optional auto-dismiss timeout in milliseconds.
     */
    function showStatusMessage(message, isError, dismissLabel, timeoutMs) {
        Status.show(message, isError, dismissLabel, timeoutMs, getPlayerShell());
    }

    /**
     * Show a user-safe error status message without exposing low-level AJAX details.
     *
     * Validation messages may carry intentional server-side wording, for example
     * when reactions require active playback. Transport, auth and unknown failures
     * fall back to the localised generic label supplied by the caller.
     *
     * @param {*} error Raw or normalised error object.
     * @param {string} fallbackMessage Localised generic error message.
     * @param {string} dismissLabel Accessible dismiss label.
     * @param {number=} timeoutMs Optional auto-dismiss timeout in milliseconds.
     */
    function showErrorStatusMessage(error, fallbackMessage, dismissLabel, timeoutMs) {
        var category = error && error.category ? String(error.category) : '';
        var rawMessage = error && error.message ? String(error.message).trim() : '';
        var message = fallbackMessage || rawMessage || (error && error.statuserrorlabel) || '';

        if (category === 'validation' && rawMessage && rawMessage !== 'invalid-method') {
            message = rawMessage;
        }

        Status.show(message, true, dismissLabel, timeoutMs, getPlayerShell());
    }

    /**
     * Announce a non-visual status message through the shared live region.
     *
     * @param {string} message Message text.
     * @param {boolean=} isError Whether the message should be assertive.
     */
    function announceStatusMessage(message, isError) {
        Status.announce(message, isError, getPlayerShell());
    }

    /**
     * Update the note character counter next to a textarea.
     *
     * @param {HTMLTextAreaElement} textarea Note textarea.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     * @returns {number} Remaining characters.
     */
    function updateNoteCharCounter(textarea, config, Utils) {
        return Notes.updateCharCounter(textarea, config, Utils);
    }

    /**
     * Update the enabled state of the note save button while keeping it focusable.
     *
     * @param {HTMLButtonElement} saveBtn Save button.
     * @param {boolean} playing Whether playback is active.
     */
    function setNoteButtonState(saveBtn, playing) {
        Notes.setButtonState(saveBtn, playing);
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
     * @param {Event} e Custom playstate event.
     * @param {Object} state Player mutable state.
     * @param {Function} removePosterFn Callback that removes the poster overlay.
     */
    function onFirstPlay(e, state, removePosterFn) {
        if (e.detail && e.detail.playing && !state._posterRemoved) {
            state._posterRemoved = true;
            removePosterFn();
            document.removeEventListener('videotrack:playstate', state._posterPlayListener);
            state._posterPlayListener = null;
        }
    }

    /**
     * Append a newly saved personal note to the notes list.
     *
     * @param {number} noteid Note record id.
     * @param {number} videotime Video timestamp in seconds.
     * @param {string} text Note text.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     */
    function appendNoteRow(noteid, videotime, text, config, Utils) {
        Notes.appendRow(noteid, videotime, text, config, Utils);
    }

    /**
     * Calculate remaining characters for a note textarea.
     *
     * @param {HTMLTextAreaElement} textarea Note textarea.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     * @returns {number} Remaining characters.
     */
    function getRemainingNoteChars(textarea, config, Utils) {
        return Notes.getRemainingChars(textarea, config, Utils);
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
        deps.showStatusMessage = showStatusMessage;
        deps.showErrorStatusMessage = showErrorStatusMessage;
        Notes.installHandler(deps);
    }

    /**
     * Remove the poster overlay with the existing fade-out transition.
     *
     * @param {HTMLElement} overlay Poster overlay.
     */
    function removePoster(overlay) {
        if (overlay && overlay.parentElement) {
            overlay.style.opacity = '0';
            window.setTimeout(function() {
                if (overlay && overlay.parentElement) {
                    overlay.parentElement.removeChild(overlay);
                }
            }, 300);
        }
    }


    /**
     * Install the personal notes panel collapse/expand toggle.
     *
     * @param {Object} config Player configuration.
     * @param {Object} Utils Shared utility module.
     * @param {string} contextLabel Log context used by sessionStorage helpers.
     */
    function installNotesToggle(config, Utils, contextLabel) {
        Notes.installToggle(config, Utils, contextLabel);
    }

    /**
     * Returns the player shell used to scope delegated UI events.
     *
     * Event delegation must never fall back to document because multiple
     * activities or unrelated controls can coexist on the page.
     *
     * @param {Object} Log Optional Moodle log module.
     * @returns {HTMLElement|null} The scoped player shell, when available.
     */
    function getPlayerShell(Log) {
        var shell = document.querySelector('.videotrack-player-shell');
        if (!shell && Log && Log.debug) {
            Log.debug('mod_videotrack: player shell not found; delegated handlers not installed');
        }
        return shell;
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
        setNoteButtonState: setNoteButtonState,
        announceReactionAvailability: announceReactionAvailability,
        announceReactionUnavailable: announceReactionUnavailable,
        getPlayerShell: getPlayerShell,
        onFirstPlay: onFirstPlay,
        appendNoteRow: appendNoteRow,
        getRemainingNoteChars: getRemainingNoteChars,
        updateNoteCharCounter: updateNoteCharCounter,
        installNoteHandler: installNoteHandler,
        installNotesToggle: installNotesToggle,
        removePoster: removePoster
    };
});
