/**
 * Shared utility helpers for mod_videotrack AMD player modules.
 *
 * @module mod_videotrack/core/utils
 */
define(['core/log'], function(Log) {

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
    function fetchTextWithTimeout(url) {
        var timeout = 10000;

        if (window.AbortController) {
            var controller = new AbortController();
            var timer = window.setTimeout(function() {
                controller.abort();
            }, timeout);
            return fetch(url, {signal: controller.signal, credentials: 'same-origin'})
                .then(function(response) {
                    return response.ok ? response.text() : Promise.reject(response.status);
                })
                .finally(function() {
                    window.clearTimeout(timer);
                });
        }

        // Browsers without AbortController cannot cancel the underlying fetch.
        return Promise.race([
            fetch(url, {credentials: 'same-origin'}).then(function(response) {
                return response.ok ? response.text() : Promise.reject(response.status);
            }),
            new Promise(function(resolve, reject) {
                window.setTimeout(function() {
                    reject('timeout');
                }, timeout);
            })
        ]);
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
            window.sessionStorage.setItem(key, value);
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
            value = window.sessionStorage.getItem(key);
        } catch (error) {
            Log.debug('mod_videotrack: could not read ' + (context || 'session state') + ' - ' + error);
        }
        return value;
    }

    return {
        safeInt: safeInt,
        formatSeconds: formatSeconds,
        fetchTextWithTimeout: fetchTextWithTimeout,
        sessionSet: sessionSet,
        sessionGet: sessionGet
    };
});
