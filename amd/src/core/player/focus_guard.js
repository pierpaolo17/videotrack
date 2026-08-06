/**
 * Provider-neutral integrity indicators and optional learner focus controls.
 *
 * Signals are diagnostic only. They are deliberately bounded, contain no free
 * text and must not be treated as automatic proof of misconduct.
 *
 * @module mod_videotrack/core/player/focus_guard
 */
define([
    'mod_videotrack/core/api',
    'mod_videotrack/core/debug'
], function(Api, Debug) {
    'use strict';

    var CALLBACK_TIMEOUT_MS = 30000;
    var WATCHDOG_INTERVAL_MS = 10000;
    var CLIENT_DEBOUNCE_MS = 5000;

    /**
     * Return a bounded integer from the browser crypto API when available.
     *
     * @param {number} min Inclusive lower bound.
     * @param {number} max Inclusive upper bound.
     * @returns {number} Random integer.
     */
    function randomInteger(min, max) {
        var range = Math.max(1, max - min + 1);
        var cryptoApi = window.crypto || window.msCrypto;
        if (cryptoApi && typeof cryptoApi.getRandomValues === 'function') {
            var values = new Uint32Array(1);
            cryptoApi.getRandomValues(values);
            return min + (values[0] % range);
        }
        return min + Math.floor(Math.random() * range);
    }

    /**
     * Create the focus-control controller for one player instance.
     *
     * @param {Object} options Controller options.
     * @returns {Object} Public controller.
     */
    function create(options) {
        options = options || {};
        var config = options.config || {};
        var state = options.state || {};
        var playing = false;
        var destroyed = false;
        var randomTimer = null;
        var watchdogTimer = null;
        var observer = null;
        var lastProgressAt = Date.now();
        var lastProgressTime = null;
        var lastActionAt = Date.now();
        var lastEventAt = {};
        var shell = document.querySelector('.videotrack-player-shell');
        var playerWrap = document.querySelector('.videotrack-player-wrap');

        /**
         * Resolve current playback time without assuming a synchronous provider API.
         *
         * @returns {Promise<number>} Current timestamp.
         */
        function currentTime() {
            if (typeof options.getCurrentTime !== 'function') {
                return Promise.resolve(Number(state.lasttime) || 0);
            }
            try {
                return Promise.resolve(options.getCurrentTime()).then(function(value) {
                    value = Number(value);
                    return Number.isFinite(value) && value > 0 ? value : 0;
                });
            } catch (error) {
                return Promise.resolve(Number(state.lasttime) || 0);
            }
        }

        /**
         * Record one bounded diagnostic signal when recording is enabled.
         *
         * @param {string} eventType Signal type.
         * @returns {Promise<void>}
         */
        function record(eventType) {
            if (!config.integrityindicatorsenabled || destroyed) {
                return Promise.resolve();
            }
            var now = Date.now();
            if (lastEventAt[eventType] && now - lastEventAt[eventType] < CLIENT_DEBOUNCE_MS) {
                return Promise.resolve();
            }
            lastEventAt[eventType] = now;
            return currentTime().then(function(time) {
                return Api.call('mod_videotrack_save_integrity_event', {
                    cmid: Number(config.cmid) || 0,
                    sessionid: String(state.sessionid || ''),
                    eventtype: eventType,
                    videotime: time
                }, {timeout: 30000});
            }).catch(function(error) {
                Debug.log('integrityeventsavefailed', {message: error});
            });
        }

        /** Cancel the current random-pause deadline. */
        function clearRandomTimer() {
            if (randomTimer) {
                window.clearTimeout(randomTimer);
                randomTimer = null;
            }
        }

        /**
         * Show a non-error status message through the shared player status area.
         *
         * @param {string} message Message text.
         */
        function showMessage(message) {
            if (message && typeof options.showMessage === 'function') {
                options.showMessage(message);
            }
        }

        /**
         * Pause playback through the provider callback.
         *
         * @param {string} reason Signal reason.
         * @param {string} message Student-facing message.
         */
        function pausePlayback(reason, message) {
            if (!playing || typeof options.pause !== 'function') {
                return;
            }
            playing = false;
            clearRandomTimer();
            record(reason);
            try {
                Promise.resolve(options.pause()).catch(function(error) {
                    Debug.log('focuspausefailed', {message: error});
                });
            } catch (error) {
                Debug.log('focuspausefailed', {message: error});
            }
            showMessage(message);
        }

        /** Schedule the next random attention pause while playback is active. */
        function scheduleRandomPause() {
            clearRandomTimer();
            if (!playing || !config.randomfocuspauses || destroyed) {
                return;
            }
            var min = Math.max(301, Number(config.randompauseminseconds) || 301);
            var max = Math.min(1799, Number(config.randompausemaxseconds) || 1799);
            if (max < min) {
                max = min;
            }
            var delay = randomInteger(min, max) * 1000;
            randomTimer = window.setTimeout(function() {
                randomTimer = null;
                pausePlayback('randompause', config.randompausedlabel);
            }, delay);
        }

        /**
         * Register a learner interaction and restart the random-pause interval.
         *
         * @param {string=} action Action label used only for local diagnostics.
         */
        function noteAction(action) {
            lastActionAt = Date.now();
            state.lastFocusAction = action || 'interaction';
            if (playing) {
                scheduleRandomPause();
            }
        }

        /**
         * Update the controller playback state.
         *
         * @param {boolean} value Whether the provider is playing.
         */
        function setPlaying(value) {
            playing = !!value;
            noteAction(playing ? 'play' : 'pause');
            if (!playing) {
                clearRandomTimer();
            }
        }

        /**
         * Feed provider progress callbacks to the diagnostics watchdog.
         *
         * @param {number} time Current playback time.
         */
        function noteProgress(time) {
            var now = Date.now();
            var current = Number(time);
            if (!Number.isFinite(current)) {
                return;
            }
            if (playing && lastProgressTime !== null && now - lastActionAt > 2000) {
                var wallDelta = Math.max(0, (now - lastProgressAt) / 1000);
                var videoDelta = current - lastProgressTime;
                if (videoDelta > Math.max(8, wallDelta * 4) || videoDelta < -8) {
                    record('trackinggap');
                }
            }
            lastProgressAt = now;
            lastProgressTime = current;
        }

        /**
         * Apply the configured best-effort Picture-in-Picture policy.
         *
         * @param {HTMLElement} element Media element or provider iframe.
         */
        function applyPictureInPicturePolicy(element) {
            if (!config.preventpictureinpicture || !element) {
                return;
            }
            if (element.tagName && element.tagName.toLowerCase() === 'iframe') {
                var allow = String(element.getAttribute('allow') || '');
                var tokens = allow.split(';').map(function(token) {
                    return token.trim();
                }).filter(function(token) {
                    return token && token.toLowerCase() !== 'picture-in-picture';
                });
                element.setAttribute('allow', tokens.join('; '));
                return;
            }
            try {
                element.disablePictureInPicture = true;
                element.setAttribute('disablepictureinpicture', '');
            } catch (error) {
                Debug.log('pictureinpicturepolicyfailed', {message: error});
            }
            element.addEventListener('enterpictureinpicture', function() {
                record('pipattempt');
                showMessage(config.pipblockedlabel);
                if (document.pictureInPictureElement && typeof document.exitPictureInPicture === 'function') {
                    document.exitPictureInPicture().catch(function(error) {
                        Debug.log('pictureinpictureexitfailed', {message: error});
                    });
                }
            });
        }

        var onVisibilityChange = function() {
            if (!document.hidden) {
                noteAction('tabvisible');
                return;
            }
            record('tabhidden');
            if (config.pauseonfocusloss) {
                pausePlayback('tabhidden', config.focuspausedlabel);
            }
        };
        var onWindowBlur = function() {
            if (document.hidden) {
                return;
            }
            window.setTimeout(function() {
                if (destroyed || document.hidden) {
                    return;
                }
                var active = document.activeElement;
                if (active && active.tagName && active.tagName.toLowerCase() === 'iframe'
                        && playerWrap && playerWrap.contains(active)) {
                    noteAction('providerinteraction');
                    return;
                }
                if (typeof document.hasFocus === 'function' && document.hasFocus()) {
                    return;
                }
                record('windowblur');
                if (config.pauseonfocusloss) {
                    pausePlayback('windowblur', config.focuspausedlabel);
                }
            }, 150);
        };
        var onShellInteraction = function(event) {
            if (!event || event.type === 'keydown') {
                if (event && event.key !== 'Enter' && event.key !== ' ') {
                    return;
                }
            }
            noteAction('interaction');
        };

        document.addEventListener('visibilitychange', onVisibilityChange);
        window.addEventListener('blur', onWindowBlur);
        if (shell) {
            shell.addEventListener('click', onShellInteraction, true);
            shell.addEventListener('keydown', onShellInteraction, true);
        }

        if (window.IntersectionObserver && playerWrap) {
            observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (playing && !document.hidden && entry.intersectionRatio < 0.25) {
                        record('outofviewport');
                    }
                });
            }, {threshold: [0, 0.25, 1]});
            observer.observe(playerWrap);
        }

        watchdogTimer = window.setInterval(function() {
            if (playing && !document.hidden && Date.now() - lastProgressAt > CALLBACK_TIMEOUT_MS) {
                record('callbackmissing');
                lastProgressAt = Date.now();
            }
        }, WATCHDOG_INTERVAL_MS);

        /** Remove listeners and timers. */
        function destroy() {
            destroyed = true;
            clearRandomTimer();
            if (watchdogTimer) {
                window.clearInterval(watchdogTimer);
                watchdogTimer = null;
            }
            document.removeEventListener('visibilitychange', onVisibilityChange);
            window.removeEventListener('blur', onWindowBlur);
            if (shell) {
                shell.removeEventListener('click', onShellInteraction, true);
                shell.removeEventListener('keydown', onShellInteraction, true);
            }
            if (observer) {
                observer.disconnect();
                observer = null;
            }
        }

        window.addEventListener('pagehide', destroy, {once: true});

        return {
            record: record,
            noteAction: noteAction,
            noteProgress: noteProgress,
            setPlaying: setPlaying,
            applyPictureInPicturePolicy: applyPictureInPicturePolicy,
            destroy: destroy
        };
    }

    return {
        create: create
    };
});
