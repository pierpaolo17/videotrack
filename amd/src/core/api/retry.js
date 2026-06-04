/**
 * Retry helpers for the mod_videotrack AJAX hardening layer.
 *
 * @module mod_videotrack/core/api/retry
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, no-control-regex */
define([], function() {
    'use strict';

    var AJAX_RETRY_DELAY_MS = 750; // Short base delay; delay() adds jitter to avoid synchronized retry bursts.
    var AJAX_MAX_RETRIES = 2; // Limit retries to protect Moodle from repeated writes during transient network failures.
    var retryCounter = 0;
    var retrySeed = 0;

    /**
     * Return bounded jitter for retry backoff without using the legacy pseudo-random helper.
     *
     * The value is not security-sensitive, but using Web Crypto when available
     * keeps the implementation aligned with the plugin's hardening policy.
     *
     * @param {number} max Exclusive upper bound.
     * @returns {number} Integer in the range [0, max).
     */
    function getRetryJitter(max) {
        var limit = Math.max(1, Math.floor(Number(max) || 1));
        if (typeof window !== 'undefined' && window.crypto && window.crypto.getRandomValues) {
            var values = new Uint32Array(1);
            window.crypto.getRandomValues(values);
            return values[0] % limit;
        }
        retryCounter += 1;
        if (!retrySeed) {
            var performanceOffset = (typeof window !== 'undefined' && window.performance && window.performance.now) ?
                Math.floor(window.performance.now() * 1000) : 0;
            var locationOffset = (typeof window !== 'undefined' && window.location && window.location.href) ?
                window.location.href.length * 997 : 0;
            retrySeed = Math.abs(Date.now() + performanceOffset + locationOffset) || 1;
        }
        // Deterministic fallback for legacy/non-browser contexts. It avoids the legacy
        // pseudo-random helper, but still mixes per-page timing and a module counter so
        // retry waves are less likely to align when Web Crypto is unavailable.
        retrySeed = (retrySeed * 1103515245 + 12345 + retryCounter) % 2147483647;
        return retrySeed % limit;
    }

    /**
     * Normalize a requested retry count against the plugin safety cap.
     *
     * @param {*} retries Requested retry count.
     * @returns {number} Bounded retry count.
     */
    function normalizeRetryCount(retries) {
        var maxRetries = Number(retries);
        if (!isFinite(maxRetries) || maxRetries < 0) {
            maxRetries = 0;
        }
        return Math.min(AJAX_MAX_RETRIES, Math.floor(maxRetries));
    }

    /**
     * Wait before retrying a transient AJAX failure.
     *
     * @param {number} attempt Zero-based retry attempt.
     * @param {number=} delayMs Base delay in milliseconds.
     * @returns {Promise<void>} Promise resolved after the retry delay.
     */
    function delay(attempt, delayMs) {
        var base = Number(delayMs);
        if (!isFinite(base) || base < 0) {
            base = AJAX_RETRY_DELAY_MS;
        }
        return new Promise(function(resolve) {
            var multiplier = Math.pow(2, Math.max(0, attempt));
            var calculatedDelay = base * multiplier;
            var jitter = getRetryJitter(Math.max(500, calculatedDelay * 0.3));
            window.setTimeout(resolve, calculatedDelay + jitter);
        });
    }

    return {
        MAX_RETRIES: AJAX_MAX_RETRIES,
        normalizeRetryCount: normalizeRetryCount,
        delay: delay
    };
});
