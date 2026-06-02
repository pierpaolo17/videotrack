/**
 * Vimeo player module for mod_videotrack.
 *
 * Wraps the Vimeo Player SDK (loaded from player.vimeo.com) and mirrors
 * the segment-tracking contract of the YouTube player module:
 *  - heartbeat every HEARTBEAT_INTERVAL wallclock seconds
 *  - segment saved on pause, seek, tab-change and page-hide
 *  - seek blocking when allowseekforward / allowseekbackward are off
 *
 * @module mod_videotrack/vimeo_player
 */

/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define([
    'core/log',
    'mod_videotrack/core/api',
    'mod_videotrack/core/adapter',
    'mod_videotrack/core/utils',
    'mod_videotrack/core/ui',
    'mod_videotrack/core/progress',
    'mod_videotrack/core/state',
    'mod_videotrack/core/reactions',
    'mod_videotrack/core/tracker',
    'mod_videotrack/core/player'
], function(Log, Api, Adapter, Utils, Ui, Progress, State, Reactions, Tracker, PlayerCore) {
    'use strict';


    var player  = null;
    var config  = null;
    var reactionState = Reactions.createState();
    var HEARTBEAT_INTERVAL = 30;

    var state = State.create();
    state.ajaxRequestScope = Api.createRequestScope();

    // ── Utilities ─────────────────────────────────────────────────────────

    function uuid() {
        return PlayerCore.uuid();
    }


    function saveSegment(start, end, reason) {
        return Api.saveSegment(config, state, start, end, reason, {
            swallowFailures: true,
            errorMessage: 'mod_videotrack: Vimeo player event failed',
            requestScope: state.ajaxRequestScope
        }).then(updateProgress);
    }

    function hasPlayer(methods, capability) {
        return Adapter.hasCapability(player, 'vimeo', capability, methods);
    }

    function saveCurrentProgress(reason) {
        return PlayerCore.saveCurrentProgress(state, getCurrentVideoTime, saveSegment, reason, hasPlayer(null, 'currentTime'));
    }

    function updateProgress(response) {
        return Progress.updateProgress(response, state, Utils, PlayerCore, Log);
    }

    // Segment lifecycle.

    function startSegment(currentTime) {
        Tracker.openSegment(state, currentTime, Math.floor(Date.now() / 1000));
    }

    function closeSegment(reason) {
        return Tracker.closeAndSaveSegment(state, function() {
            return player ? player.getCurrentTime() : state.lasttime;
        }, saveSegment, reason, hasPlayer(null, 'currentTime')).catch(Log.debug);
    }

    // ── Heartbeat ─────────────────────────────────────────────────────────

    function startHeartbeat() {
        Tracker.startPolling(state, function() {
            Tracker.runHeartbeat({
                state: state,
                heartbeatInterval: HEARTBEAT_INTERVAL,
                getCurrentTime: function() {
                    return player.getCurrentTime();
                },
                saveSegment: saveSegment,
                hasPlayer: function() {
                    return hasPlayer(['getCurrentTime']);
                },
                log: Log
            });
        }, HEARTBEAT_INTERVAL);
    }

    function stopHeartbeat() {
        Tracker.stopPolling(state);
    }

    // ── Global listeners ──────────────────────────────────────────────────

    /**
     * Shows a temporary banner informing the student about automatic resume.
     * @param {number} seconds Resume position in seconds.
     */
    function showResumeNotice(seconds) {
        PlayerCore.showResumeNotice(seconds, config, Utils);
    }

    function installGlobalListeners() {
        Tracker.installLifecycleHandlers({
            state: state,
            closeSegment: closeSegment,
            stopPolling: stopHeartbeat,
            onHidden: function() {
                setReactionButtons(false);
            },
            hasPlayer: function() { return hasPlayer(['getCurrentTime']); },
            sendBeacon: function() {
                return Tracker.sendUnloadBeacon({
                    state: state,
                    hasPlayer: function() { return hasPlayer(['getCurrentTime']); },
                    sendSegment: function(start, end) {
                        return PlayerCore.sendBeaconSegment(config, state, start, end, Utils, Log);
                    }
                });
            }
        });
    }

    // ── Vimeo SDK ─────────────────────────────────────────────────────────

    function loadVimeoSDK(callback) {
        if (window.Vimeo && window.Vimeo.Player) {
            callback();
            return;
        }
        var script       = document.createElement('script');
        script.src       = 'https://player.vimeo.com/api/player.js';
        script.async     = true;
        // crossorigin='anonymous' prevents credential leakage.
        // Note: Vimeo does not publish stable SRI hashes as the SDK is updated
        // dynamically; if your CSP blocks external scripts, add
        // 'player.vimeo.com' to the script-src directive.
        script.crossOrigin = 'anonymous';
        script.onload = callback;
        script.onerror = function() {
            Log.debug('mod_videotrack: failed to load Vimeo Player SDK from player.vimeo.com');
            // Show a readable user message: likely CSP or network blocking.
            var wrap = document.getElementById('mod-videotrack-player');
            if (wrap) {
                var notice = document.createElement('div');
                notice.className = 'alert alert-warning mt-2';
                notice.setAttribute('role', 'alert');
                notice.textContent = config.sdkerrorlabel;
                wrap.parentNode.insertBefore(notice, wrap.nextSibling);
            }
        };
        document.head.appendChild(script);
    }

    function buildPlayer() {
        var container = document.getElementById('mod-videotrack-player');
        if (!container) { return; }

        player = new window.Vimeo.Player(container, {
            id:          config.videoid,
            responsive:  true,
            controls:    config.showcontrols !== false,
            autoplay:    !!config.autoplay,
            loop:        !!config.loop,
            muted:       !!(config.autoplay || config.startmuted),
            fullscreen:  config.showfullscreen !== false,
            speed:       true,          // Enable SDK speed control.
            playsinline: true,
            dnt:         true,          // Do-not-track: don't store watch data on Vimeo.
        });

        // Set allowed playback speeds if the Vimeo player supports it.
        if (config.playbackspeeds && config.playbackspeeds.length) {
            // Vimeo does not have a "restrict speeds" API, but we can
            // set the initial speed to the closest available value.
            var defaultspeed = config.playbackspeeds.indexOf(1) >= 0 ? 1 :
                config.playbackspeeds[Math.floor(config.playbackspeeds.length / 2)];
            player.setPlaybackRate(defaultspeed).catch(Log.debug);
        }

        player.getDuration().then(function(d) {
            state.duration = Adapter.getDuration(state, function() {
                return d;
            }, Log, 'Vimeo duration');
            // Automatically resume from the last saved position (lastposition > 2s).
            if (typeof config.resumeposition === 'number' && config.resumeposition > 2) {
                Tracker.markProgrammaticSeek(state);
                player.setCurrentTime(config.resumeposition).then(function() {
                    state.isProgrammaticSeek = false;
                    showResumeNotice(config.resumeposition);
                }).catch(function() {
                    // iOS Safari may silently fail on setCurrentTime before play.
                    // The seek will be retried on the first 'play' event.
                    state.isProgrammaticSeek = false;
                    state._pendingResume = config.resumeposition;
                });
            }
            // Enforce maxplaybackrate when the media loads.
            // config.maxplaybackrate is stored in hundredths (150 = 1.5x); convert it to a float.
            if (config.maxplaybackrate > 0) {
                var maxRateLoad = config.maxplaybackrate / 100;
                player.getPlaybackRate().then(function(currentRate) {
                    if (currentRate > maxRateLoad) {
                        player.setPlaybackRate(maxRateLoad).catch(Log.debug);
                    }
                }).catch(Log.debug);
            }
        });

        // Captions: activate the pre-loaded Vimeo track matching the language code.
        if (config.captions && config.captionslang) {
            player.enableTextTrack(config.captionslang).catch(function() {
                // Silently ignore: track may not exist on this video.
            });
        }

        // Rewind / fast-forward overlay buttons.
        buildVimeoSkipButtons();

        player.on('play', function() {
            state.ended = false;
            player.getCurrentTime().then(function(t) {
                // iOS Safari workaround: setCurrentTime may fail before play.
                // Retry the seek on first playback when it was pending.
                if (state._pendingResume && state._pendingResume > 2) {
                    var resumePos = state._pendingResume;
                    state._pendingResume = null;
                    Tracker.markProgrammaticSeek(state);
                    player.setCurrentTime(resumePos).then(function() {
                        state.isProgrammaticSeek = false;
                        showResumeNotice(resumePos);
                    }).catch(function() {
                        state.isProgrammaticSeek = false;
                    });
                    return;
                }
                startSegment(t);
                startHeartbeat();
                setReactionButtons(true);
                // Enforce max rate on every play event because the student may have changed it.
                // config.maxplaybackrate is stored in hundredths (150 = 1.5x).
                if (config.maxplaybackrate > 0) {
                    var maxRatePlay = config.maxplaybackrate / 100;
                    player.getPlaybackRate().then(function(rate) {
                        if (rate > maxRatePlay) {
                            player.setPlaybackRate(maxRatePlay).catch(Log.debug);
                        }
                    }).catch(Log.debug);
                }
            });
        });

        player.on('pause', function() {
            if (state.ended) {
                return;
            }
            stopHeartbeat();
            closeSegment('pause');
            setReactionButtons(false);
        });

        player.on('ended', function() {
            state.ended = true;
            reactionState.readyAnnounced = false;
            stopHeartbeat();
            closeSegment('ended');
            setReactionButtons(false); // Disable buttons at the end of the video.
        });

        player.on('seeked', function(data) {
            // Ignore programmatic seeks (replay, resume): they must not trigger
            // the anti-skip block or close the current segment.
            if (state.seekblocked || Tracker.consumeProgrammaticSeek(state, data.seconds)) { return; }
            var seek = Tracker.resolveSeek(state, data.seconds, config, 0);

            if (state.playing) {
                if (seek.blocked) {
                    Tracker.blockSeek(state, 600);
                    Tracker.markProgrammaticSeek(state);
                    player.setCurrentTime(seek.fallbackTime).then(function() {
                        state.isProgrammaticSeek = false;
                    });
                    return;
                }
                // Valid seek: close current segment, open new one.
                saveSegment(state.segmentstart, seek.oldTime, 'seek');
                startSegment(seek.newTime);
            }
        });

        player.on('timeupdate', function(data) {
            Tracker.syncTime(state, data.seconds, data.playbackRate || 1);

            // Replay stop.
            if (Tracker.shouldStopReplay(state, data.seconds)) {
                Adapter.pause(function() {
                    return player.pause();
                }, Log, 'Vimeo replay pause');
            }
        });

        var root = PlayerCore.getPlayerShell(Log);
        if (!root) { return; }

        // Replay buttons.
        root.addEventListener('click', function(e) {
            var btn = e.target.closest('.videotrack-replay');
            if (btn && player) {
                var start = parseFloat(btn.dataset.start) || 0;
                var end   = parseFloat(btn.dataset.end)   || 0;
                state.currentReplayEnd = end > 0 ? end : null;
                // Mark the seek as programmatic to avoid triggering the anti-skip block.
                Tracker.markProgrammaticSeek(state);
                player.setCurrentTime(start).then(function() {
                    state.isProgrammaticSeek = false;
                    player.play();
                }).catch(function() {
                    state.isProgrammaticSeek = false;
                });
            }
        });
    }

    /**
     * Builds rewind and fast-forward overlay buttons for the Vimeo player.
     *
     * Visibility rules (AND logic):
     *  - Rewind:       rewindstep > 0  AND  allowseekbackward !== false
     *  - Fast-forward: fastforwardstep > 0  AND  allowseekforward !== false
     */
    function buildVimeoSkipButtons() {
        var showRewind = (config.rewindstep > 0) && (config.allowseekbackward !== false);
        var showFF     = (config.fastforwardstep > 0) && (config.allowseekforward !== false);
        if (!showRewind && !showFF) { return; }

        var container = document.getElementById('mod-videotrack-player');
        if (!container) { return; }
        var wrap = container.closest('.videotrack-player-wrap') || container.parentElement;

        var bar = document.createElement('div');
        bar.className = 'videotrack-skip-bar';

        if (showRewind) {
            var rwBtn = document.createElement('button');
            rwBtn.type = 'button';
            rwBtn.className = 'btn btn-sm btn-dark videotrack-skip-btn';
            var rwIcon = document.createElement('span');
            rwIcon.setAttribute('aria-hidden', 'true');
            rwIcon.textContent = '⏪ ';
            rwBtn.appendChild(rwIcon);
            rwBtn.appendChild(document.createTextNode(config.rewindstep + 's'));
            rwBtn.setAttribute('aria-label',
                (config.rewindlabel) + ' ' + config.rewindstep +
                ' ' + (config.secondslabel));
            rwBtn.addEventListener('click', function() {
                player.getCurrentTime().then(function(t) {
                    Adapter.seek(
                        Adapter.resolveSkipTarget(t, -config.rewindstep, state.duration),
                        function(target) {
                            return player.setCurrentTime(target);
                        },
                        Log,
                        'Vimeo rewind'
                    );
                });
            });
            bar.appendChild(rwBtn);
        }

        if (showFF) {
            var ffBtn = document.createElement('button');
            ffBtn.type = 'button';
            ffBtn.className = 'btn btn-sm btn-dark videotrack-skip-btn';
            ffBtn.appendChild(document.createTextNode(config.fastforwardstep + 's '));
            var ffIcon = document.createElement('span');
            ffIcon.setAttribute('aria-hidden', 'true');
            ffIcon.textContent = '⏩';
            ffBtn.appendChild(ffIcon);
            ffBtn.setAttribute('aria-label',
                (config.fastforwardlabel) + ' ' + config.fastforwardstep +
                ' ' + (config.secondslabel));
            ffBtn.addEventListener('click', function() {
                player.getCurrentTime().then(function(t) {
                    Adapter.seek(
                        Adapter.resolveSkipTarget(t, config.fastforwardstep, state.duration),
                        function(target) {
                            return player.setCurrentTime(target);
                        },
                        Log,
                        'Vimeo fast-forward'
                    );
                });
            });
            bar.appendChild(ffBtn);
        }

        wrap.appendChild(bar);
    }


    // ── Reaction buttons ──────────────────────────────────────────────────────

    /**
     * Enables or disables the reaction buttons based on playback state.
     * Uses only aria-disabled to keep inactive controls keyboard-focusable.
     * The click/keydown handlers block saving and announce why the action is unavailable.
     * By-design: reactions can only be saved while the video is playing.
     *
     * @param {boolean} playing  True = enable buttons; false = disable them.
     */
    function setReactionButtons(playing) {
        Reactions.setButtons(playing, config, reactionState, Ui);
    }


    function announceReactionUnavailable() {
        PlayerCore.announceReactionUnavailable(config, reactionState);
    }


    function installReactionHandler() {
        var root = PlayerCore.getPlayerShell(Log);
        if (!root) { return; }

        function appendReactionRow(eventid, reaction, videotime) {
            var tbody = document.getElementById('videotrack-my-reactions');
            if (!tbody) { return; }
            // Remove the 'no reactions yet' placeholder row when the first reaction is added.
            var placeholder = tbody.querySelector('.videotrack-no-reactions-placeholder');
            if (placeholder) { placeholder.parentNode.removeChild(placeholder); }
            var tr = document.createElement('tr');
            tr.setAttribute('data-eventid', eventid);
            // Timestamp cell, formatted as MM:SS for readability.
            var tdtime = document.createElement('td');
            tdtime.textContent = Utils.formatSeconds(videotime);
            tr.appendChild(tdtime);
            // Icon cell
            var tdicon = document.createElement('td');
            var span = document.createElement('span');
            span.className = 'videotrack-report-icon';
            Ui.appendIconSafe(span, reaction);
            tdicon.appendChild(span);
            tr.appendChild(tdicon);
            // Description
            var tddesc = document.createElement('td');
            tddesc.textContent = reaction.description || '';
            tr.appendChild(tddesc);
            // Replay
            var tdreplay = document.createElement('td');
            var replaybtn = document.createElement('button');
            replaybtn.type = 'button';
            replaybtn.className = 'btn btn-secondary btn-sm videotrack-replay';
            replaybtn.textContent = config.replaylabel;
            replaybtn.setAttribute('aria-label',
                (config.replaylabel) + ' — ' + Utils.formatSeconds(videotime));
            replaybtn.dataset.start = Math.max(0, videotime - 30);
            replaybtn.dataset.end   = videotime + 30;
            tdreplay.appendChild(replaybtn);
            tr.appendChild(tdreplay);
            // Delete
            var tddel = document.createElement('td');
            var delbtn = document.createElement('button');
            delbtn.type = 'button';
            delbtn.className = 'btn btn-link btn-sm videotrack-delete-reaction';
            delbtn.textContent = config.removelabel;
            delbtn.setAttribute('data-eventid', eventid);
            // Descriptive aria-label for screen readers: contextualises the action.
            delbtn.setAttribute('aria-label',
                (config.removelabel) + ' — ' + (reaction.label || '') + ' — ' + Utils.formatSeconds(videotime));
            tddel.appendChild(delbtn);
            tr.appendChild(tddel);
            tbody.appendChild(tr);
        }

        // A1 fix: keydown handler for Enter/Space on aria-disabled reaction buttons.
        // Browsers do not consistently fire 'click' for Enter/Space on buttons with
        // aria-disabled=true, so screen reader users got no feedback.
        if (state._reactionRootCleanup) {
            state._reactionRootCleanup();
        }
        var reactionKeydownHandler = function(e) {
            if (e.key !== 'Enter' && e.key !== ' ') { return; }
            var reactionbtn = e.target.closest('.videotrack-reaction-btn');
            if (reactionbtn && reactionbtn.getAttribute('aria-disabled') === 'true') {
                e.preventDefault();
                announceReactionUnavailable();
            }
        };

        var reactionClickHandler = function(e) {
            var reactionbtn = e.target.closest('.videotrack-reaction-btn');
            if (reactionbtn && reactionbtn.getAttribute('aria-disabled') === 'true') {
                e.preventDefault();
                e.stopPropagation();
                announceReactionUnavailable();
                return;
            }
            if (reactionbtn) {
                e.preventDefault();
                if (reactionbtn.getAttribute('aria-busy') === 'true') {
                    return;
                }
                var currentTime = state.lasttime || 0;
                reactionbtn.classList.add('videotrack-saving');
                reactionbtn.setAttribute('aria-busy', 'true');
                reactionbtn.disabled = true;
                saveCurrentProgress('reaction').then(function() {
                    return Api.call('mod_videotrack_save_reaction', {
                        cmid:       config.cmid,
                        sessionid:  state.sessionid,
                        reactionid: Utils.safeInt(reactionbtn.getAttribute('data-reactionid'), 0),
                        videotime:  currentTime,
                        playbackrate: state.playbackrate || 1,
                    });
                }).then(function(response) {
                    reactionbtn.classList.remove('videotrack-saving');
                    reactionbtn.removeAttribute('aria-busy');
                    reactionbtn.disabled = false;
                    if (response && response.reactioneventid) {
                        appendReactionRow(response.reactioneventid, {
                            label: reactionbtn.getAttribute('data-reactionlabel') || '',
                            description: reactionbtn.getAttribute('data-reactiondesc') || '',
                            icontype: reactionbtn.getAttribute('data-reactionicontype') || 'emoji',
                            iconclass: reactionbtn.getAttribute('data-reactioniconclass') || '',
                            iconsrc: reactionbtn.getAttribute('data-reactioniconsrc') || '',
                            icontext: reactionbtn.getAttribute('data-reactionicontext') || '',
                        }, currentTime);
                    }
                }).catch(function(err) {
                    reactionbtn.classList.remove('videotrack-saving');
                    reactionbtn.removeAttribute('aria-busy');
                    reactionbtn.disabled = false;
                    PlayerCore.showErrorStatusMessage(err, config.reactionerrorlabel, config.dismisslabel);
                });
                return;
            }

            var deletebtn = e.target.closest('.videotrack-delete-reaction');
            if (deletebtn) {
                var row   = deletebtn.closest('tr');
                var tbody = document.getElementById('videotrack-my-reactions');
                var rows  = tbody ? Array.from(tbody.querySelectorAll('tr[data-eventid]')) : [];
                var idx   = rows.indexOf(row);
                Api.call('mod_videotrack_delete_reaction', {
                    cmid: config.cmid,
                    reactioneventid: Utils.safeInt(deletebtn.getAttribute('data-eventid'), 0),
                }).then(updateProgress).then(function(response) {
                    if (response && response.deleted) {
                        if (row) { row.remove(); }
                        var remaining = tbody
                            ? Array.from(tbody.querySelectorAll('tr[data-eventid]'))
                            : [];
                        if (remaining.length > 0) {
                            var target = remaining[Math.min(idx, remaining.length - 1)];
                            var focusBtn = target.querySelector('button');
                            if (focusBtn) { focusBtn.focus(); }
                        } else if (tbody) {
                            tbody.setAttribute('tabindex', '-1');
                            tbody.focus();
                            tbody.removeAttribute('tabindex');
                        }
                    }
                }).catch(function(err) {
                    PlayerCore.showErrorStatusMessage(err, config.reactionerrorlabel, config.dismisslabel);
                });
            }
        };
        root.addEventListener('keydown', reactionKeydownHandler);
        root.addEventListener('click', reactionClickHandler);
        var cleanupReactionRootHandlers = function() {
            root.removeEventListener('keydown', reactionKeydownHandler);
            root.removeEventListener('click', reactionClickHandler);
            window.removeEventListener('pagehide', cleanupReactionRootHandlers);
            window.removeEventListener('beforeunload', cleanupReactionRootHandlers);
            if (state._reactionRootCleanup === cleanupReactionRootHandlers) {
                state._reactionRootCleanup = null;
            }
        };
        state._reactionRootCleanup = cleanupReactionRootHandlers;
        window.addEventListener('pagehide', cleanupReactionRootHandlers, {once: true});
        window.addEventListener('beforeunload', cleanupReactionRootHandlers, {once: true});
    }






    /** Returns the current video timestamp for the Vimeo player, using lasttime as sync fallback. */
    function getCurrentVideoTime() {
        return Adapter.getCurrentTime(state, function() {
            return state.lasttime;
        }, Log, 'Vimeo');
    }


    /**
     * Toggle show/hide del pannello note tramite helper condiviso.
     */
    function installNotesToggle() {
        PlayerCore.installNotesToggle(config, Utils, 'Vimeo notes panel state');
    }

    /**
     * Feature 11: Note personali studente.
     * Gestisce salvataggio e cancellazione di note testuali timestampate.
     * The "Save" button is active only during playback (aria-disabled).
     */
    function installNoteHandler() {
        PlayerCore.installNoteHandler({
            Api: Api,
            Log: Log,
            Utils: Utils,
            config: config,
            state: state,
            getCurrentVideoTime: getCurrentVideoTime,
            saveCurrentProgress: saveCurrentProgress
        });
    }

    /**
     * Feature 12: Gestione overlay poster pre-play.
     * Removes the overlay on the first PLAYING event or overlay play button click.
     */
    function installPosterHandler() {
        var overlay = document.getElementById('videotrack-poster-overlay');
        if (!overlay) { return; } // Nessun poster caricato.

        function removePoster() {
            PlayerCore.removePoster(overlay);
        }

        var playBtn = document.getElementById('videotrack-poster-play-btn');
        var posterClickHandler = function() {
            removePoster();
            // Start playback with the Vimeo SDK API (not player.playVideo, which is YouTube).
            if (player && player.play) {
                var posterPlay = Adapter.play(function() {
                    return player.play();
                }, Log, 'Vimeo poster play');
                if (posterPlay && typeof posterPlay.catch === 'function') {
                    posterPlay.catch(function(err) {
                        Log.debug('mod_videotrack: play request failed - ' + err);
                    });
                }
            }
        };
        if (playBtn) {
            playBtn.addEventListener('click', posterClickHandler);
        }

        // Remove the poster on the first Vimeo play event.
        state._posterRemoved = false;
        state._posterPlayListener = function(e) {
            PlayerCore.onFirstPlay(e, state, removePoster);
        };
        document.addEventListener('videotrack:playstate', state._posterPlayListener);

        state._posterCleanup = function() {
            if (playBtn) {
                playBtn.removeEventListener('click', posterClickHandler);
            }
            if (state._posterPlayListener) {
                document.removeEventListener('videotrack:playstate', state._posterPlayListener);
                state._posterPlayListener = null;
            }
            window.removeEventListener('pagehide', state._posterCleanup);
            window.removeEventListener('beforeunload', state._posterCleanup);
            state._posterCleanup = null;
        };
        window.addEventListener('pagehide', state._posterCleanup, {once: true});
        window.addEventListener('beforeunload', state._posterCleanup, {once: true});
    }
    // ── Public API ────────────────────────────────────────────────────────

    return {
        init: function(initConfig) {
            config             = initConfig;
            PlayerCore.configureStatus(config);
            // reactionannouncementinterval is provided by PHP in milliseconds; cap matches settings.php max (120000 ms).
            var interval = parseInt(config.reactionannouncementinterval, 10);
            reactionState.unavailableInterval = interval === 0 ? Number.MAX_SAFE_INTEGER :
                Math.max(1000, Math.min(120000, interval || Reactions.DEFAULT_UNAVAILABLE_ANNOUNCE_INTERVAL));
            // reactionreadydebouncems is intentionally configured in milliseconds; cap matches settings.php max (2000 ms).
            var debounce = parseInt(config.reactionreadydebouncems, 10);
            reactionState.debounceMs = debounce === 0 ? 0 :
                Math.max(0, Math.min(2000, debounce || Reactions.DEFAULT_READY_DEBOUNCE_MS));
            HEARTBEAT_INTERVAL = Tracker.normaliseHeartbeatInterval(config, 30);
            state.sessionid    = uuid();
            installGlobalListeners();
            installReactionHandler();
            installNoteHandler();
            installNotesToggle();
            installPosterHandler();
            loadVimeoSDK(buildPlayer);
        }
    };
});
