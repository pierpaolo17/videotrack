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
define(['core/log', 'mod_videotrack/core/confirm', 'mod_videotrack/core/debug'], function(Log, Confirm, Debug) {
    'use strict';

    var openDialog = null;

    function cssEscape(value) {
        if (window.CSS && typeof window.CSS.escape === 'function') {
            return window.CSS.escape(value);
        }
        return value.replace(/"/g, '\\"');
    }

    function queryByName(container, name) {
        if (!name) {
            return null;
        }
        return container.querySelector('[name="' + cssEscape(name) + '"]');
    }

    function findPicker(element) {
        return element.closest('.videotrack-icon-picker');
    }

    function findTargetInput(element) {
        var picker = findPicker(element);
        if (!picker) {
            return null;
        }
        var form = picker.closest('form') || document;
        return queryByName(form, picker.getAttribute('data-videotrack-icon-target'));
    }

    function findTypeSelect(picker) {
        var form = picker.closest('form') || document;
        return queryByName(form, picker.getAttribute('data-videotrack-icon-type-target'));
    }

    function renderPreview(input) {
        var form = input.closest('form') || document;
        var pickers = form.querySelectorAll('.videotrack-icon-picker[data-videotrack-icon-target="' +
            cssEscape(input.name) + '"]');
        Array.prototype.forEach.call(pickers, function(picker) {
            var preview = picker.querySelector('.videotrack-icon-picker-current');
            var typeSelect = findTypeSelect(picker);
            var type = typeSelect ? typeSelect.value : 'emoji';
            if (!preview) {
                return;
            }
            preview.innerHTML = '';
            preview.classList.toggle('videotrack-icon-picker-current-empty', !input.value);
            if (!input.value) {
                preview.textContent = '—';
                return;
            }
            if (type === 'fa') {
                var icon = document.createElement('i');
                icon.className = input.value;
                icon.setAttribute('aria-hidden', 'true');
                preview.appendChild(icon);
            } else {
                preview.textContent = input.value;
            }
        });
    }

    function updateChoiceState(input) {
        if (!input || !input.name) {
            return;
        }
        var form = input.closest('form') || document;
        var pickers = form.querySelectorAll('.videotrack-icon-picker[data-videotrack-icon-target="' +
            cssEscape(input.name) + '"]');
        Array.prototype.forEach.call(pickers, function(picker) {
            var buttons = picker.querySelectorAll('.videotrack-icon-choice');
            Array.prototype.forEach.call(buttons, function(choice) {
                var selected = choice.getAttribute('data-icon-value') === input.value;
                choice.classList.toggle('active', selected);
                choice.setAttribute('aria-pressed', selected ? 'true' : 'false');
            });
        });
        renderPreview(input);
    }

    function filterDialog(picker) {
        var dialog = picker.querySelector('.videotrack-icon-picker-dialog');
        if (!dialog) {
            return;
        }
        var search = dialog.querySelector('.videotrack-icon-picker-search');
        var typeSelect = findTypeSelect(picker);
        var activeType = typeSelect ? typeSelect.value : 'emoji';
        if (activeType !== 'emoji' && activeType !== 'fa') {
            activeType = 'emoji';
        }
        var query = search ? search.value.trim().toLowerCase() : '';
        var visible = 0;
        Array.prototype.forEach.call(dialog.querySelectorAll('.videotrack-icon-picker-tab'), function(tab) {
            tab.classList.toggle('active', tab.getAttribute('data-icon-type') === activeType);
        });
        Array.prototype.forEach.call(dialog.querySelectorAll('.videotrack-icon-picker-type'), function(group) {
            var showType = group.getAttribute('data-icon-type') === activeType;
            group.hidden = !showType;
            if (!showType) {
                return;
            }
            Array.prototype.forEach.call(group.querySelectorAll('.videotrack-icon-picker-group'), function(category) {
                var categoryVisible = 0;
                Array.prototype.forEach.call(category.querySelectorAll('.videotrack-icon-choice'), function(choice) {
                    var haystack = choice.getAttribute('data-icon-search') || '';
                    var show = !query || haystack.indexOf(query) !== -1;
                    choice.hidden = !show;
                    if (show) {
                        categoryVisible++;
                        visible++;
                    }
                });
                category.hidden = categoryVisible === 0;
            });
        });
        var empty = dialog.querySelector('.videotrack-icon-picker-empty');
        if (empty) {
            empty.hidden = visible !== 0;
        }
    }

    function closePicker(picker) {
        if (!picker) {
            return;
        }
        var dialog = picker.querySelector('.videotrack-icon-picker-dialog');
        if (dialog) {
            dialog.hidden = true;
        }
        picker.classList.remove('videotrack-icon-picker-opened');
        if (openDialog === picker) {
            openDialog = null;
        }
    }

    function openPicker(picker) {
        if (!picker) {
            return;
        }
        if (openDialog && openDialog !== picker) {
            closePicker(openDialog);
        }
        var dialog = picker.querySelector('.videotrack-icon-picker-dialog');
        if (!dialog) {
            return;
        }
        dialog.hidden = false;
        picker.classList.add('videotrack-icon-picker-opened');
        openDialog = picker;
        filterDialog(picker);
        var search = dialog.querySelector('.videotrack-icon-picker-search');
        if (search) {
            search.focus();
        }
    }

    function attachIconPickers() {
        document.addEventListener('click', function(event) {
            var openButton = event.target.closest('.videotrack-icon-picker-open');
            if (openButton) {
                openPicker(findPicker(openButton));
                return;
            }
            var closeButton = event.target.closest('.videotrack-icon-picker-close');
            if (closeButton) {
                closePicker(findPicker(closeButton));
                return;
            }
            var button = event.target.closest('.videotrack-icon-choice');
            if (!button) {
                if (openDialog && !event.target.closest('.videotrack-icon-picker-panel')) {
                    closePicker(openDialog);
                }
                return;
            }
            var input = findTargetInput(button);
            if (!input) {
                return;
            }
            input.value = button.getAttribute('data-icon-value') || '';
            input.dispatchEvent(new Event('input', {bubbles: true}));
            input.dispatchEvent(new Event('change', {bubbles: true}));
            updateChoiceState(input);
            closePicker(findPicker(button));
        });
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && openDialog) {
                closePicker(openDialog);
            }
        });
        var inputs = document.querySelectorAll('.videotrack-icon-value-input');
        Array.prototype.forEach.call(inputs, function(input) {
            updateChoiceState(input);
            input.addEventListener('input', function() {
                updateChoiceState(input);
            });
            input.addEventListener('change', function() {
                updateChoiceState(input);
            });
        });
        var pickers = document.querySelectorAll('.videotrack-icon-picker');
        Array.prototype.forEach.call(pickers, function(picker) {
            var search = picker.querySelector('.videotrack-icon-picker-search');
            var typeSelect = findTypeSelect(picker);
            if (search) {
                search.addEventListener('input', function() {
                    filterDialog(picker);
                });
            }
            if (typeSelect) {
                typeSelect.addEventListener('change', function() {
                    var input = findTargetInput(picker);
                    if (input) {
                        updateChoiceState(input);
                    }
                    filterDialog(picker);
                });
            }
            Array.prototype.forEach.call(picker.querySelectorAll('.videotrack-icon-picker-tab'), function(tab) {
                tab.addEventListener('click', function() {
                    if (typeSelect) {
                        typeSelect.value = tab.getAttribute('data-icon-type') || 'emoji';
                        typeSelect.dispatchEvent(new Event('change', {bubbles: true}));
                    }
                    filterDialog(picker);
                });
            });
        });
    }

    return {
        /**
         * Initialise preset delete confirmation forms and reaction icon pickers.
         */
        init: function(config) {
            config = config || {};
            if (typeof config === 'string') {
                config = {};
            }
            if (config.confirmdelete) {
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
            }
            attachIconPickers();
            Debug.log('presetsinitialised');
        }
    };
});
