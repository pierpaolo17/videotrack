/**
 * Shared segment validation and normalisation helpers.
 *
 * This module is intentionally free of DOM and player API dependencies so the
 * same rules can be reused by YouTube, Vimeo, HTML5 and future tests.
 *
 * @module mod_videotrack/core/segment
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define([], function() {
    'use strict';

    var INTERACTION_MIN_SECONDS = 0.25;
    var SAVE_REASONS = [
        'heartbeat', 'pause', 'seek', 'ended', 'beforeunload', 'pagehide', 'tab',
        'visibilitychange', 'reaction', 'note', 'interaction'
    ];

    /**
     * Convert any candidate value to a non-negative finite second value.
     *
     * @param {*} value Candidate value.
     * @returns {number} Non-negative seconds.
     */
    function finiteSeconds(value) {
        value = Number(value);
        return Number.isFinite(value) ? Math.max(0, value) : 0;
    }

    /**
     * Normalise segment end reasons before they reach AJAX/beacon endpoints.
     *
     * @param {string} reason Candidate reason.
     * @returns {string} Whitelisted reason.
     */
    function normaliseSaveReason(reason) {
        reason = String(reason || 'interaction');
        return SAVE_REASONS.indexOf(reason) !== -1 ? reason : 'interaction';
    }

    /**
     * Clamp client-side segment times before they are sent to persistence.
     *
     * Server-side validation remains authoritative; this helper avoids sending
     * negative, non-finite, or over-duration values from UI edge cases.
     *
     * @param {*} start Segment start candidate.
     * @param {*} end Segment end candidate.
     * @param {*} duration Optional known media duration.
     * @returns {{start: number, end: number}} Clamped and rounded times.
     */
    function clampSegmentTimes(start, end, duration) {
        var max = finiteSeconds(duration);
        start = finiteSeconds(start);
        end = Math.max(start, finiteSeconds(end));
        if (max > 0) {
            start = Math.min(start, max);
            end = Math.min(end, max);
        }
        return {
            start: Math.round(start * 1000) / 1000,
            end: Math.round(end * 1000) / 1000
        };
    }

    /**
     * Ensure note/reaction interactions can persist a very short active segment.
     *
     * @param {number} start Segment start in seconds.
     * @param {*} end Candidate end in seconds.
     * @param {*} duration Optional known duration in seconds.
     * @param {string} reason Save reason.
     * @returns {number} Adjusted end time.
     */
    function calculateInteractionEnd(start, end, duration, reason) {
        start = finiteSeconds(start);
        end = finiteSeconds(end);
        if (end > start) {
            return end;
        }
        reason = normaliseSaveReason(reason);
        if (reason !== 'reaction' && reason !== 'note') {
            return end;
        }
        end = start + INTERACTION_MIN_SECONDS;
        duration = finiteSeconds(duration);
        if (duration > 0) {
            end = Math.min(end, duration);
        }
        return end;
    }

    return {
        finiteSeconds: finiteSeconds,
        normaliseSaveReason: normaliseSaveReason,
        clampSegmentTimes: clampSegmentTimes,
        calculateInteractionEnd: calculateInteractionEnd
    };
});
