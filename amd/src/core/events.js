/**
 * Lightweight event bus for mod_videotrack AMD modules.
 *
 * The 1.3 refactor is moving player behaviour into small shared modules. This
 * helper gives those modules a tiny provider-neutral event flow without adding
 * a dependency on a framework or changing the public AMD entrypoints.
 *
 * @module mod_videotrack/core/events
 */
define([], function() {

    /**
     * Create a new event bus instance.
     *
     * @returns {Object} Event bus API.
     */
    function create() {
        var handlers = {};

        return {
            /**
             * Register an event handler.
             *
             * @param {string} name Event name.
             * @param {Function} handler Event handler.
             * @returns {Function} Unsubscribe callback.
             */
            on: function(name, handler) {
                if (typeof name !== 'string' || typeof handler !== 'function') {
                    return function() {};
                }
                handlers[name] = handlers[name] || [];
                handlers[name].push(handler);

                return function() {
                    handlers[name] = (handlers[name] || []).filter(function(candidate) {
                        return candidate !== handler;
                    });
                };
            },

            /**
             * Remove a specific event handler.
             *
             * @param {string} name Event name.
             * @param {Function} handler Event handler.
             */
            off: function(name, handler) {
                if (!handlers[name]) {
                    return;
                }
                handlers[name] = handlers[name].filter(function(candidate) {
                    return candidate !== handler;
                });
            },

            /**
             * Emit an event to registered handlers.
             *
             * @param {string} name Event name.
             * @param {Object=} payload Event payload.
             * @returns {Array} Handler return values.
             */
            emit: function(name, payload) {
                var eventPayload = payload || {};
                return (handlers[name] || []).slice().map(function(handler) {
                    try {
                        return handler(eventPayload);
                    } catch (error) {
                        return null;
                    }
                });
            },

            /**
             * Register an event handler that runs at most once.
             *
             * @param {string} name Event name.
             * @param {Function} handler Event handler.
             * @returns {Function} Unsubscribe callback.
             */
            once: function(name, handler) {
                var unsubscribe = function() {};
                var wrapped = function(payload) {
                    unsubscribe();
                    return handler(payload || {});
                };
                unsubscribe = this.on(name, wrapped);
                return unsubscribe;
            },

            /**
             * Count registered handlers for an event, or all handlers.
             *
             * @param {string=} name Optional event name.
             * @returns {number} Handler count.
             */
            count: function(name) {
                if (typeof name === 'string') {
                    return (handlers[name] || []).length;
                }
                return Object.keys(handlers).reduce(function(total, key) {
                    return total + handlers[key].length;
                }, 0);
            },

            /**
             * Remove all event handlers.
             */
            clear: function() {
                handlers = {};
            }
        };
    }

    /**
     * Ensure a state object has an event bus.
     *
     * @param {Object} state Mutable player state.
     * @returns {Object} Event bus API.
     */
    function ensure(state) {
        if (!state) {
            return create();
        }
        if (!state.events || typeof state.events.emit !== 'function') {
            state.events = create();
        }
        return state.events;
    }

    /**
     * Emit an event if a state-bound event bus exists.
     *
     * @param {Object} state Mutable player state.
     * @param {string} name Event name.
     * @param {Object=} payload Event payload.
     * @returns {Array} Handler return values.
     */
    function emit(state, name, payload) {
        if (!state || !state.events || typeof state.events.emit !== 'function') {
            return [];
        }
        return state.events.emit(name, payload || {});
    }

    return {
        create: create,
        ensure: ensure,
        emit: emit
    };
});
