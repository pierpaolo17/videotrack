/**
 * Shared player state factory for mod_videotrack AMD player modules.
 *
 * The concrete YouTube, Vimeo and HTML5 modules mutate the returned object as
 * playback events arrive. Keeping the default shape in one place avoids each
 * player growing a slightly different set of defaults during the 1.3 refactor.
 *
 * @module mod_videotrack/core/state
 */
define([], function() {
    'use strict';


    /**
     * Create a fresh mutable player state object.
     *
     * @param {Object=} overrides Optional player-specific defaults.
     * @returns {Object} Mutable state for one player instance.
     */
    function create(overrides) {
        var state = {
            sessionid: null,
            trackerstate: 'idle',
            playing: false,
            segmentstart: null,
            wallclockstart: null,
            lastHeartbeatWallclock: null,
            heartbeatPending: false,
            lasttime: 0,
            playbackrate: 1,
            duration: 0,
            heartbeatid: null,
            isSeeking: false,
            isProgrammaticSeek: false,
            seekblocked: false,
            seekblocktimer: null,
            currentReplayEnd: null,
            ended: false,
            _pendingResume: false,
            _transitionSerial: 0,
            _heartbeatSerial: 0,
            ajaxRequestScope: null,
            _posterRemoved: false,
            _posterPlayListener: null
        };

        overrides = overrides || {};
        Object.keys(overrides).forEach(function(key) {
            state[key] = overrides[key];
        });

        return state;
    }

    return {
        create: create
    };
});
