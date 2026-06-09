/**
 * Shared progress UI helpers for mod_videotrack player modules.
 *
 * Keeps progress percent, accessible fallback progress and interval canvas in
 * sync after save_segment/save_reaction/save_note AJAX responses.
 *
 * @module mod_videotrack/core/progress
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define([], function() {
    'use strict';


    /**
     * Return the first numeric property available on a response object.
     *
     * @param {Object} response AJAX response.
     * @param {Array} names Candidate property names.
     * @returns {number|null} Finite number or null.
     */
    function pickNumber(response, names) {
        var i;
        var value;
        if (!response) {
            return null;
        }
        for (i = 0; i < names.length; i++) {
            if (typeof response[names[i]] !== 'undefined') {
                value = Number(response[names[i]]);
                if (Number.isFinite(value)) {
                    return value;
                }
            }
        }
        return null;
    }

    /**
     * Format a percentage with a stable, compact representation.
     *
     * @param {number} percent Progress percentage.
     * @returns {string} Formatted percentage.
     */
    function formatPercent(percent) {
        percent = Math.max(0, Math.min(100, Number(percent) || 0));
        return percent.toFixed(2).replace(/\.00$/, '').replace(/(\.\d)0$/, '$1') + '%';
    }

    /**
     * Update the text percentage in the sidebar.
     *
     * @param {number} percent Progress percentage.
     */
    function updatePercentText(percent) {
        var target = document.getElementById('videotrack-progress-percent');
        if (target) {
            target.textContent = formatPercent(percent);
        }
    }

    /**
     * Update the accessible progress fallback used with the interval canvas.
     *
     * @param {number} percent Progress percentage.
     */
    function updateFallbackProgress(percent, updateStatus) {
        var fallback = document.getElementById('videotrack-interval-progress');
        var status = document.getElementById('videotrack-interval-bar-status');
        var label = formatPercent(percent);
        if (fallback) {
            fallback.value = Math.max(0, Math.min(100, Number(percent) || 0));
            fallback.textContent = label;
        }
        if (status && updateStatus !== false) {
            if (/—\s*[\d.,']+\s*%$/.test(status.textContent)) {
                status.textContent = status.textContent.replace(/—\s*[\d.,']+\s*%$/, '— ' + label);
            } else {
                status.textContent = status.textContent + ' — ' + label;
            }
        }
    }

    /**
     * Update progress-related UI from an AJAX response.
     *
     * @param {Object|null} response AJAX response.
     * @param {Object} state Mutable player state.
     * @param {Object} Utils Shared utility module.
     * @param {Object} PlayerCore Shared player core module.
     * @param {Object} Log Moodle log module.
     * @returns {Object|null} The original response for promise chaining.
     */
    function updateProgress(response, state, Utils, PlayerCore, Log) {
        var percent;
        var duration;
        var intervaljson;
        if (!response) {
            return response;
        }

        percent = pickNumber(response, ['completionpercent', 'percent', 'percentage']);
        duration = pickNumber(response, ['durationseconds', 'duration']);
        intervaljson = typeof response.intervaljson === 'string' ? response.intervaljson : null;
        if (intervaljson !== null && state) {
            state.intervaljson = intervaljson;
        }

        if (percent !== null) {
            updatePercentText(percent);
            updateFallbackProgress(percent, !intervaljson);
        }

        if (duration !== null && duration > 0 && state) {
            state.duration = duration;
        }

        duration = state && state.duration ? state.duration : duration;
        if (intervaljson && duration > 0 && PlayerCore && typeof PlayerCore.updateIntervalBar === 'function') {
            PlayerCore.updateIntervalBar(intervaljson, duration, Log);
        }

        if (Utils && typeof Utils.updateCompletionInfo === 'function') {
            Utils.updateCompletionInfo(response);
        }

        return response;
    }

    return {
        updateProgress: updateProgress,
        formatPercent: formatPercent
    };
});
