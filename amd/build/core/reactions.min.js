/**
 * Shared reaction UI state helpers for mod_videotrack player modules.
 *
 * This module intentionally keeps only mutable announcement state for now.
 * The concrete player modules still own their DOM event handlers while the
 * 1.3 branch progressively extracts reaction save/delete behaviour.
 *
 * @module mod_videotrack/core/reactions
 */
define([], function() {

    /**
     * Build a fresh reaction announcement state object.
     *
     * @returns {Object} Mutable reaction state used by PlayerCore helpers.
     */
    function createState() {
        return {
            timer: null,
            cssTimer: null,
            readyAnnounced: false,
            lastAnnouncement: null,
            lastUnavailableAt: 0,
            unavailableInterval: 60000,
            debounceMs: 500
        };
    }

    return {
        createState: createState
    };
});
