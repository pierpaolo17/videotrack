/**
 * Shared player progress helpers for mod_videotrack AMD player modules.
 *
 * This module groups the progress-related facades used by concrete player
 * implementations. It keeps core/player focused on public API wiring while
 * delegating segment clamping, progress saving and unload beacon persistence.
 *
 * @module mod_videotrack/core/player/progress
 */
define([
    'mod_videotrack/core/segment',
    'mod_videotrack/core/tracker',
    'mod_videotrack/core/beacon'
], function(Segment, Tracker, Beacon) {
    'use strict';

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
     * Persist the currently open segment with sendBeacon during page unload.
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

    return {
        clampSegmentTimes: clampSegmentTimes,
        normaliseSaveReason: normaliseSaveReason,
        saveCurrentProgress: saveCurrentProgress,
        sendBeaconSegment: sendBeaconSegment
    };
});
