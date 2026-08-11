/**
 * Shared Forum-composer launcher for VideoTrack players.
 *
 * @module mod_videotrack/core/player/forum
 */
define([], function() {
    'use strict';

    /**
     * Resolve the timestamp after an optional progress flush.
     *
     * A positive server-saved endpoint is authoritative. Zero is treated as a
     * no-op response so the current provider timestamp remains the fallback.
     *
     * @param {Object|null} progressResponse Progress persistence response.
     * @param {*} fallbackTime Current provider timestamp.
     * @return {number} Non-negative video timestamp.
     */
    function resolveForumTime(progressResponse, fallbackTime) {
        var savedEnd = progressResponse && Number(progressResponse.savedvideotimeend);
        var time = Number(fallbackTime);
        if (Number.isFinite(savedEnd) && savedEnd > 0) {
            return Math.max(0, savedEnd);
        }
        return Number.isFinite(time) ? Math.max(0, time) : 0;
    }

    /**
     * Installs the Forum button handler.
     *
     * @param {Object} options Integration options.
     * @return {Function} Cleanup callback.
     */
    function install(options) {
        var button = document.getElementById(options.buttonId || '');
        if (!button || typeof options.getCurrentTime !== 'function' || !options.composerUrl) {
            return function() {};
        }
        if (typeof button._videotrackForumCleanup === 'function') {
            button._videotrackForumCleanup();
        }
        var status = options.statusId ? document.getElementById(options.statusId) : null;
        var setBusy = function(busy) {
            button.disabled = busy;
            button.setAttribute('aria-busy', busy ? 'true' : 'false');
        };
        var handler = function() {
            if (button.disabled) {
                return;
            }
            setBusy(true);
            var progressResponse = null;
            Promise.resolve().then(function() {
                if (typeof options.saveCurrentProgress !== 'function') {
                    return null;
                }
                return options.saveCurrentProgress('interaction');
            }).then(function(response) {
                progressResponse = response;
                return Promise.resolve(options.getCurrentTime());
            }).then(function(value) {
                var time = resolveForumTime(progressResponse, value);
                if (!isFinite(time) || time < 0) {
                    throw new Error('Invalid video timestamp.');
                }
                var duration = typeof options.getDuration === 'function' ? Number(options.getDuration()) : 0;
                time = Math.round(time);
                if (isFinite(duration) && duration > 0) {
                    time = Math.min(time, Math.round(duration));
                }
                var url = new URL(options.composerUrl, window.location.href);
                url.searchParams.set('time', String(time));
                if (options.sessionId) {
                    url.searchParams.set('sessionid', String(options.sessionId));
                }
                setBusy(false);
                window.location.assign(url.toString());
            }).catch(function() {
                setBusy(false);
                if (status) {
                    status.textContent = options.errorLabel || '';
                    status.className = 'd-block text-danger small mt-1';
                    status.hidden = false;
                }
            });
        };
        var cleanup = function() {
            button.removeEventListener('click', handler);
            if (button._videotrackForumCleanup === cleanup) {
                delete button._videotrackForumCleanup;
            }
        };
        button.addEventListener('click', handler);
        button._videotrackForumCleanup = cleanup;
        return cleanup;
    }

    return {install: install};
});
