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
    'use strict';

    var MIN_PLAYBACK_RATE = 0.25;
    var MAX_PLAYBACK_RATE = 4;

    /**
     * Provider capability definitions used by the adapter layer.
     *
     * Each capability lists the provider methods and/or properties that must be
     * available before callers can safely execute that operation. HTML5
     * operations are often property based, so they must not be represented as an
     * empty requirement set because that would make every truthy object appear
     * capable of every property based operation.
     *
     * @type {Object<string, Object<string, {methods: Array<string>, properties: Array<string>}>>}
     */
    var CAPABILITIES = {
        youtube: {
            currentTime: {methods: ['getCurrentTime'], properties: []},
            duration: {methods: ['getDuration'], properties: []},
            play: {methods: ['playVideo'], properties: []},
            pause: {methods: ['pauseVideo'], properties: []},
            seek: {methods: ['seekTo'], properties: []},
            playbackRate: {methods: ['getPlaybackRate', 'setPlaybackRate'], properties: []},
            readPlaybackRate: {methods: ['getPlaybackRate'], properties: []},
            writePlaybackRate: {methods: ['setPlaybackRate'], properties: []},
            ended: {methods: ['getPlayerState'], properties: []}
        },
        html5: {
            currentTime: {methods: [], properties: ['currentTime']},
            duration: {methods: [], properties: ['duration']},
            play: {methods: ['play'], properties: []},
            pause: {methods: ['pause'], properties: []},
            seek: {methods: [], properties: ['currentTime']},
            playbackRate: {methods: [], properties: ['playbackRate']},
            readPlaybackRate: {methods: [], properties: ['playbackRate']},
            writePlaybackRate: {methods: [], properties: ['playbackRate']},
            volume: {methods: [], properties: ['volume']},
            mute: {methods: [], properties: ['muted']},
            paused: {methods: [], properties: ['paused']},
            ended: {methods: [], properties: ['ended']}
        },
        vimeo: {
            currentTime: {methods: ['getCurrentTime'], properties: []},
            duration: {methods: ['getDuration'], properties: []},
            play: {methods: ['play'], properties: []},
            pause: {methods: ['pause'], properties: []},
            seek: {methods: ['setCurrentTime'], properties: []},
            playbackRate: {methods: ['getPlaybackRate', 'setPlaybackRate'], properties: []},
            readPlaybackRate: {methods: ['getPlaybackRate'], properties: []},
            writePlaybackRate: {methods: ['setPlaybackRate'], properties: []}
        }
    };


    /**
     * Normalise a provider type to the canonical adapter key.
     *
     * @param {string} providerType Candidate provider key.
     * @returns {string} Canonical provider key or empty string.
     */
    function normaliseProviderType(providerType) {
        var type = String(providerType || '').trim().toLowerCase();
        return Object.prototype.hasOwnProperty.call(CAPABILITIES, type) ? type : '';
    }

    /**
     * Check whether the adapter knows a provider type.
     *
     * @param {string} providerType Candidate provider key.
     * @returns {boolean} True when the provider is registered.
     */
    function isKnownProviderType(providerType) {
        return normaliseProviderType(providerType) !== '';
    }

    /**
     * Resolve a provider capability definition.
     *
     * @param {string} providerType Provider key: youtube, html5 or vimeo.
     * @param {string} capability Capability key.
     * @returns {{methods: Array<string>, properties: Array<string>}|null} Capability definition.
     */
    function getCapabilityDefinition(providerType, capability) {
        var type = normaliseProviderType(providerType);
        var providerCapabilities = type ? CAPABILITIES[type] : null;
        if (!providerCapabilities || !Object.prototype.hasOwnProperty.call(providerCapabilities, capability)) {
            return null;
        }
        return providerCapabilities[capability];
    }

    function getCapabilityMethods(providerType, capability) {
        var definition = getCapabilityDefinition(providerType, capability);
        return definition ? definition.methods.slice(0) : [];
    }

    function getCapabilityProperties(providerType, capability) {
        var definition = getCapabilityDefinition(providerType, capability);
        return definition ? definition.properties.slice(0) : [];
    }

    /**
     * Check whether a provider supports a named capability.
     *
     * @param {*} provider Candidate provider object.
     * @param {string} providerType Provider key: youtube, html5 or vimeo.
     * @param {string} capability Capability key.
     * @returns {boolean} True when the provider is available for that capability.
     */
    function can(provider, providerType, capability) {
        var type = normaliseProviderType(providerType);
        var definition = type ? getCapabilityDefinition(type, capability) : null;
        if (!definition) {
            return false;
        }
        return isAvailable(provider, definition.methods, definition.properties);
    }

    /**
     * Check a capability or legacy method list with one adapter entry point.
     *
     * @param {*} provider Candidate provider object.
     * @param {string} providerType Provider key: youtube, html5 or vimeo.
     * @param {string=} capability Optional capability key.
     * @param {Array<string>=} fallbackMethods Optional legacy method list.
     * @returns {boolean} True when the provider can perform the operation.
     */
    function hasCapability(provider, providerType, capability, fallbackMethods) {
        if (capability) {
            return can(provider, providerType, capability);
        }
        if (fallbackMethods && fallbackMethods.length) {
            return isAvailable(provider, fallbackMethods);
        }
        return false;
    }

    /**
     * Return a defensive snapshot of the known capabilities for a provider.
     *
     * @param {string} providerType Provider key: youtube, html5 or vimeo.
     * @returns {Object<string, {methods: Array<string>, properties: Array<string>}>} Capability map.
     */
    function getCapabilities(providerType) {
        var providerCapabilities = CAPABILITIES[String(providerType || '').trim().toLowerCase()] || {};
        var copy = {};
        Object.keys(providerCapabilities).forEach(function(key) {
            copy[key] = {
                methods: providerCapabilities[key].methods.slice(0),
                properties: providerCapabilities[key].properties.slice(0)
            };
        });
        return copy;
    }

    /**
     * Convenience capability wrappers used by concrete player modules.
     *
     * These helpers keep provider-specific capability names out of callers and
     * make the adapter layer the single place where capabilities are declared.
     */
    function canCurrentTime(provider, providerType) {
        return can(provider, providerType, 'currentTime');
    }

    function canDuration(provider, providerType) {
        return can(provider, providerType, 'duration');
    }

    function canPlay(provider, providerType) {
        return can(provider, providerType, 'play');
    }

    function canPause(provider, providerType) {
        return can(provider, providerType, 'pause');
    }

    function canSeek(provider, providerType) {
        return can(provider, providerType, 'seek');
    }

    function canPlaybackRate(provider, providerType) {
        return can(provider, providerType, 'playbackRate');
    }

    function canVolume(provider, providerType) {
        return can(provider, providerType, 'volume');
    }

    function canMute(provider, providerType) {
        return can(provider, providerType, 'mute');
    }

    function canPaused(provider, providerType) {
        return can(provider, providerType, 'paused');
    }

    function canEnded(provider, providerType) {
        return can(provider, providerType, 'ended');
    }

    /**
     * Convert a candidate media time to a safe non-negative number.
     *
     * @param {*} value Candidate media time.
     * @param {number=} fallback Fallback value.
     * @returns {number} Safe media time in seconds.
     */
    function normaliseTime(value, fallback) {
        if (value !== null && value !== undefined && value !== '') {
            var time = Number(value);
            if (isFinite(time) && time >= 0) {
                return time;
            }
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
        if (duration && typeof duration.then === 'function') {
            duration = undefined;
        }
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
     * @param {Array<string>=} properties Required property names.
     * @returns {boolean} True when the provider is usable for the requested methods/properties.
     */
    function isAvailable(provider, methods, properties) {
        if (!provider) {
            return false;
        }
        var requiredMethods = methods || [];
        var requiredProperties = properties || [];
        if (!requiredMethods.length && !requiredProperties.length) {
            return true;
        }
        return requiredMethods.every(function(method) {
            return typeof provider[method] === 'function';
        }) && requiredProperties.every(function(property) {
            return property in Object(provider);
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
        if (typeof setter !== 'function') {
            return run(null, log, label || 'set volume');
        }
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
        if (typeof setter !== 'function') {
            return run(null, log, label || 'set muted');
        }
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
                if (isFinite(rate) && rate >= MIN_PLAYBACK_RATE && rate <= MAX_PLAYBACK_RATE) {
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
        return isFinite(fallbackRate) && fallbackRate >= MIN_PLAYBACK_RATE && fallbackRate <= MAX_PLAYBACK_RATE ? fallbackRate : 1;
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
        if (!isFinite(safeRate) || safeRate < MIN_PLAYBACK_RATE || safeRate > MAX_PLAYBACK_RATE) {
            safeRate = 1;
        }
        if (typeof setter !== 'function') {
            return run(null, log, label || 'set playback rate');
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
                var paused = !!getter();
                if (state) {
                    state.playing = !paused;
                }
                return paused;
            }
        } catch (error) {
            if (log && typeof log.debug === 'function') {
                log.debug('mod_videotrack: could not read ' + (label || 'player') + ' paused state - ' + error);
            }
        }
        return fallback;
    }

    /**
     * Read whether a provider reached the ended state safely.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getter Provider-specific ended-state getter.
     * @param {Object=} log Optional Moodle log module.
     * @param {string=} label Optional log label.
     * @param {*=} endedValue Provider-specific raw value meaning ended, e.g. YouTube 0.
     * @param {string=} providerType Provider key used when no explicit endedValue is available.
     * @returns {boolean} True when playback has ended.
     */
    function isEnded(state, getter, log, label, endedValue, providerType) {
        var fallback = state && typeof state.ended === 'boolean' ? state.ended : false;
        try {
            if (typeof getter === 'function') {
                var value = getter();
                var ended = false;
                if (typeof value === 'boolean') {
                    ended = value;
                } else if (endedValue !== undefined && value !== null && value !== '') {
                    ended = String(value) === String(endedValue);
                } else if ((typeof value === 'number' || (typeof value === 'string' && value !== '')) &&
                        normaliseProviderType(providerType || label) === 'youtube') {
                    ended = Number(value) === 0;
                } else if (typeof value === 'number' || (typeof value === 'string' && value !== '')) {
                    // Numeric provider states are provider-specific. Without an explicit
                    // endedValue, treat them as non-ended to avoid false positives when
                    // adding future providers with different state constants.
                    ended = false;
                } else {
                    ended = !!value;
                }
                if (state) {
                    state.ended = ended;
                }
                return ended;
            }
        } catch (error) {
            if (log && typeof log.debug === 'function') {
                log.debug('mod_videotrack: could not read ' + (label || 'player') + ' ended state - ' + error);
            }
        }
        if (state) {
            state.ended = fallback;
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
        var logFailure = function(error) {
            if (log && typeof log.debug === 'function') {
                log.debug('mod_videotrack: player adapter command failed' +
                    (label ? ' (' + label + ')' : '') + ' - ' + error);
            }
        };
        try {
            if (typeof action === 'function') {
                var result = action();
                if (result && typeof result.then === 'function') {
                    return result.catch(function(error) {
                        logFailure(error);
                        return null;
                    });
                }
                return result;
            }
        } catch (error) {
            logFailure(error);
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
        if (typeof seeker !== 'function') {
            return run(null, log, label || 'seek');
        }
        return run(function() {
            return seeker(safeTarget);
        }, log, label || 'seek');
    }

    return {
        getCapabilities: getCapabilities,
        getCapabilityMethods: getCapabilityMethods,
        getCapabilityProperties: getCapabilityProperties,
        normaliseProviderType: normaliseProviderType,
        isKnownProviderType: isKnownProviderType,
        can: can,
        hasCapability: hasCapability,
        canCurrentTime: canCurrentTime,
        canDuration: canDuration,
        canPlay: canPlay,
        canPause: canPause,
        canSeek: canSeek,
        canPlaybackRate: canPlaybackRate,
        canVolume: canVolume,
        canMute: canMute,
        canPaused: canPaused,
        canEnded: canEnded,
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
        isEnded: isEnded,
        run: run,
        play: play,
        pause: pause,
        seek: seek
    };
});
