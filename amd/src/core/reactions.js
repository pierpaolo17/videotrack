/**
 * Shared reaction UI state helpers for mod_videotrack player modules.
 *
 * This module owns the lightweight live-region announcements shared by all
 * concrete player modules. The concrete modules still own reaction click
 * handlers while the 1.3 branch progressively extracts reaction behaviour.
 *
 * @module mod_videotrack/core/reactions
 */
define([], function() {

    /**
     * Build a fresh reaction announcement state object.
     *
     * @returns {Object} Mutable reaction state used by PlayerCore helpers.
     */
    function createState() {
        return {
            timer: null,
            cssTimer: null,
            readyAnnounced: false,
            lastAnnouncement: null,
            lastUnavailableAt: 0,
            unavailableInterval: 60000,
            debounceMs: 500
        };
    }



    /**
     * Update the dedicated live region in a screen-reader friendly way.
     * Clearing and setting on the next tick avoids some assistive technologies
     * ignoring repeated messages with the same text.
     *
     * @param {HTMLElement} status Dedicated status live-region element.
     * @param {string} message Announcement text.
     */
    function announceStatus(status, message) {
        if (!status) {
            return;
        }
        var text = (message || '').toString();
        if (!text) {
            return;
        }
        status.textContent = '';
        window.setTimeout(function() {
            status.textContent = text;
        }, 30);
    }

    /**
     * Announce when reactions become available or unavailable.
     *
     * @param {boolean} playing Whether playback is active.
     * @param {Object} config Player configuration.
     * @param {Object} reactionState Mutable reaction announcement state.
     */
    function announceAvailability(playing, config, reactionState) {
        var hint = document.getElementById('videotrack-reactions-hint');
        var status = document.getElementById('videotrack-reactions-live-status');
        if (!hint || !status || !reactionState) {
            return;
        }
        if (playing) {
            if (reactionState.timer) {
                window.clearTimeout(reactionState.timer);
                reactionState.timer = null;
            }
            if (Date.now() - reactionState.lastUnavailableAt < reactionState.debounceMs) {
                return;
            }
            if (reactionState.readyAnnounced || reactionState.lastAnnouncement === true) {
                return;
            }
            reactionState.lastAnnouncement = true;
            reactionState.readyAnnounced = true;
            announceStatus(status, config.reactionsreadylabel);
            hint.classList.toggle('videotrack-reactions-hint-active', false);
            return;
        }

        if (reactionState.timer) {
            return;
        }
        var now = Date.now();
        if (reactionState.lastAnnouncement === false &&
                now - reactionState.lastUnavailableAt < reactionState.unavailableInterval) {
            return;
        }
        reactionState.timer = window.setTimeout(function() {
            reactionState.timer = null;
            reactionState.lastAnnouncement = false;
            reactionState.lastUnavailableAt = Date.now();
            announceStatus(status, config.reactionunavailablelabel);
            hint.classList.toggle('videotrack-reactions-hint-active', true);
        }, 400);
    }

    /**
     * Announce that reactions are unavailable immediately.
     *
     * @param {Object} config Player configuration.
     * @param {Object} reactionState Mutable reaction announcement state.
     */
    function announceUnavailable(config, reactionState) {
        var hint = document.getElementById('videotrack-reactions-hint');
        var status = document.getElementById('videotrack-reactions-live-status');
        if (!hint || !status || !reactionState) {
            return;
        }
        if (reactionState.timer) {
            window.clearTimeout(reactionState.timer);
            reactionState.timer = null;
        }
        var now = Date.now();
        if (reactionState.lastAnnouncement === false && now - reactionState.lastUnavailableAt < 1000) {
            return;
        }
        reactionState.lastAnnouncement = false;
        reactionState.lastUnavailableAt = now;
        announceStatus(status, config.reactionunavailablelabel);
        hint.classList.add('videotrack-reactions-hint-active');
        if (reactionState.cssTimer) {
            window.clearTimeout(reactionState.cssTimer);
        }
        reactionState.cssTimer = window.setTimeout(function() {
            hint.classList.remove('videotrack-reactions-hint-active');
            reactionState.cssTimer = null;
        }, 1500);
    }


    /**
     * Apply the shared reaction button state and announce availability in one call.
     *
     * Concrete player modules still decide when playback is active, but this
     * helper keeps the DOM state and live-region announcement coupled so they
     * cannot drift between YouTube, HTML5 and Vimeo implementations.
     *
     * @param {boolean} playing Whether reaction controls should be available.
     * @param {Object} config Player configuration.
     * @param {Object} reactionState Mutable reaction announcement state.
     * @param {Object} Ui Shared UI helper module.
     */
    function setButtons(playing, config, reactionState, Ui) {
        if (Ui && typeof Ui.setReactionButtons === 'function') {
            Ui.setReactionButtons(playing);
        }
        announceAvailability(playing, config || {}, reactionState);
    }

    return {
        createState: createState,
        setButtons: setButtons,
        announceAvailability: announceAvailability,
        announceUnavailable: announceUnavailable
    };
});
