/**
 * Shared reaction UI state helpers for mod_videotrack player modules.
 *
 * This module owns the lightweight live-region announcements shared by all
 * concrete player modules. The concrete modules still own reaction click
 * handlers while the 1.3 branch progressively extracts reaction behaviour.
 *
 * @module mod_videotrack/core/reactions
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define([], function() {
    'use strict';

    var DEFAULT_UNAVAILABLE_ANNOUNCE_INTERVAL = 30000;
    var DEFAULT_READY_DEBOUNCE_MS = 400;
    var MIN_UNAVAILABLE_ANNOUNCE_INTERVAL = 1000;
    var MAX_UNAVAILABLE_ANNOUNCE_INTERVAL = 120000;
    var MIN_READY_DEBOUNCE_MS = 0;
    var MAX_READY_DEBOUNCE_MS = 2000;


    /**
     * Build a fresh reaction announcement state object.
     *
     * @returns {Object} Mutable reaction state used by PlayerCore helpers.
     */
    function createState() {
        return {
            timer: null,
            cssTimer: null,
            announcementTimer: null,
            readyAnnounced: false,
            lastAnnouncement: null,
            lastUnavailableAt: 0,
            unavailableInterval: DEFAULT_UNAVAILABLE_ANNOUNCE_INTERVAL,
            debounceMs: DEFAULT_READY_DEBOUNCE_MS
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
    function getStatusRegion() {
        var status = document.getElementById('videotrack-reactions-live-status');
        if (status) {
            return status;
        }
        var root = document.getElementById('videotrack-reactions');
        if (!root) {
            return null;
        }
        status = document.createElement('span');
        status.id = 'videotrack-reactions-live-status';
        status.className = 'sr-only visually-hidden';
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');
        status.setAttribute('aria-atomic', 'true');
        root.appendChild(status);
        return status;
    }

    function announceStatus(status, message, reactionState) {
        status = status || getStatusRegion();
        if (!status) {
            return;
        }
        var text = (message || '').toString();
        if (!text) {
            return;
        }
        if (reactionState && reactionState.announcementTimer) {
            window.clearTimeout(reactionState.announcementTimer);
            reactionState.announcementTimer = null;
        }
        status.textContent = '';
        var timer = window.setTimeout(function() {
            status.textContent = text;
            if (reactionState && reactionState.announcementTimer === timer) {
                reactionState.announcementTimer = null;
            }
        }, 30);
        if (reactionState) {
            reactionState.announcementTimer = timer;
        }
    }

    /**
     * Announce when reactions become available or unavailable.
     *
     * @param {boolean} available Whether reaction controls are available.
     * @param {Object} config Player configuration.
     * @param {Object} reactionState Mutable reaction announcement state.
     */
    function announceAvailability(available, config, reactionState) {
        var hint = document.getElementById('videotrack-reactions-hint');
        var status = getStatusRegion();
        if (!hint || !status || !reactionState) {
            return;
        }
        if (available) {
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
            announceStatus(status, config.reactionsreadylabel, reactionState);
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
            announceStatus(status, config.reactionunavailablelabel, reactionState);
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
        var status = getStatusRegion();
        if (!hint || !status || !reactionState) {
            return;
        }
        if (reactionState.timer) {
            window.clearTimeout(reactionState.timer);
            reactionState.timer = null;
        }
        var now = Date.now();
        var interval = reactionState.unavailableInterval || MIN_UNAVAILABLE_ANNOUNCE_INTERVAL;
        if (reactionState.lastAnnouncement === false && now - reactionState.lastUnavailableAt < interval) {
            return;
        }
        reactionState.lastAnnouncement = false;
        reactionState.lastUnavailableAt = now;
        announceStatus(status, config.reactionunavailablelabel, reactionState);
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
     * Concrete player modules still report the real playback state. Reaction
     * availability, however, depends only on the activity/account configuration,
     * so learners can react while the player is either playing or paused.
     *
     * @param {boolean} playing Whether playback is currently active.
     * @param {Object} config Player configuration.
     * @param {Object} reactionState Mutable reaction announcement state.
     * @param {Object} Ui Shared UI helper module.
     */
    function setButtons(playing, config, reactionState, Ui) {
        var available = !!(config && config.reactionsenabled);
        if (Ui && typeof Ui.setReactionButtons === 'function') {
            Ui.setReactionButtons(available, playing);
        }
        announceAvailability(available, config || {}, reactionState);
    }

    return {
        DEFAULT_UNAVAILABLE_ANNOUNCE_INTERVAL: DEFAULT_UNAVAILABLE_ANNOUNCE_INTERVAL,
        DEFAULT_READY_DEBOUNCE_MS: DEFAULT_READY_DEBOUNCE_MS,
        MIN_UNAVAILABLE_ANNOUNCE_INTERVAL: MIN_UNAVAILABLE_ANNOUNCE_INTERVAL,
        MAX_UNAVAILABLE_ANNOUNCE_INTERVAL: MAX_UNAVAILABLE_ANNOUNCE_INTERVAL,
        MIN_READY_DEBOUNCE_MS: MIN_READY_DEBOUNCE_MS,
        MAX_READY_DEBOUNCE_MS: MAX_READY_DEBOUNCE_MS,
        createState: createState,
        setButtons: setButtons,
        announceAvailability: announceAvailability,
        announceUnavailable: announceUnavailable
    };
});
