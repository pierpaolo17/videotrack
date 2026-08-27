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
 * Present the composite VideoTrack requirement as a labelled group.
 *
 * Moodle deliberately receives one composite custom-completion rule so that
 * VideoTrack can preserve its configured AND/OR semantics. Core consequently
 * renders the rule's complete description as one list item. On the activity
 * page this module moves only the logical group label before the list and
 * leaves the component conditions in the existing item.
 *
 * @module     mod_videotrack/completion_requirements
 * @copyright  2026 VideoTrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
    'use strict';

    /**
     * Normalise rendered text for exact comparisons across theme whitespace.
     *
     * @param {*} value Candidate value.
     * @returns {string} Normalised text.
     */
    var normaliseText = function(value) {
        return value ? String(value).replace(/\s+/g, ' ').trim() : '';
    };

    /**
     * Return direct list-item children for native and ARIA lists.
     *
     * @param {Element} list List element.
     * @returns {Element[]} Direct list items.
     */
    var directListItems = function(list) {
        return Array.prototype.filter.call(list.children, function(child) {
            return child.matches('li, [role="listitem"]');
        });
    };

    /**
     * Replace the composite description in an optional accessible attribute.
     *
     * @param {Element} item Completion list item.
     * @param {string} attribute Attribute name.
     * @param {string} composite Composite description.
     * @param {string} conditions Component-condition description.
     */
    var updateAccessibleAttribute = function(item, attribute, composite, conditions) {
        var value = item.getAttribute(attribute);
        if (value && value.indexOf(composite) !== -1) {
            item.setAttribute(attribute, value.replace(composite, conditions));
        }
    };

    /**
     * Group one matching completion description without changing its status.
     *
     * @param {Element} description Description span rendered by Moodle.
     * @param {Object} config Localised display configuration.
     * @param {number} index Matching group index.
     */
    var groupDescription = function(description, config, index) {
        var item = description.closest('li, [role="listitem"]');
        var list = item ? item.closest('ul, ol, [role="list"]') : null;
        if (
            !item || !list || !list.parentNode || directListItems(list).length < 2
            || list.dataset.videotrackCompletionGrouped === '1'
        ) {
            return;
        }

        var heading = document.createElement('p');
        heading.id = 'videotrack-completion-logic-' + config.cmid + '-' + index;
        heading.className = 'videotrack-completion-logic mb-1';
        heading.textContent = config.logiclabel + ':';

        description.textContent = config.conditionsdescription;
        updateAccessibleAttribute(item, 'aria-label', config.compositedescription, config.conditionsdescription);
        updateAccessibleAttribute(item, 'title', config.compositedescription, config.conditionsdescription);

        list.parentNode.insertBefore(heading, list);
        var describedBy = normaliseText(list.getAttribute('aria-describedby'));
        list.setAttribute('aria-describedby', normaliseText(describedBy + ' ' + heading.id));
        list.dataset.videotrackCompletionGrouped = '1';
    };

    return {
        /**
         * Initialise the completion-requirements presentation.
         *
         * @param {Object} config Localised display configuration supplied by PHP.
         */
        init: function(config) {
            config = config || {};
            config.cmid = Number(config.cmid) || 0;
            config.logiclabel = normaliseText(config.logiclabel);
            config.compositedescription = normaliseText(config.compositedescription);
            config.conditionsdescription = normaliseText(config.conditionsdescription);
            if (!config.logiclabel || !config.compositedescription || !config.conditionsdescription) {
                return;
            }

            var index = 0;
            Array.prototype.forEach.call(
                document.querySelectorAll('[data-region="activity-information"] [role="listitem"] span, ' +
                    '[data-region="activity-information"] li span'),
                function(description) {
                    if (normaliseText(description.textContent) === config.compositedescription) {
                        groupDescription(description, config, index);
                        index++;
                    }
                }
            );
        }
    };
});
