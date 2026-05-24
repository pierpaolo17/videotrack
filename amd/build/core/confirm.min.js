// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Shared confirmation helpers for POST forms.
 *
 * @module     mod_videotrack/core/confirm
 * @copyright  2024 mod_videotrack contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
    'core/modal_factory',
    'core/modal_events',
    'core/str',
    'core/log'
], function(ModalFactory, ModalEvents, Str, Log) {

    /**
     * Submit a form without re-triggering submit handlers.
     *
     * @param {HTMLFormElement} form Form to submit.
     */
    var submitForm = function(form) {
        if (!form) {
            return;
        }

        if (typeof form.submit === 'function') {
            HTMLFormElement.prototype.submit.call(form);
        }
    };

    /**
     * Resolve a Moodle string descriptor with a safe fallback.
     *
     * @param {Object|string} descriptor String descriptor or plain string.
     * @param {string} fallback Fallback text.
     * @returns {Promise<string>} Resolved text.
     */
    var resolveString = function(descriptor, fallback) {
        if (typeof descriptor === 'string' && descriptor.length) {
            return Promise.resolve(descriptor);
        }

        if (descriptor && descriptor.key) {
            return Str.get_string(descriptor.key, descriptor.component || 'moodle')
                .catch(function() {
                    return fallback || descriptor.key;
                });
        }

        return Promise.resolve(fallback || '');
    };

    /**
     * Fallback confirmation using the browser dialog.
     *
     * @param {HTMLFormElement} form Form to submit.
     * @param {string} message Confirmation message.
     * @returns {boolean} Whether the form was submitted.
     */
    var fallbackConfirm = function(form, message) {
        if (window.confirm(message || 'Are you sure?')) {
            submitForm(form);
            return true;
        }
        return false;
    };

    /**
     * Show a Moodle confirmation modal and submit only after confirmation.
     *
     * @param {HTMLFormElement} form Form to submit.
     * @param {Object} options Confirmation options.
     * @returns {Promise<void>} Promise resolved after the dialog is shown.
     */
    var showModalConfirm = function(form, options) {
        options = options || {};
        var labels = options.fallbackLabels || {};
        var message = options.message || labels.message || '';

        return Promise.all([
            resolveString(options.titleString, labels.confirm || 'Confirm'),
            resolveString(options.okString, labels.ok || 'OK'),
            resolveString(options.cancelString, labels.cancel || 'Cancel')
        ]).then(function(strings) {
            return ModalFactory.create({
                type: ModalFactory.types.SAVE_CANCEL,
                title: strings[0],
                body: message
            }).then(function(modal) {
                modal.setSaveButtonText(strings[1]);
                modal.setCancelButtonText(strings[2]);
                modal.getRoot().on(ModalEvents.save, function(event) {
                    event.preventDefault();
                    modal.hide();
                    submitForm(form);
                });
                modal.show();
            });
        }).catch(function(error) {
            var logger = options.logger || Log;
            if (logger && typeof logger.debug === 'function') {
                logger.debug((options.logPrefix || 'mod_videotrack/core/confirm') + ': modal fallback: ' + error);
            }
            fallbackConfirm(form, message);
        });
    };

    return {
        /**
         * Attach confirmation handling to all forms matching a selector.
         *
         * @param {string} selector CSS selector for forms.
         * @param {Object} options Confirmation options.
         */
        attachToForms: function(selector, options) {
            options = options || {};
            Array.prototype.forEach.call(document.querySelectorAll(selector), function(form) {
                if (!form || form.dataset.videotrackConfirmAttached === '1') {
                    return;
                }

                form.dataset.videotrackConfirmAttached = '1';
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    showModalConfirm(form, options);
                });
            });
        }
    };
});
