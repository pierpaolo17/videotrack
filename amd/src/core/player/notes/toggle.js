/**
 * Personal notes panel toggle helpers for mod_videotrack player modules.
 *
 * @module mod_videotrack/core/player/notes/toggle
 */
define([], function() {
    'use strict';

    /**
     * Install the personal notes panel collapse/expand toggle.
     *
     * Keeping this in a dedicated module avoids maintaining three copies of the
     * same DOM/sessionStorage logic in the YouTube, HTML5 and Vimeo entrypoints.
     *
     * @param {Object} config Player configuration.
     * @param {Object} Utils Shared utility module.
     * @param {string} contextLabel Log context used by sessionStorage helpers.
     */
    function install(config, Utils, contextLabel) {
        var btn = document.getElementById('videotrack-notes-toggle');
        var body = document.getElementById('videotrack-notes-body');
        if (!btn || !body) {
            return;
        }

        var key = 'videotrack_notes_collapsed_' + (config.cmid ? String(config.cmid) : 'x');
        var labelContext = contextLabel || 'notes panel state';

        /**
         * Apply the collapsed state to the notes panel and persist it.
         *
         * @param {boolean} collapsed Whether the panel should be collapsed.
         */
        function setCollapsed(collapsed) {
            body.style.display = collapsed ? 'none' : '';
            btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            var label = collapsed ? config.noteshowlabel : config.noteshidelabel;
            btn.textContent = label;
            btn.setAttribute('aria-label', label + ': ' + config.notespaneltitle);
            Utils.sessionSet(key, collapsed ? '1' : '0', labelContext);
        }

        setCollapsed(Utils.sessionGet(key, labelContext) === '1');

        var toggleClickHandler = function() {
            var isCollapsed = btn.getAttribute('aria-expanded') === 'false';
            setCollapsed(!isCollapsed);
        };
        var cleanupToggleHandler = function() {
            btn.removeEventListener('click', toggleClickHandler);
            window.removeEventListener('pagehide', cleanupToggleHandler);
            window.removeEventListener('beforeunload', cleanupToggleHandler);
        };
        btn.addEventListener('click', toggleClickHandler);
        window.addEventListener('pagehide', cleanupToggleHandler, {once: true});
        window.addEventListener('beforeunload', cleanupToggleHandler, {once: true});
    }

    return {
        install: install
    };
});
