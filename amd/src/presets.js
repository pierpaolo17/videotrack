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
 * AMD module for reaction preset management.
 *
 * @module     mod_videotrack/presets
 * @copyright  2024 mod_videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define(['core/log', 'mod_videotrack/core/confirm'], function(Log, Confirm) {
    'use strict';

    return {
        /**
         * Initialise preset delete confirmation forms.
         */
        init: function(config) {
            config = config || {};
            Confirm.attachToForms('.videotrack-delete-preset-form', {
                message: config.confirmdelete,
                okString: {key: 'delete', component: 'moodle'},
                fallbackLabels: {
                    confirm: config.confirmtitle,
                    ok: config.deletelabel,
                    cancel: config.cancellabel
                },
                logger: Log,
                logPrefix: 'mod_videotrack/presets'
            });
            Log.debug('mod_videotrack/presets: initialised');
        }
    };
});
