/**
 * Localised developer diagnostics for mod_videotrack AMD modules.
 *
 * Debug messages are not normally shown in the UI, but Moodle strict reviews
 * still expect diagnostic text to be centralised in language packs when it can
 * reach browser logs or developer consoles. This helper resolves diagnostic
 * strings lazily so gameplay code does not block on language loading.
 *
 * @module mod_videotrack/core/debug
 */
define(['core/log', 'core/str'], function(Log, Str) {
    'use strict';

    /**
     * Write a translated debug message to Moodle's browser logger.
     *
     * @param {string} key Language string suffix without the debug: prefix.
     * @param {Object|string|number=} data Optional placeholder data.
     * @returns {void}
     */
    function log(key, data) {
        if (!Log || typeof Log.debug !== 'function') {
            return;
        }
        Str.get_string('debug:' + key, 'mod_videotrack', data || {}).then(function(message) {
            Log.debug(message);
            return message;
        }).catch(function() {
            // Do not emit a hardcoded fallback. Missing diagnostic strings must
            // stay visible to developers through language pack checks instead.
            return null;
        });
    }

    return {
        log: log
    };
});
