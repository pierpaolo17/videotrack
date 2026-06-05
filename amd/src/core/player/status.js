/**
 * Scoped player status helpers for mod_videotrack.
 *
 * The module owns player-shell lookup and delegates status rendering to the
 * shared core/status module. Keeping this provider-neutral code outside
 * core/player reduces the public facade without changing player behaviour.
 *
 * @module mod_videotrack/core/player/status
 */
define(['mod_videotrack/core/status', 'mod_videotrack/core/debug'], function(Status, Debug) {
    'use strict';

    /**
     * Returns the player shell used to scope delegated UI events.
     *
     * Event delegation must never fall back to document because multiple
     * activities or unrelated controls can coexist on the page.
     *
     * @returns {HTMLElement|null} The scoped player shell, when available.
     */
    function getShell() {
        var shell = document.querySelector('.videotrack-player-shell');
        if (!shell) {
            Debug.log('playershellmissing');
        }
        return shell;
    }

    /**
     * Configure shared player UI helpers with labels provided by PHP.
     *
     * @param {Object=} config Player configuration.
     */
    function configure(config) {
        Status.configure(config || {});
    }

    /**
     * Show an accessible temporary status message in the player shell.
     *
     * @param {string} message Message text.
     * @param {boolean} isError Whether the message should be announced as an error.
     * @param {string} dismissLabel Accessible label for the optional dismiss button.
     * @param {number=} timeoutMs Optional auto-dismiss timeout in milliseconds.
     */
    function showMessage(message, isError, dismissLabel, timeoutMs) {
        Status.show(message, isError, dismissLabel, timeoutMs, getShell());
    }

    /**
     * Show a user-safe error status message without exposing low-level AJAX details.
     *
     * Validation messages may carry intentional server-side wording, for example
     * when reactions require active playback. Transport, auth and unknown failures
     * fall back to the localised generic label supplied by the caller.
     *
     * @param {*} error Raw or normalised error object.
     * @param {string} fallbackMessage Localised generic error message.
     * @param {string} dismissLabel Accessible dismiss label.
     * @param {number=} timeoutMs Optional auto-dismiss timeout in milliseconds.
     */
    function showErrorMessage(error, fallbackMessage, dismissLabel, timeoutMs) {
        var category = error && error.category ? String(error.category) : '';
        var rawMessage = error && error.message ? String(error.message).trim() : '';
        var message = fallbackMessage || rawMessage || (error && error.statuserrorlabel) || '';

        if (category === 'validation' && rawMessage && rawMessage !== 'invalid-method') {
            message = rawMessage;
        }

        Status.show(message, true, dismissLabel, timeoutMs, getShell());
    }

    /**
     * Announce a non-visual status message through the shared live region.
     *
     * @param {string} message Message text.
     * @param {boolean=} isError Whether the message should be assertive.
     */
    function announce(message, isError) {
        Status.announce(message, isError, getShell());
    }

    return {
        configure: configure,
        showMessage: showMessage,
        showErrorMessage: showErrorMessage,
        announce: announce,
        getShell: getShell
    };
});
