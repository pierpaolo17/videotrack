/**
 * Shared AJAX API helpers for mod_videotrack player modules.
 *
 * @module mod_videotrack/core/api
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define([
    'core/log',
    'mod_videotrack/core/api/validator',
    'mod_videotrack/core/api/error',
    'mod_videotrack/core/api/retry',
    'mod_videotrack/core/api/transport',
    'mod_videotrack/core/api/scope',
    'mod_videotrack/core/segment'
], function(Log, Validator, AjaxError, Retry, Transport, Scope, Segment) {
    'use strict';

    /**
     * Options accepted by the shared AJAX dispatch wrapper.
     *
     * @typedef {Object} RequestOptions
     * @property {number=} timeout Timeout in milliseconds.
     * @property {boolean=} swallowFailures Resolve to null on handled failures.
     * @property {string=} errorMessage Debug prefix for handled failures.
     * @property {number=} retries Number of transient retries.
     * @property {number=} retryDelay Retry delay in milliseconds.
     * @property {boolean=} deferWhenOffline Resolve to null when the browser reports offline.
     * @property {Object=} requestScope Stale-continuation guard created by createRequestScope().
     */

    /**
     * Provider-neutral watched-segment payload used by mod_videotrack_save_segment.
     *
     * @typedef {Object} SegmentArgs
     * @property {number} cmid Course module id.
     * @property {string} sessionid Tracking session id.
     * @property {number} videotimestart Segment start in seconds.
     * @property {number} videotimeend Segment end in seconds.
     * @property {number} wallclockstart Wallclock start timestamp.
     * @property {number} wallclockend Wallclock end timestamp.
     * @property {number} playbackrate Playback rate used while watching.
     * @property {string} endreason Normalised segment close reason.
     * @property {number} durationseconds Known video duration in seconds.
     */

    /**
     * Dispatch a single Moodle AJAX request through the shared hardening layer.
     *
     * @param {string} methodname Moodle external function name.
     * @param {Object=} args Request arguments.
     * @param {RequestOptions=} options Optional handling flags.
     * @param {number=} options.timeout Timeout in milliseconds.
     * @param {boolean=} options.swallowFailures Resolve to null on failure.
     * @param {string=} options.errorMessage Debug prefix for swallowed failures.
     * @param {number=} options.retries Number of transient retries, capped at two.
     * @param {number=} options.retryDelay Retry delay in milliseconds.
     * @param {boolean=} options.deferWhenOffline Resolve to null immediately when the browser reports offline.
     * @param {Object=} options.requestScope Optional stale-continuation guard.
     * @returns {Promise<Object|null>} AJAX response or null when swallowed.
     */
    function call(methodname, args, options) {
        options = options || {};
        var safeMethodName;
        try {
            safeMethodName = Validator.normaliseMethodName(methodname);
        } catch (error) {
            return Promise.reject(error);
        }

        var safeArgs;
        try {
            safeArgs = Validator.validateArgs(safeMethodName, args || {}, options.maxPayloadBytes);
        } catch (error) {
            return Promise.reject(error);
        }

        var requestScope = options.requestScope || null;
        var requestToken = Scope.nextToken(requestScope);

        if (options.deferWhenOffline && AjaxError.isBrowserOffline()) {
            Log.debug('mod_videotrack: deferred AJAX request while offline for ' + safeMethodName);
            return Promise.resolve(null);
        }

        var maxRetries = Retry.normalizeRetryCount(options.retries);

        function attemptRequest(attempt) {
            return Transport.send(safeMethodName, safeArgs, options.timeout).then(function(response) {
                return Scope.resolveIfCurrent(requestScope, requestToken, response);
            }).catch(function(error) {
                if (!Scope.isCurrent(requestScope, requestToken)) {
                    return null;
                }
                if (attempt < maxRetries && AjaxError.isTransientAjaxError(error) && !AjaxError.isBrowserOffline()) {
                    Log.debug('mod_videotrack: retrying transient AJAX failure for ' + safeMethodName +
                        ' - ' + error.message);
                    return Retry.delay(attempt, options.retryDelay).then(function() {
                        if (!Scope.isCurrent(requestScope, requestToken)) {
                            return null;
                        }
                        return attemptRequest(attempt + 1);
                    });
                }
                throw error;
            });
        }

        return attemptRequest(0).catch(function(error) {
            if (AjaxError.classifyAjaxError(error) === AjaxError.ERROR_CATEGORY_VALIDATION &&
                    AjaxError.getErrorMessage(error) === 'invalid-method') {
                return Promise.reject(error);
            }
            if (options.swallowFailures) {
                var debugContext = options.errorMessage ? String(options.errorMessage) : safeMethodName;
                Log.debug(debugContext + ' [' + AjaxError.classifyAjaxError(error) + '] - ' + error.message);
                return null;
            }
            return Promise.reject(error);
        });
    }

    /**
     * Build the common save_segment payload from a concrete player state.
     *
     * @param {Object} config Player configuration.
     * @param {Object} state Mutable player state.
     * @param {*} start Segment start candidate.
     * @param {*} end Segment end candidate.
     * @param {string} reason Segment close reason.
     * @returns {SegmentArgs|null} AJAX args or null when the segment is empty.
     */
    function buildSegmentArgs(config, state, start, end, reason) {
        var now = Math.floor(Date.now() / 1000);
        var times = Segment.clampSegmentTimes(start, end, state.duration || config.duration || 0);
        if (times.end <= times.start) {
            return null;
        }
        return {
            cmid: config.cmid,
            sessionid: state.sessionid,
            videotimestart: times.start,
            videotimeend: times.end,
            wallclockstart: state.wallclockstart || now,
            wallclockend: now,
            playbackrate: state.playbackrate || 1,
            endreason: Segment.normaliseSaveReason(reason),
            durationseconds: state.duration || config.duration || 0
        };
    }

    /**
     * Persist a watched segment through Moodle AJAX.
     *
     * @param {Object} config Player configuration.
     * @param {Object} state Mutable player state.
     * @param {*} start Segment start candidate.
     * @param {*} end Segment end candidate.
     * @param {string} reason Segment close reason.
     * @param {RequestOptions=} options Optional handling flags.
     * @param {boolean=} options.swallowFailures Resolve to null on AJAX failure.
     * @param {string=} options.errorMessage Debug prefix for swallowed failures.
     * @param {number=} options.retries Number of transient retries, capped at two.
     * @param {number=} options.retryDelay Retry delay in milliseconds.
     * @param {boolean=} options.deferWhenOffline Resolve to null immediately when the browser reports offline.
     * @param {Object=} options.requestScope Optional stale-continuation guard.
     * @returns {Promise<Object|null>} AJAX response or null for empty/skipped segments.
     */
    function saveSegment(config, state, start, end, reason, options) {
        options = options || {};
        if (typeof options.retries === 'undefined') {
            options.retries = Retry.MAX_RETRIES;
        }
        var args = buildSegmentArgs(config, state, start, end, reason);
        if (!args) {
            return Promise.resolve(null);
        }
        return call('mod_videotrack_save_segment', args, options);
    }

    return {
        call: call,
        createRequestScope: Scope.createRequestScope,
        getNetworkState: AjaxError.getNetworkState,
        classifyAjaxError: AjaxError.classifyAjaxError,
        isBrowserOffline: AjaxError.isBrowserOffline,
        isTransientAjaxError: AjaxError.isTransientAjaxError,
        validateArgs: Validator.validateArgs,
        buildSegmentArgs: buildSegmentArgs,
        saveSegment: saveSegment
    };
});
