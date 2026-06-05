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
define([
    'mod_videotrack/core/api',
    'mod_videotrack/core/debug'
], function(Api, Debug) {
    'use strict';

    var MAX_BEACON_PAYLOAD_BYTES = 60 * 1024; // Stay below common browser and normal AJAX payload limits.


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
    function sendSegment(config, state, start, end, Utils) {
        if (!config || !state || !window.navigator || typeof window.navigator.sendBeacon !== 'function') {
            return false;
        }
        if (!config.beaconurl || !Utils || typeof Utils.isSafeBeaconUrl !== 'function' || !Utils.isSafeBeaconUrl(config.beaconurl)) {
            Debug.log('beaconunsafe');
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
                Debug.log('beaconpayloadlarge');
                return false;
            }
            var accepted = window.navigator.sendBeacon(config.beaconurl, blob);
            if (!accepted) {
                Debug.log('beaconnotaccepted');
            }
            return accepted;
        } catch (error) {
            Debug.log('beaconfailed', {message: String(error)});
            return false;
        }
    }

    return {
        sendSegment: sendSegment
    };
});
