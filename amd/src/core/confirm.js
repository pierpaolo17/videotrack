// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Shared confirmation helpers for POST forms.
 *
 * @module     mod_videotrack/core/confirm
 * @copyright  2024 mod_videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define([
    'core/modal_save_cancel',
    'core/modal_events',
    'core/str',
    'core/log'
], function(ModalSaveCancel, ModalEvents, Str, Log) {
    'use strict';


    /**
     * Submit a form without re-triggering submit handlers.
     *
     * @param {HTMLFormElement} form Form to submit.
     */
    var submitForm = function(form) {
        if (!form) {
            return;
        }

        var nativeSubmit = typeof HTMLFormElement !== 'undefined' && HTMLFormElement.prototype &&
            typeof HTMLFormElement.prototype.submit === 'function' ? HTMLFormElement.prototype.submit : null;

        if (nativeSubmit) {
            nativeSubmit.call(form);
            return;
        }

        if (typeof form.submit === 'function') {
            form.submit();
        }
    };

    /**
     * Return the currently focused element when it is safe to restore focus later.
     *
     * @returns {HTMLElement|null} Element to focus after a dialog closes.
     */
    var getFocusableElement = function() {
        var element = document.activeElement;
        if (!element || element === document.body || typeof element.focus !== 'function') {
            return null;
        }
        return element;
    };

    /**
     * Restore focus without throwing when the original element was removed.
     *
     * @param {HTMLElement|null} element Element to focus.
     */
    var restoreFocus = function(element) {
        if (!element || typeof element.focus !== 'function' || !document.documentElement.contains(element)) {
            return;
        }

        window.setTimeout(function() {
            try {
                element.focus({preventScroll: true});
            } catch (error) {
                element.focus();
            }
        }, 0);
    };

    /**
     * Move focus into a Moodle modal if the modal did not already do it.
     *
     * @param {Object} modal Moodle modal instance.
     */
    var focusModal = function(modal) {
        window.setTimeout(function() {
            var root = modal && typeof modal.getRoot === 'function' ? modal.getRoot() : null;
            var target = null;
            if (root && typeof root.find === 'function') {
                target = root.find('.modal-footer .btn-primary, .modal-footer button, .modal-header button, [tabindex]:not([tabindex="-1"])')[0];
            }
            if (!target && root && root[0]) {
                target = root[0].querySelector('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])');
            }
            if (target && typeof target.focus === 'function') {
                target.focus();
            }
        }, 0);
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
     * Show an accessible inline fallback when Moodle modal creation fails.
     *
     * Browser confirm() is intentionally avoided because it is not consistently
     * exposed to assistive technologies and cannot restore focus predictably.
     *
     * @param {HTMLFormElement} form Form that requested confirmation.
     * @param {string} message Confirmation message.
     */
    var showInlineFallback = function(form, message) {
        if (!form || form.querySelector('.videotrack-confirm-fallback')) {
            return;
        }
        var notice = document.createElement('div');
        notice.className = 'videotrack-confirm-fallback alert alert-warning mt-2';
        notice.setAttribute('role', 'alert');
        notice.setAttribute('aria-live', 'assertive');
        notice.setAttribute('tabindex', '-1');
        notice.textContent = message || 'Confirmation dialog could not be opened. Please try again.';
        form.appendChild(notice);
        try {
            notice.focus({preventScroll: true});
        } catch (error) {
            notice.focus();
        }
    };

    /**
     * Show a Moodle confirmation modal and submit only after confirmation.
     *
     * @param {HTMLFormElement} form Form to submit.
     * @param {Object} options Confirmation options.
     * @param {HTMLElement|null=} focusReturnElement Element to restore focus to when cancelled.
     * @returns {Promise<void>} Promise resolved after the dialog is shown.
     */
    var showModalConfirm = function(form, options, focusReturnElement) {
        options = options || {};
        var labels = options.fallbackLabels || {};
        var message = options.message || labels.message || '';
        var submitted = false;
        var describedById = 'videotrack-confirm-body-' + Math.floor(Date.now() + Math.random() * 1000000);

        return Promise.all([
            resolveString(options.titleString || {key: 'confirm', component: 'moodle'}, labels.confirm || ''),
            resolveString(options.okString || {key: 'ok', component: 'moodle'}, labels.ok || ''),
            resolveString(options.cancelString || {key: 'cancel', component: 'moodle'}, labels.cancel || '')
        ]).then(function(strings) {
            return ModalSaveCancel.create({
                title: strings[0],
                body: message
            }).then(function(modal) {
                modal.setSaveButtonText(strings[1]);
                modal.setCancelButtonText(strings[2]);
                var root = modal.getRoot();
                if (root && typeof root.find === 'function') {
                    root.find('.modal-body').attr('id', describedById);
                    root.find('.modal-dialog, .modal-content').attr('aria-describedby', describedById);
                }
                root.on(ModalEvents.save, function(event) {
                    event.preventDefault();
                    if (form.dataset.videotrackConfirmSubmitting === '1') {
                        return;
                    }
                    form.dataset.videotrackConfirmSubmitting = '1';
                    submitted = true;
                    modal.hide();
                    submitForm(form);
                });
                if (ModalEvents.hidden) {
                    root.on(ModalEvents.hidden, function() {
                        if (!submitted) {
                            restoreFocus(focusReturnElement);
                        }
                    });
                }
                modal.show();
                focusModal(modal);
            });
        }).catch(function(error) {
            var logger = options.logger || Log;
            if (logger && typeof logger.debug === 'function') {
                logger.debug((options.logPrefix || 'mod_videotrack/core/confirm') + ': modal fallback: ' + error);
            }
            showInlineFallback(form, message);
            restoreFocus(focusReturnElement);
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
                    if (form.dataset.videotrackConfirmSubmitting === '1') {
                        return;
                    }
                    var focusReturnElement = event.submitter || getFocusableElement();
                    event.preventDefault();
                    showModalConfirm(form, options, focusReturnElement);
                });
            });
        }
    };
});
