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

        if (timerId) {
            window.clearTimeout(timerId);
            timerId = null;
        }

        var existing = document.getElementById('videotrack-status-message');
        remove(existing);

        var notice = document.createElement('div');
        notice.id = 'videotrack-status-message';
        notice.className = 'videotrack-status-message alert ' +
            (isError ? 'alert-danger' : 'alert-info') + ' alert-dismissible mt-2';
        notice.setAttribute('role', isError ? 'alert' : 'status');
        notice.setAttribute('aria-live', isError ? 'assertive' : 'polite');

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
        show: show
    };
});
