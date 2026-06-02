/**
 * Lightweight event bus for mod_videotrack AMD modules.
 *
 * The 1.3 refactor is moving player behaviour into small shared modules. This
 * helper gives those modules a tiny provider-neutral event flow without adding
 * a dependency on a framework or changing the public AMD entrypoints.
 *
 * @module mod_videotrack/core/events
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define(['core/log'], function(Log) {
    'use strict';


    /**
     * Create a new event bus instance.
     *
     * @returns {Object} Event bus API.
     */
    function create(options) {
        options = options || {};
        var handlers = Object.create(null);
        var maxHandlersPerEvent = Number(options.maxHandlersPerEvent);
        if (!isFinite(maxHandlersPerEvent) || maxHandlersPerEvent <= 0) {
            maxHandlersPerEvent = 100;
        }

        /**
         * Normalise internal event names before using them as object keys.
         *
         * @param {*} name Candidate event name.
         * @returns {string} Safe event name or an empty string.
         */
        var normaliseEventName = function(name) {
            var eventName = String(name || '').trim();
            if (!/^[a-z0-9:_-]{1,100}$/i.test(eventName)) {
                return '';
            }
            return eventName;
        };

        return {
            /**
             * Register an event handler.
             *
             * @param {string} name Event name.
             * @param {Function} handler Event handler.
             * @returns {Function} Unsubscribe callback.
             */
            on: function(name, handler) {
                name = normaliseEventName(name);
                if (!name || typeof handler !== 'function') {
                    return function() {};
                }
                handlers[name] = handlers[name] || [];
                var removeHandler = function() {
                    var list = handlers[name];
                    if (!list) {
                        return;
                    }
                    var index = list.indexOf(handler);
                    if (index !== -1) {
                        list.splice(index, 1);
                    }
                    if (!list.length) {
                        delete handlers[name];
                    }
                };
                if (handlers[name].indexOf(handler) !== -1) {
                    return removeHandler;
                }
                if (handlers[name].length >= maxHandlersPerEvent) {
                    Log.debug('mod_videotrack: event handler limit reached for ' + name);
                    return function() {};
                }
                handlers[name].push(handler);

                return removeHandler;
            },

            /**
             * Remove a specific event handler.
             *
             * @param {string} name Event name.
             * @param {Function} handler Event handler.
             */
            off: function(name, handler) {
                name = normaliseEventName(name);
                if (!name || !handlers[name]) {
                    return;
                }
                var index = handlers[name].indexOf(handler);
                if (index !== -1) {
                    handlers[name].splice(index, 1);
                }
                if (!handlers[name].length) {
                    delete handlers[name];
                }
            },

            /**
             * Emit an event to registered handlers.
             *
             * @param {string} name Event name.
             * @param {Object=} payload Event payload.
             * @returns {Array} Handler return values.
             */
            emit: function(name, payload) {
                name = normaliseEventName(name);
                if (!name) {
                    return [];
                }
                var eventPayload = payload || {};
                var list = (handlers[name] || []).slice(0);
                var results = [];
                for (var i = 0; i < list.length; i++) {
                    try {
                        var result = list[i](eventPayload);
                        if (result && typeof result.catch === 'function') {
                            result.catch(function(error) {
                                Log.debug('mod_videotrack: async event handler failed for ' + name + ' - ' +
                                    (error && error.stack ? error.stack : error));
                            });
                        }
                        results.push(result);
                    } catch (error) {
                        Log.debug('mod_videotrack: event handler failed for ' + name + ' - ' +
                            (error && error.stack ? error.stack : error));
                        results.push(null);
                    }
                }
                return results;
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
                name = normaliseEventName(name);
                if (name) {
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
                handlers = Object.create(null);
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
