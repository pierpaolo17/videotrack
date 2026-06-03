/**
 * Shared AJAX API helpers for mod_videotrack player modules.
 *
 * @module mod_videotrack/core/api
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define([
    'core/ajax',
    'core/log',
    'mod_videotrack/core/segment'
], function(Ajax, Log, Segment) {
    'use strict';

    var AJAX_TIMEOUT_MS = 15000;
    var AJAX_RETRY_DELAY_MS = 750;
    var AJAX_MAX_RETRIES = 2;
    var AJAX_MAX_PAYLOAD_BYTES = 64 * 1024;
    var AJAX_MAX_STRING_ARG_LENGTH = 10000;
    var AJAX_MAX_ARG_DEPTH = 4;
    var AJAX_MAX_ARRAY_LENGTH = 100;
    var AJAX_MAX_OBJECT_KEYS = 50;
    var AJAX_MAX_OBJECT_KEY_LENGTH = 64;
    var METHOD_PREFIX = 'mod_videotrack_';
    var ALLOWED_METHODS = {
        mod_videotrack_save_segment: true,
        mod_videotrack_save_reaction: true,
        mod_videotrack_delete_reaction: true,
        mod_videotrack_save_note: true,
        mod_videotrack_delete_note: true
    };
    var ERROR_CATEGORY_TRANSIENT = 'transient';
    var ERROR_CATEGORY_AUTH = 'auth';
    var ERROR_CATEGORY_VALIDATION = 'validation';
    var ERROR_CATEGORY_CLIENT = 'client';
    var ERROR_CATEGORY_UNKNOWN = 'unknown';
    var ERROR_CATEGORY_CANCELLED = 'cancelled';
    var NETWORK_STATE_ONLINE = 'online';
    var NETWORK_STATE_OFFLINE = 'offline';
    var NETWORK_STATE_UNKNOWN = 'unknown';
    var retryCounter = 0;
    var retrySeed = 0;

    /**
     * Return the browser network state without assuming navigator support.
     *
     * @returns {string} online, offline or unknown.
     */
    function getNetworkState() {
        if (typeof window === 'undefined' || !window.navigator || typeof window.navigator.onLine === 'undefined') {
            return NETWORK_STATE_UNKNOWN;
        }
        return window.navigator.onLine ? NETWORK_STATE_ONLINE : NETWORK_STATE_OFFLINE;
    }

    /**
     * Decide whether the browser is explicitly reporting an offline state.
     *
     * @returns {boolean} True when navigator.onLine is available and false.
     */
    function isBrowserOffline() {
        return getNetworkState() === NETWORK_STATE_OFFLINE;
    }

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
     * Validate a Moodle AJAX method name before dispatching the request.
     *
     * @param {*} methodname Candidate method name.
     * @returns {string} Safe method name.
     */
    function normaliseMethodName(methodname) {
        var name = String(methodname || '');
        if (name.indexOf(METHOD_PREFIX) !== 0 || !/^mod_videotrack_[a-z0-9_]+$/.test(name) || !ALLOWED_METHODS[name]) {
            var error = new Error('invalid-method');
            error.methodname = name || methodname;
            throw error;
        }
        return name;
    }


    /**
     * Build a compact validation error with enough metadata for debugging.
     *
     * @param {string} code Stable validation error code.
     * @param {string} methodname AJAX method name.
     * @param {string=} reason Human-readable validation reason for logs.
     * @returns {Error} Error object.
     */
    function createValidationError(code, methodname, reason) {
        var error = new Error(code);
        error.methodname = methodname;
        error.reason = reason || '';
        error.errorcode = code;
        return error;
    }

    /**
     * Return true for plain serialisable argument containers.
     *
     * @param {*} value Candidate value.
     * @returns {boolean} True when value is a plain object.
     */
    function isPlainObject(value) {
        return !!value && Object.prototype.toString.call(value) === '[object Object]';
    }

    /**
     * Estimate UTF-8 payload size without relying on TextEncoder in old browsers.
     *
     * @param {string} text Text to measure.
     * @returns {number} Byte length estimate.
     */
    function getUtf8Length(text) {
        text = String(text || '');
        if (typeof window !== 'undefined' && window.TextEncoder) {
            return new window.TextEncoder().encode(text).length;
        }
        return unescape(encodeURIComponent(text)).length;
    }

    /**
     * Recursively reject obviously unsafe/non-serialisable AJAX arguments.
     *
     * @param {*} value Candidate value.
     * @param {number} depth Current recursion depth.
     * @returns {boolean} True when value is safe to JSON serialise.
     */
    function isSafeArgValue(value, depth) {
        if (depth > AJAX_MAX_ARG_DEPTH) {
            return false;
        }
        if (value === null || value === undefined) {
            return true;
        }
        if (typeof value === 'string') {
            return value.length <= AJAX_MAX_STRING_ARG_LENGTH;
        }
        if (typeof value === 'number') {
            return isFinite(value);
        }
        if (typeof value === 'boolean') {
            return true;
        }
        if (Array.isArray(value)) {
            return value.length <= AJAX_MAX_ARRAY_LENGTH && value.every(function(item) {
                return isSafeArgValue(item, depth + 1);
            });
        }
        if (isPlainObject(value)) {
            var keys = Object.keys(value);
            return keys.length <= AJAX_MAX_OBJECT_KEYS && keys.every(function(key) {
                return key.length <= AJAX_MAX_OBJECT_KEY_LENGTH && /^[a-z0-9_:-]+$/i.test(key) &&
                    isSafeArgValue(value[key], depth + 1);
            });
        }
        return false;
    }

    /**
     * Validate required numeric argument.
     *
     * @param {Object} args AJAX arguments.
     * @param {string} key Required key.
     * @returns {boolean} True when value is finite and non-negative.
     */
    function hasNonNegativeNumber(args, key) {
        var value = Number(args[key]);
        return isFinite(value) && value >= 0;
    }

    /**
     * Validate a minimal argument shape before core/ajax dispatch.
     *
     * Server-side PARAM_* validation remains authoritative. This client-side
     * check prevents malformed or oversized requests from entering the retry
     * path and makes local debugging clearer without trusting the browser.
     *
     * @param {string} methodname Safe Moodle AJAX method name.
     * @param {Object=} args Candidate argument object.
     * @param {number=} maxPayloadBytes Optional payload size limit.
     * @returns {Object} Safe argument object.
     */
    function validateArgs(methodname, args, maxPayloadBytes) {
        args = args || {};
        if (!isPlainObject(args) || !isSafeArgValue(args, 0)) {
            throw createValidationError('invalid-args', methodname, 'non-serialisable-or-too-deep');
        }

        var payload;
        try {
            payload = JSON.stringify(args);
        } catch (error) {
            throw createValidationError('invalid-args', methodname, 'json-serialisation-failed');
        }
        var limit = Number(maxPayloadBytes);
        if (!isFinite(limit) || limit <= 0) {
            limit = AJAX_MAX_PAYLOAD_BYTES;
        }
        if (getUtf8Length(payload) > limit) {
            throw createValidationError('payload-too-large', methodname, 'payload-size');
        }

        if (methodname === 'mod_videotrack_save_segment') {
            if (!hasNonNegativeNumber(args, 'cmid') || String(args.sessionid || '').length === 0 ||
                    !hasNonNegativeNumber(args, 'videotimestart') || !hasNonNegativeNumber(args, 'videotimeend') ||
                    !hasNonNegativeNumber(args, 'wallclockstart') || !hasNonNegativeNumber(args, 'wallclockend') ||
                    !hasNonNegativeNumber(args, 'durationseconds')) {
                throw createValidationError('invalid-args', methodname, 'segment-required-fields');
            }
        } else if (methodname === 'mod_videotrack_save_reaction') {
            if (!hasNonNegativeNumber(args, 'cmid') || String(args.sessionid || '').length === 0 ||
                    !hasNonNegativeNumber(args, 'videotime') || !hasNonNegativeNumber(args, 'reactionid')) {
                throw createValidationError('invalid-args', methodname, 'reaction-required-fields');
            }
        } else if (methodname === 'mod_videotrack_delete_reaction') {
            if (!hasNonNegativeNumber(args, 'cmid') || !hasNonNegativeNumber(args, 'reactioneventid')) {
                throw createValidationError('invalid-args', methodname, 'delete-reaction-required-fields');
            }
        } else if (methodname === 'mod_videotrack_save_note') {
            if (!hasNonNegativeNumber(args, 'cmid') || String(args.sessionid || '').length === 0 ||
                    !hasNonNegativeNumber(args, 'videotime') || typeof args.notetext !== 'string') {
                throw createValidationError('invalid-args', methodname, 'note-required-fields');
            }
        } else if (methodname === 'mod_videotrack_delete_note') {
            if (!hasNonNegativeNumber(args, 'cmid') || !hasNonNegativeNumber(args, 'noteeventid')) {
                throw createValidationError('invalid-args', methodname, 'delete-note-required-fields');
            }
        }
        return args;
    }

    /**
     * Extract a stable error code from Moodle AJAX failures.
     *
     * @param {*} error Raw or normalised Moodle AJAX error.
     * @returns {string} Lowercase error code.
     */
    function getErrorCode(error) {
        if (error && error.errorcode) {
            return String(error.errorcode).toLowerCase();
        }
        if (error && error.originalError && error.originalError.errorcode) {
            return String(error.originalError.errorcode).toLowerCase();
        }
        return '';
    }

    /**
     * Extract a stable message from Moodle AJAX failures.
     *
     * @param {*} error Raw or normalised Moodle AJAX error.
     * @returns {string} Lowercase error message.
     */
    function getErrorMessage(error) {
        if (error && error.message) {
            return String(error.message).toLowerCase();
        }
        if (typeof error === 'string') {
            return error.toLowerCase();
        }
        return '';
    }


    /**
     * Extract an HTTP-like status code from AJAX/fetch failures when present.
     *
     * @param {*} error Raw or normalised Moodle AJAX error.
     * @returns {number} HTTP status code, or 0 when unavailable.
     */
    function getErrorStatus(error) {
        var status = 0;
        if (error && typeof error.status !== 'undefined') {
            status = Number(error.status);
        } else if (error && error.originalError && typeof error.originalError.status !== 'undefined') {
            status = Number(error.originalError.status);
        }
        return isFinite(status) ? status : 0;
    }

    /**
     * Classify an AJAX failure without exposing raw server details to callers.
     *
     * @param {*} error Raw or normalised Moodle AJAX error.
     * @returns {string} Error category.
     */
    function classifyAjaxError(error) {
        var code = getErrorCode(error);
        var message = getErrorMessage(error);

        var status = getErrorStatus(error);

        if (code === 'ajax-cancelled' || message === 'ajax-cancelled') {
            return ERROR_CATEGORY_CANCELLED;
        }

        if (status === 401 || status === 403 || code === 'invalidsesskey' || code === 'requireloginerror' ||
                code === 'nopermissions' || code === 'accessdenied' ||
                message.indexOf('permission') !== -1 || message.indexOf('login') !== -1) {
            return ERROR_CATEGORY_AUTH;
        }

        if ((status >= 400 && status < 500 && status !== 408 && status !== 429) ||
                code === 'invalidparameter' || code === 'invalid-method' || message === 'invalid-method' ||
                message.indexOf('invalid parameter') !== -1) {
            return ERROR_CATEGORY_VALIDATION;
        }

        if (code === 'codingerror' || message.indexOf('coding error') !== -1) {
            return ERROR_CATEGORY_CLIENT;
        }

        // Transient classification deliberately runs after auth/validation/client
        // checks so offline state never makes logical Moodle errors retryable.
        if (isBrowserOffline() || status === 408 || status === 429 || status >= 500 ||
                message === 'ajax-timeout' || code === 'servicenotavailable' ||
                code === 'networkerror' || code === 'connectionlost' ||
                message.indexOf('timeout') !== -1 || message.indexOf('network') !== -1 ||
                message.indexOf('offline') !== -1 || message.indexOf('connection') !== -1) {
            return ERROR_CATEGORY_TRANSIENT;
        }

        return ERROR_CATEGORY_UNKNOWN;
    }

    /**
     * Normalise unknown AJAX failures into a compact, log-safe error object.
     *
     * @param {*} error Raw Moodle AJAX error.
     * @returns {Error} Normalised error.
     */
    function normaliseAjaxError(error) {
        var message = 'ajax-error';
        if (error && error.errorcode) {
            message = error.errorcode;
        } else if (error && error.message) {
            message = error.message;
        } else if (typeof error === 'string') {
            message = error;
        }
        var normalised = new Error(message);
        normalised.originalError = error;
        normalised.errorcode = getErrorCode(error) || message;
        normalised.category = classifyAjaxError(normalised);
        normalised.transient = normalised.category === ERROR_CATEGORY_TRANSIENT;
        return normalised;
    }

    /**
     * Decide whether an AJAX failure is safe to retry once.
     *
     * Only short-lived transport/server availability failures are retried;
     * validation and permission errors continue to fail immediately.
     *
     * @param {*} error Normalised AJAX error.
     * @returns {boolean} True when a retry is allowed.
     */
    function isTransientAjaxError(error) {
        return classifyAjaxError(error) === ERROR_CATEGORY_TRANSIENT;
    }

    /**
     * Wait before retrying a transient AJAX failure.
     *
     * @param {number} attempt Zero-based retry attempt.
     * @param {number=} delay Base delay in milliseconds.
     * @returns {Promise<void>} Promise resolved after the retry delay.
     */
    function retryDelay(attempt, delay) {
        var base = Number(delay);
        if (!isFinite(base) || base < 0) {
            base = AJAX_RETRY_DELAY_MS;
        }
        return new Promise(function(resolve) {
            var multiplier = Math.pow(2, Math.max(0, attempt));
            var delayMs = base * multiplier;
            var jitter = getRetryJitter(Math.max(500, delayMs * 0.3));
            window.setTimeout(resolve, delayMs + jitter);
        });
    }

    /**
     * Create a lightweight request scope for suppressing stale AJAX continuations.
     *
     * core/ajax does not expose abort handles, so this scope deliberately does not
     * cancel the network request. It invalidates callbacks that resolve after the
     * player has been reinitialised or cleaned up, preventing late progress/UI
     * updates from older requests.
     *
     * @returns {Object} Mutable request scope.
     */
    function createRequestScope() {
        return {
            cancelled: false,
            serial: 0,
            reason: null,
            next: function() {
                return this.serial;
            },
            cancel: function(reason) {
                this.cancelled = true;
                this.reason = reason || 'cancelled';
                this.serial += 1;
            },
            isCurrent: function(token) {
                return !this.cancelled && token === this.serial;
            }
        };
    }

    /**
     * Return true when a request scope token can still update callers.
     *
     * @param {Object|null} scope Request scope.
     * @param {number|null} token Request token.
     * @returns {boolean} True when the continuation is current.
     */
    function isRequestCurrent(scope, token) {
        if (!scope) {
            return true;
        }
        return typeof scope.isCurrent === 'function' ? scope.isCurrent(token) : !scope.cancelled;
    }

    /**
     * Resolve stale scoped requests to null without treating them as AJAX errors.
     *
     * @param {Object|null} scope Request scope.
     * @param {number|null} token Request token.
     * @param {*} value Resolved AJAX value.
     * @returns {*} Original value, or null when stale/cancelled.
     */
    function resolveIfCurrent(scope, token, value) {
        return isRequestCurrent(scope, token) ? value : null;
    }

    /**
     * Resolve a promise with a conservative timeout.
     *
     * Moodle core/ajax does not expose AbortController handles. The timeout is
     * therefore intentionally a caller-side guard: it prevents stale UI/tracker
     * continuations from waiting indefinitely without changing the server API.
     *
     * @param {Promise} promise Promise returned by core/ajax.
     * @param {number} timeout Timeout in milliseconds.
     * @returns {Promise}
     */
    function withTimeout(promise, timeout) {
        var timeoutMs = Number(timeout);
        if (!isFinite(timeoutMs) || timeoutMs <= 0) {
            timeoutMs = AJAX_TIMEOUT_MS;
        }
        var timer = null;
        var timeoutPromise = new Promise(function(resolve, reject) {
            timer = window.setTimeout(function() {
                timer = null;
                reject(new Error('ajax-timeout'));
            }, timeoutMs);
        });
        return Promise.race([promise, timeoutPromise]).then(function(response) {
            if (timer !== null) {
                window.clearTimeout(timer);
            }
            return response;
        }, function(error) {
            if (timer !== null) {
                window.clearTimeout(timer);
            }
            return Promise.reject(normaliseAjaxError(error));
        });
    }

    /**
     * Dispatch a single Moodle AJAX request through the shared hardening layer.
     *
     * @param {string} methodname Moodle external function name.
     * @param {Object=} args Request arguments.
     * @param {Object=} options Optional handling flags.
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
            safeMethodName = normaliseMethodName(methodname);
        } catch (error) {
            return Promise.reject(error);
        }

        var safeArgs;
        try {
            safeArgs = validateArgs(safeMethodName, args || {}, options.maxPayloadBytes);
        } catch (error) {
            return Promise.reject(error);
        }

        var requestScope = options.requestScope || null;
        var requestToken = requestScope && typeof requestScope.next === 'function' ? requestScope.next() : null;

        if (options.deferWhenOffline && isBrowserOffline()) {
            Log.debug('mod_videotrack: deferred AJAX request while offline for ' + safeMethodName);
            return Promise.resolve(null);
        }

        var maxRetries = Number(options.retries);
        if (!isFinite(maxRetries) || maxRetries < 0) {
            maxRetries = 0;
        }
        maxRetries = Math.min(AJAX_MAX_RETRIES, Math.floor(maxRetries));

        function attemptRequest(attempt) {
            var promise;
            try {
                promise = Ajax.call([{methodname: safeMethodName, args: safeArgs}])[0];
            } catch (error) {
                promise = Promise.reject(error);
            }

            return withTimeout(Promise.resolve(promise), options.timeout).then(function(response) {
                return resolveIfCurrent(requestScope, requestToken, response);
            }).catch(function(error) {
                if (!isRequestCurrent(requestScope, requestToken)) {
                    return null;
                }
                if (attempt < maxRetries && isTransientAjaxError(error) && !isBrowserOffline()) {
                    Log.debug('mod_videotrack: retrying transient AJAX failure for ' + safeMethodName +
                        ' - ' + error.message);
                    return retryDelay(attempt, options.retryDelay).then(function() {
                        if (!isRequestCurrent(requestScope, requestToken)) {
                            return null;
                        }
                        return attemptRequest(attempt + 1);
                    });
                }
                throw error;
            });
        }

        return attemptRequest(0).catch(function(error) {
            if (classifyAjaxError(error) === ERROR_CATEGORY_VALIDATION && getErrorMessage(error) === 'invalid-method') {
                return Promise.reject(error);
            }
            if (options.swallowFailures) {
                Log.debug((options.errorMessage || 'mod_videotrack: AJAX request failed') +
                    ' [' + classifyAjaxError(error) + '] - ' + error.message);
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
     * @returns {Object|null} AJAX args or null when the segment is empty.
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
     * @param {Object=} options Optional handling flags.
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
            options.retries = AJAX_MAX_RETRIES;
        }
        var args = buildSegmentArgs(config, state, start, end, reason);
        if (!args) {
            return Promise.resolve(null);
        }
        return call('mod_videotrack_save_segment', args, options);
    }

    return {
        call: call,
        createRequestScope: createRequestScope,
        getNetworkState: getNetworkState,
        classifyAjaxError: classifyAjaxError,
        isBrowserOffline: isBrowserOffline,
        isTransientAjaxError: isTransientAjaxError,
        validateArgs: validateArgs,
        buildSegmentArgs: buildSegmentArgs,
        saveSegment: saveSegment
    };
});
