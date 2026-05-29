/**
 * Session identity helpers for mod_videotrack player modules.
 *
 * Kept separate from core/player so identifiers can be tested and reused by
 * future APIs without pulling DOM/player UI helpers into those modules.
 *
 * @module mod_videotrack/core/session
 */
define([], function() {
    'use strict';


    /**
     * Create a compact session identifier.
     *
     * @security This identifier is not an authentication token. Legacy fallback
     * browsers without Web Crypto receive a deterministic uniqueness-oriented
     * identifier used only for client-side request grouping.
     * @returns {string} Session identifier.
     */
    function uuid() {
        var cryptoApi = typeof window !== 'undefined' && window.crypto ? window.crypto : null;
        if (cryptoApi && cryptoApi.randomUUID) {
            return cryptoApi.randomUUID().replace(/-/g, '');
        }
        if (cryptoApi && cryptoApi.getRandomValues) {
            var bytes = new Uint8Array(16);
            cryptoApi.getRandomValues(bytes);
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
        var perfApi = typeof window !== 'undefined' && window.performance ? window.performance : null;
        var perf = perfApi && typeof perfApi.now === 'function' ?
            Math.floor(perfApi.now() * 1000).toString(36) : '0';
        var identifier = Date.now().toString(36) + perf + uuid.counter.toString(36);
        while (identifier.length < 16) {
            identifier += Date.now().toString(36) + uuid.counter.toString(36);
        }
        return ('sess' + identifier).substring(0, 52);
    }

    return {
        uuid: uuid
    };
});
