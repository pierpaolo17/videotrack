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
     * Resolve a skip target by applying a delta to the current time and
     * clamping the result to the available media duration when known.
     *
     * @param {*} current Candidate current time.
     * @param {*} delta Signed skip delta in seconds.
     * @param {*=} duration Optional media duration.
     * @returns {number} Safe target time in seconds.
     */
    function resolveSkipTarget(current, delta, duration) {
        var target = normaliseTime(current, 0) + Number(delta || 0);
        if (!isFinite(target)) {
            target = normaliseTime(current, 0);
        }
        target = Math.max(0, target);
        var safeDuration = Number(duration);
        if (isFinite(safeDuration) && safeDuration > 0) {
            target = Math.min(safeDuration, target);
        }
        return target;
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
     * Check that a provider object is present and exposes the required methods.
     *
     * Keeping this small guard in the adapter layer avoids subtle differences
     * between YouTube, HTML5 and Vimeo availability checks. A provider may be
     * truthy while its SDK has not exposed the method needed by a tracker path
     * yet; in that case callers should skip the operation safely.
     *
     * @param {*} provider Candidate provider object.
     * @param {Array<string>=} methods Required method names.
     * @returns {boolean} True when the provider is usable for the requested methods.
     */
    function isAvailable(provider, methods) {
        if (!provider) {
            return false;
        }
        if (!methods || !methods.length) {
            return true;
        }
        return methods.every(function(method) {
            return typeof provider[method] === 'function';
        });
    }

    /**
     * Convert a candidate volume to a safe range between 0 and 1.
     *
     * @param {*} value Candidate volume.
     * @param {number=} fallback Fallback value.
     * @returns {number} Safe volume ratio.
     */
    function normaliseVolume(value, fallback) {
        var volume = Number(value);
        if (isFinite(volume)) {
            return Math.max(0, Math.min(1, volume));
        }
        var fallbackVolume = Number(fallback);
        return isFinite(fallbackVolume) ? Math.max(0, Math.min(1, fallbackVolume)) : 1;
    }

    /**
     * Read media volume from a provider safely.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getter Provider-specific volume getter.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {number} Safe volume ratio between 0 and 1.
     */
    function getVolume(state, getter, log, label) {
        var fallback = state && state.volume;
        try {
            if (typeof getter === 'function') {
                var volume = normaliseVolume(getter(), fallback);
                if (state) {
                    state.volume = volume;
                }
                return volume;
            }
        } catch (error) {
            if (log && typeof log.debug === 'function') {
                log.debug('mod_videotrack: could not read ' + (label || 'player') + ' volume - ' + error);
            }
        }
        return normaliseVolume(fallback, 1);
    }

    /**
     * Set media volume through a provider safely.
     *
     * @param {*} volume Candidate volume ratio.
     * @param {Function} setter Provider-specific volume setter.
     * @param {Object=} state Optional mutable player state.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {*} Provider return value or null on failure.
     */
    function setVolume(volume, setter, state, log, label) {
        var safeVolume = normaliseVolume(volume, state && state.volume);
        return run(function() {
            var result = setter(safeVolume);
            if (state) {
                state.volume = safeVolume;
                state.muted = (safeVolume === 0);
            }
            return result;
        }, log, label || 'set volume');
    }

    /**
     * Read whether a provider is muted safely.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getter Provider-specific muted-state getter.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {boolean} True when muted.
     */
    function isMuted(state, getter, log, label) {
        var fallback = state && typeof state.muted === 'boolean' ? state.muted : false;
        try {
            if (typeof getter === 'function') {
                var muted = !!getter();
                if (state) {
                    state.muted = muted;
                }
                return muted;
            }
        } catch (error) {
            if (log && typeof log.debug === 'function') {
                log.debug('mod_videotrack: could not read ' + (label || 'player') + ' muted state - ' + error);
            }
        }
        return fallback;
    }

    /**
     * Set muted state through a provider safely.
     *
     * @param {*} muted Candidate muted state.
     * @param {Function} setter Provider-specific muted-state setter.
     * @param {Object=} state Optional mutable player state.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @returns {*} Provider return value or null on failure.
     */
    function setMuted(muted, setter, state, log, label) {
        var safeMuted = !!muted;
        return run(function() {
            var result = setter(safeMuted);
            if (state) {
                state.muted = safeMuted;
            }
            return result;
        }, log, label || 'set muted');
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
        normaliseVolume: normaliseVolume,
        resolveSkipTarget: resolveSkipTarget,
        isAvailable: isAvailable,
        getCurrentTime: getCurrentTime,
        getDuration: getDuration,
        getVolume: getVolume,
        setVolume: setVolume,
        isMuted: isMuted,
        setMuted: setMuted,
        getPlaybackRate: getPlaybackRate,
        setPlaybackRate: setPlaybackRate,
        isPaused: isPaused,
        run: run,
        play: play,
        pause: pause,
        seek: seek
    };
});
