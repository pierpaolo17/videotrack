/**
 * Shared utility helpers for mod_videotrack AMD player modules.
 *
 * @module mod_videotrack/core/utils
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define(['core/log'], function(Log) {
    'use strict';

    var MAX_TEXT_RESPONSE_BYTES = 1024 * 1024;
    var FETCH_TEXT_TIMEOUT_MS = 10000;
    var UNSAFE_TAG_PATTERN = /<\s*(script|iframe|object|embed|link|meta|style|svg|math)\b/;
    var UNSAFE_PROTOCOL_PATTERN = /\b(?:javascript|data)\s*:/i;
    var UNSAFE_EVENT_PATTERN = /\son[a-z]+\s*=/i;
    var ENCODED_LT_PATTERN = /&(?:#x0*3c|#0*60|lt);/i;

    /**
     * Safely parses an integer value.
     *
     * @param {*} value Value to parse.
     * @param {number} fallback Fallback when parsing fails.
     * @return {number}
     */
    function safeInt(value, fallback) {
        var parsed = parseInt(value, 10);
        return Number.isFinite(parsed) ? parsed : fallback;
    }

    /**
     * Pads a number to two digits.
     *
     * @param {number} value Value to pad.
     * @return {string}
     */
    function pad(value) {
        value = Math.max(0, Math.floor(value));
        return value < 10 ? '0' + value : '' + value;
    }

    /**
     * Formats seconds as mm:ss or h:mm:ss.
     *
     * @param {number} seconds Seconds to format.
     * @return {string}
     */
    function formatSeconds(seconds) {
        seconds = Math.max(0, Math.round(Number(seconds) || 0));
        var hours = Math.floor(seconds / 3600);
        var minutes = Math.floor((seconds % 3600) / 60);
        var secs = seconds % 60;
        if (hours > 0) {
            return hours + ':' + pad(minutes) + ':' + pad(secs);
        }
        return pad(minutes) + ':' + pad(secs);
    }

    /**
     * Reads text with a fixed timeout. Used for optional VTT resources.
     *
     * @param {string} url URL to fetch.
     * @return {Promise<string>}
     */

    /**
     * Performs conservative WebVTT validation before the content is parsed into
     * DOM text nodes. WebVTT allows cue text markup, but active content is never
     * needed by VideoTrack transcripts/chapters; reject obvious scriptable
     * payloads early and keep the raw text for the existing parser.
     *
     * @param {string} text Raw WebVTT text.
     * @return {string} Validated text.
     */

    /**
     * Decode a conservative subset of HTML entities for validation only.
     *
     * The parser still writes cue text with textContent. This helper makes sure
     * encoded active payloads such as &lt;script&gt; or javascript&#58; are rejected
     * before transcript/chapter parsing continues.
     *
     * @param {string} value Raw text.
     * @returns {string} Text with common/numeric entities decoded.
     */
    function decodeHtmlEntitiesOnce(value) {
        return String(value || '').replace(/&(#x[0-9a-f]+|#\d+|lt|gt|amp|quot|apos);/gi, function(match, entity) {
            var lower = entity.toLowerCase();
            if (lower === 'lt') { return '<'; }
            if (lower === 'gt') { return '>'; }
            if (lower === 'amp') { return '&'; }
            if (lower === 'quot') { return '"'; }
            if (lower === 'apos') { return "'"; }
            if (lower.indexOf('#x') === 0) {
                return String.fromCharCode(parseInt(lower.substring(2), 16));
            }
            if (lower.charAt(0) === '#') {
                return String.fromCharCode(parseInt(lower.substring(1), 10));
            }
            return match;
        });
    }

    function decodeHtmlEntitiesForValidation(value) {
        var decoded = String(value || '');
        // Five passes cover nested HTML entity encoding without risking an
        // unbounded loop; legitimate WebVTT captions normally need at most one.
        for (var i = 0; i < 5; i++) {
            var next = decodeHtmlEntitiesOnce(decoded);
            if (next === decoded) {
                return decoded;
            }
            decoded = next;
        }
        return decoded;
    }

    function validateWebVttText(text) {
        var normalised = String(text || '').replace(/^\uFEFF/, '');
        var trimmedStart = normalised.replace(/^\s+/, '');
        var sample = trimmedStart.substring(0, 64).toUpperCase();
        var lower = normalised.toLowerCase();
        var decodedLower = decodeHtmlEntitiesForValidation(normalised).toLowerCase();
        if (sample.indexOf('WEBVTT') !== 0 || !/^WEBVTT(?:[ \t].*)?(?:\n|$)/i.test(trimmedStart)) {
            throw 'unexpected-text-content';
        }
        if (/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/.test(normalised)) {
            throw 'unexpected-text-content';
        }
        if (normalised.length > MAX_TEXT_RESPONSE_BYTES) {
            throw 'response-too-large';
        }
        var cueCount = 0;
        var lines = normalised.replace(/\r\n?/g, '\n').split('\n');
        var timingPattern = /^\s*(?:\d{2}:)?\d{2}:\d{2}\.\d{3}\s+-->\s+(?:\d{2}:)?\d{2}:\d{2}\.\d{3}(?:\s+[^<>&]*)?\s*$/;
        for (var i = 0; i < lines.length; i++) {
            var line = lines[i];
            if (/^\s*(STYLE|REGION)\b/i.test(line)) {
                throw 'unexpected-text-content';
            }
            if (line.indexOf('-->') !== -1) {
                cueCount++;
                if (!timingPattern.test(line)) {
                    throw 'unexpected-text-content';
                }
            }
        }
        if (cueCount > 5000) {
            throw 'unexpected-text-content';
        }
        if (UNSAFE_TAG_PATTERN.test(lower) || UNSAFE_TAG_PATTERN.test(decodedLower)) {
            throw 'unexpected-text-content';
        }
        if (UNSAFE_PROTOCOL_PATTERN.test(normalised) || UNSAFE_EVENT_PATTERN.test(normalised) ||
                UNSAFE_PROTOCOL_PATTERN.test(decodedLower) || UNSAFE_EVENT_PATTERN.test(decodedLower) ||
                ENCODED_LT_PATTERN.test(normalised)) {
            throw 'unexpected-text-content';
        }
        return normalised;
    }

    function validateTextResponse(response) {
        if (!response.ok) {
            return Promise.reject(response.status);
        }
        if (response.redirected) {
            return Promise.reject('redirected-response');
        }
        if (response.url && !isSafeFetchUrl(response.url)) {
            return Promise.reject('unexpected-response-url');
        }
        var contentType = (response.headers.get('content-type') || '').toLowerCase();
        var length = parseInt(response.headers.get('content-length') || '0', 10);
        if (Number.isFinite(length) && length > MAX_TEXT_RESPONSE_BYTES) {
            return Promise.reject('response-too-large');
        }
        if (!isSafeFetchUrl(response.url || '')) {
            return Promise.reject('unexpected-response-url');
        }
        if (contentType && contentType.indexOf('text/vtt') === -1 &&
                contentType.indexOf('text/plain') === -1) {
            return Promise.reject('unexpected-content-type');
        }
        return response.text().then(function(text) {
            var sample;
            if (text.length > MAX_TEXT_RESPONSE_BYTES) {
                return Promise.reject('response-too-large');
            }
            var trimmedStart = text.replace(/^\uFEFF/, '').replace(/^\s+/, '');
            sample = trimmedStart.substring(0, 64).toUpperCase();
            if (sample.indexOf('WEBVTT') !== 0 || !/^WEBVTT(?:[ \t].*)?(?:\n|$)/i.test(trimmedStart)) {
                return Promise.reject('unexpected-text-content');
            }
            try {
                return validateWebVttText(text);
            } catch (error) {
                return Promise.reject(error);
            }
        });
    }

    function isSafeFetchUrl(url) {
        if (!url) {
            return false;
        }
        var raw = String(url).trim();
        if (raw === '' || raw.length > 2048 || /[\\\r\n]/.test(raw)) {
            return false;
        }
        try {
            var parsed = new URL(raw, window.location.href);
            if (parsed.origin !== window.location.origin ||
                    (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') ||
                    parsed.username || parsed.password || parsed.hash) {
                return false;
            }
            var path = decodeURIComponent(parsed.pathname).replace(/\\/g, '/').replace(/\/+/g, '/').toLowerCase();
            if (/(?:^|\/)\.\.(?:\/|$)/.test(path)) {
                return false;
            }
            var isPluginFile = path.indexOf('/pluginfile.php/') !== -1 ||
                path.indexOf('/webservice/pluginfile.php/') !== -1;
            return isPluginFile;
        } catch (e) {
            return false;
        }
    }

    function isSafeBeaconUrl(url) {
        if (!url) {
            return false;
        }
        var raw = String(url).trim();
        if (raw === '' || raw.length > 2048 || /[\\\r\n]/.test(raw)) {
            return false;
        }
        try {
            var parsed = new URL(raw, window.location.href);
            if (parsed.origin !== window.location.origin ||
                    (parsed.protocol !== 'http:' && parsed.protocol !== 'https:') ||
                    parsed.username || parsed.password || parsed.hash) {
                return false;
            }
            var path = decodeURIComponent(parsed.pathname).replace(/\\/g, '/').replace(/\/+/g, '/');
            if (/(?:^|\/)\.\.(?:\/|$)/.test(path)) {
                return false;
            }
            if (!/\/lib\/ajax\/service\.php$/.test(path)) {
                return false;
            }
            return parsed.searchParams.has('sesskey');
        } catch (e) {
            return false;
        }
    }

    function fetchTextWithTimeout(url) {
        if (typeof window.fetch !== 'function') {
            return Promise.reject('fetch-unavailable');
        }

        if (!isSafeFetchUrl(url)) {
            return Promise.reject('unsafe-url');
        }

        var options = {
            credentials: 'same-origin',
            mode: 'same-origin',
            redirect: 'error',
            headers: {'X-Requested-With': 'XMLHttpRequest'}
        };

        if (window.AbortController) {
            var controller = new AbortController();
            var timer = null;
            options.signal = controller.signal;
            timer = window.setTimeout(function() {
                controller.abort();
            }, FETCH_TEXT_TIMEOUT_MS);
            return window.fetch(url, options)
                .then(function(response) {
                    return validateTextResponse(response);
                })
                .catch(function(error) {
                    if (error && error.name === 'AbortError') {
                        return Promise.reject('timeout');
                    }
                    return Promise.reject(error);
                })
                .then(function(text) {
                    window.clearTimeout(timer);
                    return text;
                }, function(error) {
                    window.clearTimeout(timer);
                    return Promise.reject(error);
                });
        }

        // Browsers without AbortController cannot cancel the underlying fetch,
        // but the promise is still rejected after the same timeout and the timer
        // is cleared for both fulfilled and rejected fetch responses.
        var timeoutId = null;
        var fetchPromise = window.fetch(url, options)
            .then(function(response) {
                return validateTextResponse(response);
            })
            .then(function(text) {
                if (timeoutId !== null) {
                    window.clearTimeout(timeoutId);
                    timeoutId = null;
                }
                return text;
            }, function(error) {
                if (timeoutId !== null) {
                    window.clearTimeout(timeoutId);
                    timeoutId = null;
                }
                return Promise.reject(error);
            });
        var timeoutPromise = new Promise(function(resolve, reject) {
            timeoutId = window.setTimeout(function() {
                timeoutId = null;
                reject('timeout');
            }, FETCH_TEXT_TIMEOUT_MS);
        });
        return Promise.race([fetchPromise, timeoutPromise]);
    }

    /**
     * Stores a session value, logging failures without interrupting playback.
     *
     * @param {string} key Storage key.
     * @param {string} value Storage value.
     * @param {string} context Debug context.
     * @return {Promise<void>}
     */
    function sessionSet(key, value, context) {
        try {
            if (!key || !window.sessionStorage) {
                return Promise.resolve();
            }
            window.sessionStorage.setItem(String(key), String(value));
        } catch (error) {
            Log.debug('mod_videotrack: could not save ' + (context || 'session state') + ' - ' + error);
        }
        return Promise.resolve();
    }

    /**
     * Reads a session value, logging failures without interrupting playback.
     *
     * @param {string} key Storage key.
     * @param {string} context Debug context.
     * @return {string|null}
     */
    function sessionGet(key, context) {
        var value = null;
        try {
            if (!key || !window.sessionStorage) {
                return null;
            }
            value = window.sessionStorage.getItem(String(key));
        } catch (error) {
            Log.debug('mod_videotrack: could not read ' + (context || 'session state') + ' - ' + error);
        }
        return value;
    }

    return {
        safeInt: safeInt,
        formatSeconds: formatSeconds,
        fetchTextWithTimeout: fetchTextWithTimeout,
        isSafeBeaconUrl: isSafeBeaconUrl,
        sessionSet: sessionSet,
        sessionGet: sessionGet
    };
});
