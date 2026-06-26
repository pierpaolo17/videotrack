/**
 * Error classification helpers for the shared AJAX layer.
 *
 * This module is intentionally independent from core/ajax. It normalises Moodle
 * AJAX failures into compact categories so the transport layer can decide when
 * retrying or swallowing failures is safe.
 *
 * @module mod_videotrack/core/api/error
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, complexity */
define([], function() {
    'use strict';

    var ERROR_CATEGORY_TRANSIENT = 'transient';
    var ERROR_CATEGORY_AUTH = 'auth';
    var ERROR_CATEGORY_VALIDATION = 'validation';
    var ERROR_CATEGORY_CLIENT = 'client';
    var ERROR_CATEGORY_UNKNOWN = 'unknown';
    var ERROR_CATEGORY_CANCELLED = 'cancelled';
    var NETWORK_STATE_ONLINE = 'online';
    var NETWORK_STATE_OFFLINE = 'offline';
    var NETWORK_STATE_UNKNOWN = 'unknown';

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
                code === 'error:playbackrequired' || code === 'error:playbackpositionnotwatched' ||
                code === 'reactionsdisabled' || code === 'error:reactionratelimit' ||
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

    return {
        ERROR_CATEGORY_VALIDATION: ERROR_CATEGORY_VALIDATION,
        getNetworkState: getNetworkState,
        getErrorMessage: getErrorMessage,
        classifyAjaxError: classifyAjaxError,
        normaliseAjaxError: normaliseAjaxError,
        isBrowserOffline: isBrowserOffline,
        isTransientAjaxError: isTransientAjaxError
    };
});
