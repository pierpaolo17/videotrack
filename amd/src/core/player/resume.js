/**
 * Resume notice helpers for shared Videotrack player UI.
 *
 * @module mod_videotrack/core/player/resume
 */
define([], function() {
    'use strict';

    /**
     * Show the resume-position notice.
     *
     * @param {number} seconds Resume position in seconds.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     */
    function showNotice(seconds, config, Utils) {
        var existing = document.getElementById('videotrack-resume-notice');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
        var formatted = Utils.formatSeconds(seconds);
        var notice = document.createElement('div');
        notice.id = 'videotrack-resume-notice';
        notice.className = 'videotrack-resume-notice videotrack-inline-notice alert alert-info mt-1';
        notice.setAttribute('role', 'status');
        notice.setAttribute('aria-live', 'polite');

        var text = document.createElement('span');
        text.textContent = config.resumelabel + ' ' + formatted + '.';
        notice.appendChild(text);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-close videotrack-inline-notice-close ms-2';
        btn.setAttribute('aria-label', config.dismisslabel);
        btn.addEventListener('click', function() {
            if (notice.parentNode) {
                notice.parentNode.removeChild(notice);
            }
        });
        notice.appendChild(btn);

        var shell = document.querySelector('.videotrack-player-shell');
        var suffix = String(Math.round(seconds * 1000));
        text.id = 'videotrack-resume-notice-text-' + suffix;
        notice.setAttribute('aria-describedby', text.id);
        if (shell) {
            shell.insertBefore(notice, shell.firstChild);
        }
        // Keep the resume notice visible until the user dismisses it or starts interacting.
    }

    return {
        showNotice: showNotice
    };
});
