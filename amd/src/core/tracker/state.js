/**
 * Provider-neutral tracker state helpers.
 *
 * @module mod_videotrack/core/tracker/state
 */
define([
    'mod_videotrack/core/tracker/events'
], function(TrackerEvents) {
    'use strict';

    /**
     * Convert an arbitrary media time to a safe non-negative number.
     *
     * @param {*} value Candidate media time.
     * @returns {number} Safe media time in seconds.
     */
    function normaliseTime(value) {
        var time = Number(value);
        if (!isFinite(time) || time < 0) {
            return 0;
        }
        return time;
    }

    var emit = TrackerEvents.emit;

    /**
     * Tracker state constants used by the 1.3 state machine.
     *
     * Concrete players may still expose their own SDK states. This state
     * machine keeps the provider-neutral lifecycle explicit in the tracker
     * layer so future phases can reason about transitions without reading
     * YouTube, HTML5 and Vimeo event details directly.
     *
     * @type {Object<string, string>}
     */
    var STATES = {
        IDLE: 'idle',
        PLAYING: 'playing',
        PAUSED: 'paused',
        SEEKING: 'seeking',
        ENDED: 'ended',
        DESTROYED: 'destroyed'
    };

    /**
     * Allowed provider-neutral state transitions.
     *
     * @type {Object<string, Array<string>>}
     */
    var TRANSITIONS = {};
    TRANSITIONS[STATES.IDLE] = [STATES.PLAYING, STATES.PAUSED, STATES.SEEKING, STATES.ENDED, STATES.DESTROYED];
    TRANSITIONS[STATES.PLAYING] = [STATES.PAUSED, STATES.SEEKING, STATES.ENDED, STATES.DESTROYED];
    TRANSITIONS[STATES.PAUSED] = [STATES.PLAYING, STATES.SEEKING, STATES.ENDED, STATES.DESTROYED];
    TRANSITIONS[STATES.SEEKING] = [STATES.PLAYING, STATES.PAUSED, STATES.ENDED, STATES.DESTROYED];
    TRANSITIONS[STATES.ENDED] = [STATES.IDLE, STATES.PLAYING, STATES.DESTROYED];
    TRANSITIONS[STATES.DESTROYED] = [];

    /**
     * Normalise a candidate tracker state.
     *
     * @param {*} value Candidate state.
     * @returns {string} Known tracker state.
     */
    function normaliseTrackerState(value) {
        var stateName = String(value || '').toLowerCase();
        return Object.keys(STATES).some(function(key) {
            return STATES[key] === stateName;
        }) ? stateName : STATES.IDLE;
    }


    /**
     * Check whether a candidate tracker state is explicitly known.
     *
     * Empty values are treated as the initial idle state for backwards
     * compatibility, but non-empty unknown state names must not silently become
     * valid transitions.
     *
     * @param {*} value Candidate state.
     * @returns {boolean} True when value is empty or a known tracker state.
     */
    function isKnownTrackerState(value) {
        if (value === null || value === undefined || value === '') {
            return true;
        }
        var stateName = String(value).toLowerCase();
        return Object.keys(STATES).some(function(key) {
            return STATES[key] === stateName;
        });
    }

    /**
     * Read the provider-neutral tracker state.
     *
     * @param {Object} state Mutable player state.
     * @returns {string} Current tracker state.
     */
    function getTrackerState(state) {
        if (!state) {
            return STATES.IDLE;
        }
        return normaliseTrackerState(state.trackerstate);
    }

    /**
     * Read the current transition token for async race guards.
     *
     * @param {Object} state Mutable player state.
     * @returns {number} Monotonic transition token.
     */
    function getTransitionToken(state) {
        return state && typeof state._transitionSerial === 'number' ? state._transitionSerial : 0;
    }

    /**
     * Check whether an async continuation still belongs to the active state.
     *
     * @param {Object} state Mutable player state.
     * @param {number} token Token captured before async work started.
     * @returns {boolean} True when no newer transition happened.
     */
    function isTransitionCurrent(state, token) {
        return !!state && getTransitionToken(state) === token;
    }

    /**
     * Check whether a transition is allowed.
     *
     * @param {string} from Current state.
     * @param {string} to Next state.
     * @returns {boolean} True when transition is valid.
     */
    function canTransition(from, to) {
        if (!isKnownTrackerState(from) || !isKnownTrackerState(to)) {
            return false;
        }
        var current = normaliseTrackerState(from);
        var next = normaliseTrackerState(to);
        if (current === next) {
            return true;
        }
        return (TRANSITIONS[current] || []).indexOf(next) !== -1;
    }

    /**
     * Synchronise legacy boolean flags with the provider-neutral tracker state.
     *
     * @param {Object} state Mutable player state.
     * @param {string} trackerState Normalised tracker state.
     */
    function applyTrackerStateFlags(state, trackerState) {
        if (!state) {
            return;
        }
        if (trackerState === STATES.PLAYING) {
            state.playing = true;
            state.ended = false;
            state.isSeeking = false;
        } else if (trackerState === STATES.PAUSED || trackerState === STATES.IDLE) {
            state.playing = false;
            state.ended = false;
            state.isSeeking = false;
        } else if (trackerState === STATES.SEEKING) {
            state.isSeeking = true;
            state.ended = false;
        } else if (trackerState === STATES.ENDED) {
            state.playing = false;
            state.ended = true;
            state.isSeeking = false;
        } else if (trackerState === STATES.DESTROYED) {
            state.playing = false;
            state.isSeeking = false;
        }
    }

    /**
     * Apply a provider-neutral tracker state transition.
     *
     * @param {Object} state Mutable player state.
     * @param {string} nextState Target state.
     * @param {Object=} meta Optional transition metadata.
     * @returns {boolean} True when state was changed or already matched.
     */
    function setTrackerState(state, nextState, meta) {
        if (!state) {
            return false;
        }
        var previous = getTrackerState(state);
        var next = normaliseTrackerState(nextState);
        if (!canTransition(previous, next)) {
            emit(state, 'state:blocked', {from: previous, to: next, meta: meta || {}});
            return false;
        }
        if (previous === next) {
            applyTrackerStateFlags(state, next);
            return true;
        }

        state.trackerstate = next;
        state._transitionSerial = getTransitionToken(state) + 1;
        applyTrackerStateFlags(state, next);

        emit(state, 'state:change', {from: previous, to: next, meta: meta || {}});
        return true;
    }

    function markIdle(state, meta) {
        return setTrackerState(state, STATES.IDLE, meta);
    }

    function markPlaying(state, meta) {
        return setTrackerState(state, STATES.PLAYING, meta);
    }

    function markPaused(state, meta) {
        return setTrackerState(state, STATES.PAUSED, meta);
    }

    function markSeeking(state, meta) {
        return setTrackerState(state, STATES.SEEKING, meta);
    }

    function markEnded(state, meta) {
        return setTrackerState(state, STATES.ENDED, meta);
    }

    function markDestroyed(state, meta) {
        return setTrackerState(state, STATES.DESTROYED, meta);
    }


    return {
        STATES: STATES,
        normaliseTime: normaliseTime,
        normaliseTrackerState: normaliseTrackerState,
        isKnownTrackerState: isKnownTrackerState,
        getTrackerState: getTrackerState,
        getTransitionToken: getTransitionToken,
        isTransitionCurrent: isTransitionCurrent,
        canTransition: canTransition,
        setTrackerState: setTrackerState,
        markIdle: markIdle,
        markPlaying: markPlaying,
        markPaused: markPaused,
        markSeeking: markSeeking,
        markEnded: markEnded,
        markDestroyed: markDestroyed
    };
});
