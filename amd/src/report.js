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
 * AMD module for the mod_videotrack report page.
 *
 * Handles accessible confirmation dialogs for POST actions on the report.
 *
 * @module     mod_videotrack/report
 * @copyright  2024 mod_videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/log', 'mod_videotrack/core/confirm'], function(Log, Confirm) {
    'use strict';


    /**
     * Attach an accessible Moodle modal confirmation to matching forms.
     *
     * @param {string} selector CSS selector for the form(s).
     * @param {string} fallbackMessage Fallback confirmation text.
     * @param {Object} labels Fallback button labels from PHP.
     */
    var attachConfirm = function(selector, fallbackMessage, labels) {
        labels = labels || {};
        Confirm.attachToForms(selector, {
            message: fallbackMessage,
            okString: {key: 'yes', component: 'moodle'},
            fallbackLabels: {
                confirm: labels.confirm,
                ok: labels.yes,
                cancel: labels.cancel
            },
            logger: Log,
            logPrefix: 'mod_videotrack/report'
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
