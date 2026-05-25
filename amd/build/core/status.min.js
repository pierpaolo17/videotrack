/**
 * Accessible transient status messages for mod_videotrack players.
 *
 * The player core keeps a small facade for compatibility; this module owns the
 * DOM creation, ARIA attributes and auto-dismiss timer so YouTube, HTML5 and
 * Vimeo use the same feedback behaviour.
 *
 * @module mod_videotrack/core/status
 */
define([], function() {
    var timerId = null;
    var announceTimerId = null;

    /**
     * Create or reuse a hidden live region for screen-reader announcements.
     *
     * @param {boolean} isError Whether assertive announcement is required.
     * @returns {HTMLElement|null} Live region element.
     */
    function getLiveRegion(isError) {
        var id = isError ? 'videotrack-status-live-assertive' : 'videotrack-status-live-polite';
        var region = document.getElementById(id);
        if (region) {
            return region;
        }

        var container = getContainer();
        if (!container) {
            return null;
        }

        region = document.createElement('div');
        region.id = id;
        region.className = 'sr-only visually-hidden videotrack-status-live-region';
        region.setAttribute('role', isError ? 'alert' : 'status');
        region.setAttribute('aria-live', isError ? 'assertive' : 'polite');
        region.setAttribute('aria-atomic', 'true');
        container.insertBefore(region, container.firstChild || null);
        return region;
    }

    /**
     * Remove a node if it is still attached to the document.
     *
     * @param {HTMLElement} node Status node.
     */
    function remove(node) {
        if (node && node.parentNode) {
            node.parentNode.removeChild(node);
        }
    }

    /**
     * Find the safest container for a status message.
     *
     * @returns {HTMLElement|null} Container element.
     */
    function getContainer() {
        return document.querySelector('.videotrack-player-shell') || document.body || null;
    }

    /**
     * Announce a message through the shared live region without creating a visible notice.
     *
     * @param {string} message Message text.
     * @param {boolean=} isError Whether assertive announcement is required.
     */
    function announce(message, isError) {
        var text = (message || '').toString().trim();
        if (!text) {
            return;
        }

        var region = getLiveRegion(!!isError);
        if (!region) {
            return;
        }

        if (announceTimerId) {
            window.clearTimeout(announceTimerId);
            announceTimerId = null;
        }

        // Clearing first makes repeated identical messages observable to assistive technology.
        region.textContent = '';
        announceTimerId = window.setTimeout(function() {
            region.textContent = text;
            announceTimerId = null;
        }, 30);
    }

    /**
     * Clear visible and hidden status messages.
     */
    function clear() {
        if (timerId) {
            window.clearTimeout(timerId);
            timerId = null;
        }
        if (announceTimerId) {
            window.clearTimeout(announceTimerId);
            announceTimerId = null;
        }
        remove(document.getElementById('videotrack-status-message'));
        var polite = document.getElementById('videotrack-status-live-polite');
        var assertive = document.getElementById('videotrack-status-live-assertive');
        if (polite) {
            polite.textContent = '';
        }
        if (assertive) {
            assertive.textContent = '';
        }
    }

    /**
     * Show a temporary accessible status message.
     *
     * @param {string} message Message text.
     * @param {boolean} isError Whether this is an error message.
     * @param {string=} dismissLabel Accessible label for dismiss button.
     * @param {number=} timeoutMs Auto-dismiss timeout in milliseconds.
     */
    function show(message, isError, dismissLabel, timeoutMs) {
        var text = (message || '').toString().trim();
        if (!text) {
            return;
        }

        var container = getContainer();
        if (!container) {
            return;
        }

        clear();

        announce(text, !!isError);

        var notice = document.createElement('div');
        notice.id = 'videotrack-status-message';
        notice.className = 'videotrack-status-message alert ' +
            (isError ? 'alert-danger' : 'alert-info') + ' alert-dismissible mt-2';
        notice.setAttribute('role', isError ? 'alert' : 'status');
        notice.setAttribute('aria-live', isError ? 'assertive' : 'polite');
        notice.setAttribute('aria-atomic', 'true');

        var span = document.createElement('span');
        span.textContent = text;
        notice.appendChild(span);

        if (dismissLabel) {
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn-close ms-2';
            button.setAttribute('aria-label', dismissLabel);
            button.addEventListener('click', function() {
                if (timerId) {
                    window.clearTimeout(timerId);
                    timerId = null;
                }
                remove(notice);
            });
            notice.appendChild(button);
        }

        container.insertBefore(notice, container.firstChild || null);

        var timeout = Number(timeoutMs);
        if (!Number.isFinite(timeout) || timeout <= 0) {
            timeout = isError ? 8000 : 5000;
        }
        timerId = window.setTimeout(function() {
            remove(notice);
            timerId = null;
        }, timeout);
    }

    return {
        announce: announce,
        clear: clear,
        show: show
    };
});
