/**
 * Shared Forum-composer launcher for VideoTrack players.
 *
 * @module mod_videotrack/core/player/forum
 */
define([], function() {
    'use strict';

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
            Promise.resolve(options.getCurrentTime()).then(function(value) {
                var time = Number(value);
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
