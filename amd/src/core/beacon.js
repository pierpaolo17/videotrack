/**
 * sendBeacon helpers for mod_videotrack player modules.
 *
 * The concrete player modules call this through the PlayerCore facade during
 * page unload. The helper is intentionally small and defensive because unload
 * handlers must not throw or depend on normal Moodle AJAX promises.
 *
 * @module mod_videotrack/core/beacon
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define(['mod_videotrack/core/api'], function(Api) {
    'use strict';

    var MAX_BEACON_PAYLOAD_BYTES = 60 * 1024;


    /**
     * Persist the currently open segment using navigator.sendBeacon.
     *
     * @param {Object} config Player configuration.
     * @param {Object} state Mutable player state.
     * @param {*} start Segment start candidate.
     * @param {*} end Segment end candidate.
     * @param {Object} Utils Shared utility module.
     * @param {Object} Log Moodle log module.
     * @returns {boolean} True when the browser accepted the beacon for sending.
     */
    function sendSegment(config, state, start, end, Utils, Log) {
        if (!config || !state || !window.navigator || typeof window.navigator.sendBeacon !== 'function') {
            return false;
        }
        if (!config.beaconurl || !Utils || typeof Utils.isSafeBeaconUrl !== 'function' || !Utils.isSafeBeaconUrl(config.beaconurl)) {
            if (Log && typeof Log.debug === 'function') {
                Log.debug('mod_videotrack: sendBeacon skipped because the endpoint is not safe');
            }
            return false;
        }

        var args = Api.buildSegmentArgs(config, state, start, end, 'beforeunload');
        if (!args) {
            return false;
        }

        try {
            var payload = [{
                index: 0,
                methodname: 'mod_videotrack_save_segment',
                args: args
            }];
            var payloadText = JSON.stringify(payload);
            var blob = new Blob([payloadText], {type: 'application/json'});
            // Use the encoded byte size rather than UTF-16 string length so the
            // limit matches what navigator.sendBeacon actually receives.
            if (blob.size > MAX_BEACON_PAYLOAD_BYTES) {
                if (Log && typeof Log.debug === 'function') {
                    Log.debug('mod_videotrack: sendBeacon skipped because the encoded payload is too large');
                }
                return false;
            }
            var accepted = window.navigator.sendBeacon(config.beaconurl, blob);
            if (!accepted && Log && typeof Log.debug === 'function') {
                Log.debug('mod_videotrack: sendBeacon was not accepted by the browser');
            }
            return accepted;
        } catch (error) {
            if (Log && typeof Log.debug === 'function') {
                Log.debug('mod_videotrack: sendBeacon failed - ' + error);
            }
            return false;
        }
    }

    return {
        sendSegment: sendSegment
    };
});
