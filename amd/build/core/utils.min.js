/**
 * Shared utility helpers for mod_videotrack AMD player modules.
 *
 * @module mod_videotrack/core/utils
 */
define(['core/log'], function(Log) {
    var MAX_TEXT_RESPONSE_BYTES = 1024 * 1024;
    var FETCH_TEXT_TIMEOUT_MS = 10000;


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
        if (contentType && contentType.indexOf('text/vtt') === -1 &&
                contentType.indexOf('text/plain') === -1 &&
                contentType.indexOf('application/octet-stream') === -1) {
            return Promise.reject('unexpected-content-type');
        }
        return response.text().then(function(text) {
            if (text.length > MAX_TEXT_RESPONSE_BYTES) {
                return Promise.reject('response-too-large');
            }
            return text;
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
            var path = decodeURIComponent(parsed.pathname).toLowerCase();
            var isTextTrack = /(?:^|\/)[^/?#]+\.(?:vtt|txt)$/.test(path);
            var isPluginFile = path.indexOf('/pluginfile.php/') !== -1 ||
                path.indexOf('/webservice/pluginfile.php/') !== -1;
            return isTextTrack && isPluginFile;
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
            var path = decodeURIComponent(parsed.pathname).replace(/\/+/g, '/');
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
            options.signal = controller.signal;
            var timer = window.setTimeout(function() {
                controller.abort();
            }, FETCH_TEXT_TIMEOUT_MS);
            return window.fetch(url, options)
                .then(function(response) {
                    return validateTextResponse(response);
                })
                .finally(function() {
                    window.clearTimeout(timer);
                });
        }

        // Browsers without AbortController cannot cancel the underlying fetch,
        // but the promise is still rejected after the same timeout and the timer
        // is cleared if the response wins the race.
        var timeoutId = null;
        var fetchPromise = window.fetch(url, options).then(function(response) {
            if (timeoutId !== null) {
                window.clearTimeout(timeoutId);
            }
            return validateTextResponse(response);
        });
        var timeoutPromise = new Promise(function(resolve, reject) {
            timeoutId = window.setTimeout(function() {
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
