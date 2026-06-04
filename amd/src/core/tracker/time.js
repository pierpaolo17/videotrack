/**
 * Provider-neutral time and seek helpers for tracker modules.
 *
 * @module mod_videotrack/core/tracker/time
 */
define([
    'mod_videotrack/core/tracker/state',
    'mod_videotrack/core/tracker/events'
], function(TrackerState, TrackerEvents) {
    'use strict';

    var normaliseTime = TrackerState.normaliseTime;
    var markPlaying = TrackerState.markPlaying;
    var markPaused = TrackerState.markPaused;
    var markSeeking = TrackerState.markSeeking;
    var emit = TrackerEvents.emit;

    /**
     * Update the shared last-known playback position.
     *
     * @param {Object} state Mutable player state.
     * @param {number} currentTime Current media time.
     * @param {number=} playbackRate Optional playback rate.
     * @returns {number} Normalised current media time.
     */
    function syncTime(state, currentTime, playbackRate) {
        var time = normaliseTime(currentTime);
        if (state) {
            state.lasttime = time;
            if (typeof playbackRate === 'number' && isFinite(playbackRate) && playbackRate > 0) {
                state.playbackrate = playbackRate;
            }
        }
        return time;
    }


    /**
     * Resolve a current-time provider safely and normalise its result.
     *
     * Providers differ: YouTube/HTML5 usually return synchronously while Vimeo
     * returns a Promise. Keeping that contract in the tracker prevents small
     * provider differences from leaking into every lifecycle helper.
     *
     * @param {Function} getCurrentTime Function returning current media time.
     * @param {Object=} state Optional mutable state used as fallback context.
     * @returns {Promise<number>} Promise resolving to a safe media time.
     */
    function resolveCurrentTime(getCurrentTime, state) {
        if (typeof getCurrentTime !== 'function') {
            return Promise.resolve(normaliseTime(state && state.lasttime));
        }
        return Promise.resolve().then(function() {
            return getCurrentTime();
        }).then(function(currentTime) {
            return normaliseTime(currentTime);
        });
    }

    /**
     * Mark the next seek as controlled by the plugin rather than the learner.
     *
     * @param {Object} state Mutable player state.
     */
    function markProgrammaticSeek(state) {
        if (state) {
            state.isProgrammaticSeek = true;
            state.wasPlayingBeforeProgrammaticSeek = !!state.playing;
            markSeeking(state, {reason: 'programmatic-seek'});
        }
    }

    /**
     * Consume and clear a pending programmatic seek flag.
     *
     * @param {Object} state Mutable player state.
     * @param {number=} currentTime Optional media time to sync while consuming.
     * @returns {boolean} True when a programmatic seek was consumed.
     */
    function consumeProgrammaticSeek(state, currentTime) {
        if (!state || !state.isProgrammaticSeek) {
            return false;
        }
        state.isProgrammaticSeek = false;
        if (typeof currentTime !== 'undefined') {
            syncTime(state, currentTime);
        }
        if (state.wasPlayingBeforeProgrammaticSeek) {
            markPlaying(state, {reason: 'programmatic-seek-complete'});
        } else {
            markPaused(state, {reason: 'programmatic-seek-complete'});
        }
        delete state.wasPlayingBeforeProgrammaticSeek;
        return true;
    }

    /**
     * Resolve whether a learner seek is allowed by the configuration.
     *
     * @param {Object} state Mutable player state.
     * @param {number} newTime Requested media time.
     * @param {Object} config Player configuration.
     * @param {number=} tolerance Seconds ignored as playback drift.
     * @returns {Object} Seek decision.
     */
    function resolveSeek(state, newTime, config, tolerance) {
        var oldTime = normaliseTime(state && state.lasttime);
        var target = normaliseTime(newTime);
        var drift = typeof tolerance === 'number' ? Math.max(0, tolerance) : 0;
        var delta = target - oldTime;
        var forward = delta > drift;
        var backward = delta < -drift;
        var blocked = false;

        if (forward && config && config.allowseekforward === false) {
            blocked = true;
        }
        if (backward && config && config.allowseekbackward === false) {
            blocked = true;
        }

        return {
            oldTime: oldTime,
            newTime: target,
            delta: delta,
            forward: forward,
            backward: backward,
            changed: forward || backward,
            blocked: blocked,
            fallbackTime: oldTime
        };
    }


    /**
     * Temporarily block seek handling while a provider bounces back to the
     * allowed fallback time. Reusing one helper avoids slightly different
     * timeout behaviour between YouTube, HTML5 and Vimeo, and clears any older
     * timer before installing a new one.
     *
     * @param {Object} state Mutable player state.
     * @param {number=} delay Timeout in milliseconds.
     */
    function blockSeek(state, delay) {
        if (!state) {
            return;
        }
        state.seekblocked = true;
        state.wasPlayingBeforeSeekBlock = !!state.playing;
        markSeeking(state, {reason: 'seek-blocked'});
        if (state.seekblocktimer) {
            window.clearTimeout(state.seekblocktimer);
        }
        state.seekblocktimer = window.setTimeout(function() {
            state.seekblocked = false;
            if (state.isSeeking) {
                if (state.wasPlayingBeforeSeekBlock) {
                    markPlaying(state, {reason: 'seek-block-cleared'});
                } else {
                    markPaused(state, {reason: 'seek-block-cleared'});
                }
            }
            delete state.wasPlayingBeforeSeekBlock;
            state.seekblocktimer = null;
        }, typeof delay === 'number' && delay >= 0 ? delay : 500);
    }

    /**
     * Clear a pending seek block and its timeout.
     *
     * @param {Object} state Mutable player state.
     */
    function clearSeekBlock(state) {
        if (!state) {
            return;
        }
        if (state.seekblocktimer) {
            window.clearTimeout(state.seekblocktimer);
            state.seekblocktimer = null;
        }
        state.seekblocked = false;
        if (state.isSeeking) {
            if (state.wasPlayingBeforeSeekBlock) {
                markPlaying(state, {reason: 'seek-block-cleared'});
            } else {
                markPaused(state, {reason: 'seek-block-cleared'});
            }
        }
        delete state.wasPlayingBeforeSeekBlock;
    }

    /**
     * Check replay end state and clear it when playback reached the limit.
     *
     * @param {Object} state Mutable player state.
     * @param {number} currentTime Current media time.
     * @returns {boolean} True when playback should be paused.
     */
    function shouldStopReplay(state, currentTime) {
        if (!state || state.currentReplayEnd === null || typeof state.currentReplayEnd === 'undefined') {
            return false;
        }
        if (normaliseTime(currentTime) >= state.currentReplayEnd) {
            state.currentReplayEnd = null;
            markPaused(state, {reason: 'replay-limit'});
            emit(state, 'replay:limit', {currentTime: normaliseTime(currentTime)});
            return true;
        }
        return false;
    }

    return {
        syncTime: syncTime,
        resolveCurrentTime: resolveCurrentTime,
        markProgrammaticSeek: markProgrammaticSeek,
        consumeProgrammaticSeek: consumeProgrammaticSeek,
        resolveSeek: resolveSeek,
        blockSeek: blockSeek,
        clearSeekBlock: clearSeekBlock,
        shouldStopReplay: shouldStopReplay
    };
});
