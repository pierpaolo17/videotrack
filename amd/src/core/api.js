/**
 * Shared AJAX API helpers for mod_videotrack player modules.
 *
 * @module mod_videotrack/core/api
 */
define([
    'core/ajax',
    'core/log',
    'mod_videotrack/core/segment'
], function(Ajax, Log, Segment) {
    var AJAX_TIMEOUT_MS = 15000;
    var AJAX_RETRY_DELAY_MS = 750;
    var AJAX_MAX_RETRIES = 1;
    var METHOD_PREFIX = 'mod_videotrack_';

    /**
     * Validate a Moodle AJAX method name before dispatching the request.
     *
     * @param {*} methodname Candidate method name.
     * @returns {string} Safe method name.
     */
    function normaliseMethodName(methodname) {
        var name = String(methodname || '');
        if (name.indexOf(METHOD_PREFIX) !== 0 || !/^mod_videotrack_[a-z0-9_]+$/.test(name)) {
            throw new Error('invalid-method');
        }
        return name;
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
        var message = error && error.message ? String(error.message) : String(error || '');
        var code = error && error.errorcode ? String(error.errorcode) : '';
        return message === 'ajax-timeout' || code === 'servicenotavailable' || code === 'servererror' ||
            code === 'networkerror' || message.indexOf('timeout') !== -1 || message.indexOf('network') !== -1;
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
            window.setTimeout(resolve, base * Math.max(1, attempt + 1));
        });
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
     * @param {number=} options.retries Number of transient retries, capped at one.
     * @param {number=} options.retryDelay Retry delay in milliseconds.
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

        var maxRetries = Number(options.retries);
        if (!isFinite(maxRetries) || maxRetries < 0) {
            maxRetries = 0;
        }
        maxRetries = Math.min(AJAX_MAX_RETRIES, Math.floor(maxRetries));

        function attemptRequest(attempt) {
            var promise;
            try {
                promise = Ajax.call([{methodname: safeMethodName, args: args || {}}])[0];
            } catch (error) {
                promise = Promise.reject(error);
            }

            return withTimeout(Promise.resolve(promise), options.timeout).catch(function(error) {
                if (attempt < maxRetries && isTransientAjaxError(error)) {
                    Log.debug('mod_videotrack: retrying transient AJAX failure for ' + safeMethodName +
                        ' - ' + error.message);
                    return retryDelay(attempt, options.retryDelay).then(function() {
                        return attemptRequest(attempt + 1);
                    });
                }
                throw error;
            });
        }

        return attemptRequest(0).catch(function(error) {
            if (options.swallowFailures) {
                Log.debug((options.errorMessage || 'mod_videotrack: AJAX request failed') + ' - ' + error.message);
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
     * @param {number=} options.retries Number of transient retries, capped at one.
     * @param {number=} options.retryDelay Retry delay in milliseconds.
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
        isTransientAjaxError: isTransientAjaxError,
        buildSegmentArgs: buildSegmentArgs,
        saveSegment: saveSegment
    };
});
