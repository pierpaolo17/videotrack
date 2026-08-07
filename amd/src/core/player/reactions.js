/**
 * Player reaction announcement facade for mod_videotrack.
 *
 * This module keeps reaction status handling out of the shared player facade.
 * It does not change reaction timing or tracking behaviour.
 *
 * @module mod_videotrack/core/player/reactions
 */
define([
    'mod_videotrack/core/reactions'
], function(Reactions) {
    'use strict';

    /**
     * Announce when reactions become available or unavailable.
     *
     * @param {boolean} available Whether reaction controls are available.
     * @param {Object} config Player configuration.
     * @param {Object} reactionState Mutable reaction announcement state.
     */
    function announceAvailability(available, config, reactionState) {
        Reactions.announceAvailability(available, config, reactionState);
    }

    /**
     * Announce that reactions are unavailable immediately.
     *
     * @param {Object} config Player configuration.
     * @param {Object} reactionState Mutable reaction announcement state.
     */
    function announceUnavailable(config, reactionState) {
        Reactions.announceUnavailable(config, reactionState);
    }

    return {
        announceAvailability: announceAvailability,
        announceUnavailable: announceUnavailable
    };
});
