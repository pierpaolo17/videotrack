/**
 * Transport helpers for the mod_videotrack AJAX hardening layer.
 *
 * @module mod_videotrack/core/api/transport
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, promise/always-return, promise/catch-or-return */
define([
    'core/ajax',
    'mod_videotrack/core/api/error'
], function(Ajax, AjaxError) {
    'use strict';

    var AJAX_TIMEOUT_MS = 15000; // Finite timeout so pending AJAX promises cannot block UI state indefinitely.

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
            return Promise.reject(AjaxError.normaliseAjaxError(error));
        });
    }

    /**
     * Send a Moodle AJAX request and apply the shared caller-side timeout guard.
     *
     * @param {string} methodname Validated Moodle external function name.
     * @param {Object} args Validated request arguments.
     * @param {number=} timeout Timeout in milliseconds.
     * @returns {Promise}
     */
    function send(methodname, args, timeout) {
        var promise;
        try {
            promise = Ajax.call([{methodname: methodname, args: args}])[0];
        } catch (error) {
            promise = Promise.reject(error);
        }
        return withTimeout(Promise.resolve(promise), timeout);
    }

    return {
        send: send,
        withTimeout: withTimeout
    };
});
