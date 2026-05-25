/**
 * Provider-neutral player adapter helpers for mod_videotrack.
 *
 * This module is the first small step toward thin YouTube/HTML5/Vimeo
 * adapters. It does not wrap any SDK directly yet; instead it centralises the
 * defensive checks that every concrete player needs when reading current time,
 * duration or when executing provider-specific commands.
 *
 * @module mod_videotrack/core/adapter
 */
define([], function() {

    /**
     * Convert a candidate media time to a safe non-negative number.
     *
     * @param {*} value Candidate media time.
     * @param {number=} fallback Fallback value.
     * @returns {number} Safe media time in seconds.
     */
    function normaliseTime(value, fallback) {
        var time = Number(value);
        if (isFinite(time) && time >= 0) {
            return time;
        }
        var fallbackTime = Number(fallback);
        return isFinite(fallbackTime) && fallbackTime >= 0 ? fallbackTime : 0;
    }

    /**
     * Read the current media time from a provider safely.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getter Provider-specific current-time getter.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {number} Safe media time in seconds.
     */
    function getCurrentTime(state, getter, log, label) {
        var fallback = state && state.lasttime;
        try {
            if (typeof getter === 'function') {
                var current = normaliseTime(getter(), fallback);
                if (state) {
                    state.lasttime = current;
                }
                return current;
            }
        } catch (error) {
            if (log && typeof log.debug === 'function') {
                log.debug('mod_videotrack: could not read ' + (label || 'player') + ' current time - ' + error);
            }
        }
        return normaliseTime(fallback, 0);
    }

    /**
     * Read media duration from a provider safely.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getter Provider-specific duration getter.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {number} Safe duration in seconds.
     */
    function getDuration(state, getter, log, label) {
        var fallback = state && state.duration;
        try {
            if (typeof getter === 'function') {
                var duration = normaliseTime(getter(), fallback);
                if (state && duration > 0) {
                    state.duration = duration;
                }
                return duration;
            }
        } catch (error) {
            if (log && typeof log.debug === 'function') {
                log.debug('mod_videotrack: could not read ' + (label || 'player') + ' duration - ' + error);
            }
        }
        return normaliseTime(fallback, 0);
    }

    /**
     * Read media playback rate from a provider safely.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getter Provider-specific playback-rate getter.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {number} Safe playback rate.
     */
    function getPlaybackRate(state, getter, log, label) {
        var fallback = state && state.playbackrate;
        try {
            if (typeof getter === 'function') {
                var rate = Number(getter());
                if (isFinite(rate) && rate > 0) {
                    if (state) {
                        state.playbackrate = rate;
                    }
                    return rate;
                }
            }
        } catch (error) {
            if (log && typeof log.debug === 'function') {
                log.debug('mod_videotrack: could not read ' + (label || 'player') + ' playback rate - ' + error);
            }
        }
        var fallbackRate = Number(fallback);
        return isFinite(fallbackRate) && fallbackRate > 0 ? fallbackRate : 1;
    }

    /**
     * Set media playback rate through a provider safely.
     *
     * @param {*} rate Candidate playback rate.
     * @param {Function} setter Provider-specific playback-rate setter.
     * @param {Object=} state Optional mutable player state.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {*} Provider return value or null on failure.
     */
    function setPlaybackRate(rate, setter, state, log, label) {
        var safeRate = Number(rate);
        if (!isFinite(safeRate) || safeRate <= 0) {
            safeRate = 1;
        }
        return run(function() {
            var result = setter(safeRate);
            if (state) {
                state.playbackrate = safeRate;
            }
            return result;
        }, log, label || 'set playback rate');
    }


    /**
     * Read whether a provider is paused safely.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getter Provider-specific paused-state getter.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {boolean} True when paused, false when playing.
     */
    function isPaused(state, getter, log, label) {
        var fallback = state && typeof state.playing === 'boolean' ? !state.playing : true;
        try {
            if (typeof getter === 'function') {
                return !!getter();
            }
        } catch (error) {
            if (log && typeof log.debug === 'function') {
                log.debug('mod_videotrack: could not read ' + (label || 'player') + ' paused state - ' + error);
            }
        }
        return fallback;
    }

    /**
     * Execute a provider command while keeping SDK exceptions contained.
     *
     * @param {Function} action Provider-specific command.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {*} Action return value or null on failure.
     */
    function run(action, log, label) {
        try {
            if (typeof action === 'function') {
                return action();
            }
        } catch (error) {
            if (log && typeof log.debug === 'function') {
                log.debug('mod_videotrack: player adapter command failed' +
                    (label ? ' (' + label + ')' : '') + ' - ' + error);
            }
        }
        return null;
    }


    /**
     * Execute a provider play command safely.
     *
     * @param {Function} action Provider-specific play callback.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {*} Provider return value or null on failure.
     */
    function play(action, log, label) {
        return run(action, log, label || 'play');
    }

    /**
     * Execute a provider pause command safely.
     *
     * @param {Function} action Provider-specific pause callback.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {*} Provider return value or null on failure.
     */
    function pause(action, log, label) {
        return run(action, log, label || 'pause');
    }

    /**
     * Seek through a provider-specific callback using a normalised target time.
     *
     * @param {*} target Candidate target time.
     * @param {Function} seeker Provider-specific seek callback.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {*} Provider seek return value or null on failure.
     */
    function seek(target, seeker, log, label) {
        var safeTarget = normaliseTime(target, 0);
        return run(function() {
            return seeker(safeTarget);
        }, log, label || 'seek');
    }

    return {
        normaliseTime: normaliseTime,
        getCurrentTime: getCurrentTime,
        getDuration: getDuration,
        getPlaybackRate: getPlaybackRate,
        setPlaybackRate: setPlaybackRate,
        isPaused: isPaused,
        run: run,
        play: play,
        pause: pause,
        seek: seek
    };
});
