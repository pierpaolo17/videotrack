/**
 * Argument validation helpers for the shared AJAX layer.
 *
 * This module is intentionally independent from core/ajax so payload checks can
 * run before requests enter the retry/timeout path.
 *
 * @module mod_videotrack/core/api/validator
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, complexity */
define([], function() {
    'use strict';

    // Bound the encoded argument object before dispatch. The server PARAM_*
    // checks remain authoritative; this cap only keeps malformed browser-side
    // payloads out of timeout and retry queues.
    var AJAX_MAX_PAYLOAD_BYTES = 64 * 1024;

    // Text fields used by notes and tracking metadata are intentionally much
    // smaller than this limit. The higher ceiling allows translated labels and
    // optional metadata while rejecting accidental blobs.
    var AJAX_MAX_STRING_ARG_LENGTH = 10000;

    // Videotrack web service arguments are shallow records. A depth of four is
    // enough for current payloads and prevents recursive structures from making
    // client validation expensive.
    var AJAX_MAX_ARG_DEPTH = 4;

    // Lists sent by the plugin are small batches or UI selections, not exports.
    // The cap protects retries from amplifying unexpectedly large arrays.
    var AJAX_MAX_ARRAY_LENGTH = 100;

    // Argument objects use known Moodle parameter names. Limiting breadth and
    // key length keeps validation cost predictable before core/ajax is called.
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

    return {
        normaliseMethodName: normaliseMethodName,
        validateArgs: validateArgs
    };
});
