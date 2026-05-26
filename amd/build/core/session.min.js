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
        // Last-resort fallback for legacy browsers without Web Crypto.
        // This identifier is not used as an authentication token; keep it unique
        // enough for client-side request grouping without relying on non-cryptographic randomness.
        // Keep at least 16 base36 characters after the `sess` prefix so it always
        // satisfies the server-side session id validator.
        uuid.counter = (uuid.counter || 0) + 1;
        var perf = window.performance && typeof window.performance.now === 'function' ?
            Math.floor(window.performance.now() * 1000).toString(36) : '0';
        var entropy = Date.now().toString(36) + perf + uuid.counter.toString(36);
        while (entropy.length < 16) {
            entropy += Date.now().toString(36) + uuid.counter.toString(36);
        }
        return ('sess' + entropy).substring(0, 52);
    }

    return {
        uuid: uuid
    };
});
