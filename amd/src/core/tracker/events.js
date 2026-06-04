/**
 * State-bound event helpers for the provider-neutral tracker.
 *
 * @module mod_videotrack/core/tracker/events
 */
define([
    'mod_videotrack/core/events'
], function(Events) {
    'use strict';

    /**
     * Register a tracker event handler bound to a player state.
     *
     * @param {Object} state Mutable player state.
     * @param {string} name Event name.
     * @param {Function} handler Event handler.
     * @returns {Function} Unsubscribe callback.
     */
    function on(state, name, handler) {
        return Events.ensure(state).on(name, handler);
    }

    /**
     * Register a tracker event handler that runs at most once.
     *
     * @param {Object} state Mutable player state.
     * @param {string} name Event name.
     * @param {Function} handler Event handler.
     * @returns {Function} Unsubscribe callback.
     */
    function once(state, name, handler) {
        return Events.ensure(state).once(name, handler);
    }

    /**
     * Remove a tracker event handler from a player state.
     *
     * @param {Object} state Mutable player state.
     * @param {string} name Event name.
     * @param {Function} handler Event handler.
     */
    function off(state, name, handler) {
        if (state && state.events && typeof state.events.off === 'function') {
            state.events.off(name, handler);
        }
    }

    /**
     * Count tracker event handlers bound to a player state.
     *
     * @param {Object} state Mutable player state.
     * @param {string=} name Optional event name.
     * @returns {number} Registered handler count.
     */
    function count(state, name) {
        if (!state || !state.events || typeof state.events.count !== 'function') {
            return 0;
        }
        return state.events.count(name);
    }

    /**
     * Remove tracker event handlers bound to a player state.
     *
     * @param {Object} state Mutable player state.
     */
    function clear(state) {
        if (state && state.events && typeof state.events.clear === 'function') {
            state.events.clear();
        }
    }

    /**
     * Emit a tracker event when a state-bound event bus exists.
     *
     * @param {Object} state Mutable player state.
     * @param {string} name Event name.
     * @param {Object=} payload Event payload.
     */
    function emit(state, name, payload) {
        Events.emit(state, name, payload);
    }

    return {
        on: on,
        once: once,
        off: off,
        count: count,
        clear: clear,
        emit: emit
    };
});
