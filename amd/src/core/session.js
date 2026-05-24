/**
 * Session identity helpers for mod_videotrack player modules.
 *
 * Kept separate from core/player so identifiers can be tested and reused by
 * future APIs without pulling DOM/player UI helpers into those modules.
 *
 * @module mod_videotrack/core/session
 */
define([], function() {

    /**
     * Create a compact session identifier.
     *
     * @returns {string} Session identifier.
     */
    function uuid() {
        if (window.crypto && window.crypto.randomUUID) {
            return window.crypto.randomUUID().replace(/-/g, '');
        }
        if (window.crypto && window.crypto.getRandomValues) {
            var bytes = new Uint8Array(16);
            window.crypto.getRandomValues(bytes);
            return Array.prototype.map.call(bytes, function(byte) {
                return ('0' + byte.toString(16)).slice(-2);
            }).join('');
        }
        var entropy = '';
        while (entropy.length < 16) {
            entropy += Math.random().toString(36).substring(2);
        }
        return 'sess' + Date.now().toString(36) + entropy.substring(0, 24);
    }

    return {
        uuid: uuid
    };
});
