/**
 * Enables end-gated acknowledgement controls after the player reaches the final second.
 *
 * Server-side validation remains authoritative. This module only removes the initial
 * disabled state after the player has saved its natural-ended segment.
 *
 * @module mod_videotrack/core/player/acknowledgement
 */
define([], function() {
    'use strict';

    /**
     * Initialise the acknowledgement availability listener.
     *
     * @param {Object} config Element identifiers.
     */
    function init(config) {
        var checkbox = document.getElementById(String(config.checkboxid || ''));
        var button = document.getElementById(String(config.buttonid || ''));
        var pending = document.getElementById(String(config.pendingid || ''));
        var form = document.getElementById(String(config.formid || ''));
        if (!checkbox || !button || !form) {
            return;
        }

        document.addEventListener('videotrack:ended', function() {
            checkbox.disabled = false;
            checkbox.removeAttribute('aria-describedby');
            button.disabled = false;
            if (pending) {
                pending.textContent = String(config.readylabel || '');
                pending.className = 'alert alert-success';
            }
        }, {once: true});
    }

    return {init: init};
});
