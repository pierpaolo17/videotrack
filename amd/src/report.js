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
 * AMD module for the mod_videotrack report page.
 *
 * Handles accessible confirmation dialogs for POST actions on the report.
 *
 * @module     mod_videotrack/report
 * @copyright  2024 mod_videotrack contributors
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/log', 'core/notification', 'core/str'], function(Log, Notification, Str) {

    /**
     * Attach an accessible Moodle modal confirmation to matching forms.
     *
     * @param {string} selector CSS selector for the form(s).
     * @param {string} fallbackMessage Fallback confirmation text.
     */
    var attachConfirm = function(selector, fallbackMessage, labels) {
        labels = labels || {};
        document.querySelectorAll(selector).forEach(function(form) {
            form.addEventListener('submit', function(e) {
                if (form.dataset.confirmed === '1') {
                    return;
                }
                e.preventDefault();
                var button = form.querySelector('[data-confirm]');
                var msg = (button && button.getAttribute('data-confirm')) || fallbackMessage;

                Str.get_strings([
                    {key: 'confirm', component: 'moodle'},
                    {key: 'yes', component: 'moodle'},
                    {key: 'cancel', component: 'moodle'}
                ]).then(function(strings) {
                    return Notification.confirm(strings[0], msg, strings[1], strings[2], function() {
                        form.dataset.confirmed = '1';
                        form.submit();
                    });
                }).catch(function(error) {
                    Log.debug('mod_videotrack/report: could not load confirmation strings - ' + error);
                    return Notification.confirm(labels.confirm, msg, labels.yes, labels.cancel, function() {
                        form.dataset.confirmed = '1';
                        form.submit();
                    });
                });
            });
        });
    };

    return {
        /**
         * Initialise the report page JS.
         *
         * @param {Object} config Configuration object from PHP.
         * @param {string} config.confirmreset Localised reset confirmation message.
         * @param {string} config.confirmrecalculate Localised recalculation confirmation message.
         */
        init: function(config) {
            config = config || {};
            attachConfirm('.videotrack-reset-student-form',
                config.confirmreset, config.labels);
            attachConfirm('.videotrack-recalculate-form',
                config.confirmrecalculate, config.labels);
            Log.debug('mod_videotrack/report: initialised');
        }
    };
});
