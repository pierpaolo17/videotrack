/* global YT */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/check-param-names */
define([
    'core/log',
    'core/ajax',
    'mod_videotrack/core/api',
    'mod_videotrack/core/adapter',
    'mod_videotrack/core/utils',
    'mod_videotrack/core/ui',
    'mod_videotrack/core/progress',
    'mod_videotrack/core/state',
    'mod_videotrack/core/reactions',
    'mod_videotrack/core/tracker',
    'mod_videotrack/core/player'
], function(Log, Ajax, Api, Adapter, Utils, Ui, Progress, State, Reactions, Tracker, PlayerCore) {
    var player = null;
    var config = null;
    var reactionState = Reactions.createState();
    // HEARTBEAT_INTERVAL is initialised in init() from the value configured
    // by the administrator in Site administration > Plugins > Activity modules > Video track.
    var HEARTBEAT_INTERVAL = 30; // Fallback value, overridden by config.heartbeatinterval
    var state = State.create();
    state.ajaxRequestScope = Api.createRequestScope();

    function uuid() {
        return PlayerCore.uuid();
    }

    function loadApi(callback) {
        if (window.YT && window.YT.Player) {
            callback();
            return;
        }
        var previous = window.onYouTubeIframeAPIReady;
        window.onYouTubeIframeAPIReady = function() {
            if (typeof previous === 'function') {
                previous();
            }
            callback();
        };
        var tag = document.createElement('script');
        tag.src = 'https://www.youtube.com/iframe_api';
        document.head.appendChild(tag);
    }


    function updateProgress(response) {
        return Progress.updateProgress(response, state, Utils, PlayerCore, Log);
    }

    /**
     * Draw the coloured canvas bar representing watched intervals.
     * Green = watched, light grey = not watched.
     *
     * @param {string} intervaljson  JSON array di [start,end] pairs.
     * @param {number} duration Total video duration in seconds.
     */
    function updateIntervalBar(intervaljson, duration) {
        PlayerCore.updateIntervalBar(intervaljson, duration, Log);
    }

    function saveSegment(start, end, reason) {
        return Api.saveSegment(config, state, start, end, reason, {
            swallowFailures: true,
            errorMessage: 'mod_videotrack: YouTube player event failed',
            requestScope: state.ajaxRequestScope
        }).then(updateProgress);
    }

    function hasPlayer(methods, capability) {
        return Adapter.hasCapability(player, 'youtube', capability, methods);
    }

    function saveCurrentProgress(reason) {
        return PlayerCore.saveCurrentProgress(state, getCurrentVideoTime, saveSegment, reason, hasPlayer(null, 'currentTime'));
    }

    function closeCurrentSegment(reason) {
        return Tracker.closeAndSaveSegment(state, getCurrentVideoTime, saveSegment, reason, hasPlayer(null, 'currentTime'))
            .catch(Log.debug);
    }

    function startCurrentSegment() {
        var currentTime = player.getCurrentTime();
        var wallclock = Math.floor(Date.now() / 1000);
        // Feature 6: apply the maximum speed limit when configured.
        var currentRate = Adapter.getPlaybackRate(state, function() {
            return player.getPlaybackRate ? player.getPlaybackRate() : state.playbackrate;
        }, Log, 'YouTube');
        if (config.maxplaybackrate > 0) {
            var maxRate = config.maxplaybackrate / 100;
            if (currentRate > maxRate) {
                if (player.setPlaybackRate) {
                    Adapter.setPlaybackRate(maxRate, function(rate) {
                        return player.setPlaybackRate(rate);
                    }, state, Log, 'YouTube max playback rate');
                }
                currentRate = maxRate;
            }
        }
        Tracker.openSegment(state, currentTime, wallclock, currentRate);
        setReactionButtons(true);
        // Restart polling if it was suspended (hidden tab becomes visible again).
        if (!state.heartbeatid) {
            state.heartbeatid = Tracker.startPolling(state, function() {
                if (player) { handleSeekByPolling(); }
            }, HEARTBEAT_INTERVAL);
        }
    }

    function setReactionButtons(playing) {
        Reactions.setButtons(playing, config, reactionState, Ui);
    }


    function announceReactionUnavailable() {
        PlayerCore.announceReactionUnavailable(config, reactionState);
    }

    function replayFragment(start, end, autoplay) {
        if (!hasPlayer(['seekTo'])) {
            return;
        }
        state.currentReplayEnd = typeof end === 'number' ? end : null;
        // B6 fix: mark as programmatic so handleSeekByPolling ignores this seek.
        Tracker.markProgrammaticSeek(state);
        Adapter.seek(start || 0, function(target) {
            player.seekTo(target, true);
        }, Log, 'YouTube replay seek');
        if (autoplay !== false) {
            Adapter.play(function() {
                return player.playVideo();
            }, Log, 'YouTube replay play');
        }
    }

    function handleSeekByPolling() {
        if (!hasPlayer(['getCurrentTime'])) {
            return;
        }
        // B6 fix: ignore polling during programmatic seeks (replay, resume, skip buttons).
        // Reset the flag here so it stays active for exactly one polling cycle.
        if (Tracker.consumeProgrammaticSeek(state, player.getCurrentTime())) {
            return;
        }
        // If a seek was just blocked, ignore polling for 500 ms
        // so the seekTo bounce is not detected as a new anomalous seek.
        if (state.seekblocked) {
            state.lasttime = player.getCurrentTime();
            return;
        }
        var current = Tracker.normaliseTime(player.getCurrentTime());
        var delta = current - state.lasttime;
        if (Math.abs(delta) < 0.2) {
            state.lasttime = current;
            return;
        }
        var rate = Adapter.getPlaybackRate(state, function() {
            return player.getPlaybackRate ? player.getPlaybackRate() : state.playbackrate;
        }, Log, 'YouTube seek polling');
        var threshold = Math.max(2, rate * 3);
        if (state.playing && Math.abs(delta) > threshold) {
            var seek = Tracker.resolveSeek(state, current, config, 0);
            var oldtime = seek.oldTime;
            var newtime = seek.newTime;
            closeCurrentSegment('seek');
            if (seek.blocked && seek.forward) {
                Tracker.blockSeek(state, 500);
                Adapter.seek(oldtime, function(target) {
                    player.seekTo(target, true);
                }, Log, 'YouTube blocked forward seek');
                startCurrentSegment();
                return;
            }
            if (seek.blocked && seek.backward) {
                Tracker.blockSeek(state, 500);
                Adapter.seek(oldtime, function(target) {
                    player.seekTo(target, true);
                }, Log, 'YouTube blocked backward seek');
                startCurrentSegment();
                return;
            }
            // Seek permesso: apre nuovo segmento dalla posizione corrente.
            Tracker.openSegment(state, player.getCurrentTime(), Math.floor(Date.now() / 1000), state.playbackrate);
        }
        Tracker.syncTime(state, current);
        if (Tracker.shouldStopReplay(state, current)) {
            Adapter.pause(function() {
                return player.pauseVideo();
            }, Log, 'YouTube replay pause');
        }

        // Heartbeat: provider-neutral guard and error handling live in core/tracker.
        Tracker.runHeartbeat({
            state: state,
            heartbeatInterval: HEARTBEAT_INTERVAL,
            getCurrentTime: getCurrentVideoTime,
            saveSegment: saveSegment,
            hasPlayer: function() {
                return hasPlayer(['getCurrentTime']);
            },
            log: Log
        });
    }

    function onPlayerStateChange(event) {
        state.duration = Adapter.getDuration(state, function() {
            return player.getDuration ? player.getDuration() : state.duration;
        }, Log, 'YouTube');
        if (event.data === YT.PlayerState.PLAYING) {
            // Enforce maxplaybackrate: if the student raised the speed above the
            // configured limit, silently reset it to the maximum allowed value.
            // config.maxplaybackrate is in hundredths (150 = 1.5x); getPlaybackRate() returns a float.
            if (config.maxplaybackrate > 0 && player.getPlaybackRate) {
                var maxRateEnforced = config.maxplaybackrate / 100;
                var currentRate = Adapter.getPlaybackRate(state, function() {
                    return player.getPlaybackRate();
                }, Log, 'YouTube');
                if (currentRate > maxRateEnforced) {
                    Adapter.setPlaybackRate(maxRateEnforced, function(rate) {
                        return player.setPlaybackRate(rate);
                    }, state, Log, 'YouTube enforced playback rate');
                }
            }
            if (!state.playing) {
                startCurrentSegment();
            }
        } else if (event.data === YT.PlayerState.PAUSED) {
            setReactionButtons(false); // CRIT-2: disabilita bottoni su pausa
            closeCurrentSegment('pause');
        } else if (event.data === YT.PlayerState.ENDED) {
            state.ended = true;
            reactionState.readyAnnounced = false;
            setReactionButtons(false); // CRIT-2: disabilita bottoni a fine video
            closeCurrentSegment('ended');
        }
    }

    /**
     * Shows a temporary banner informing the student about automatic resume.
     * @param {number} seconds Resume position in seconds.
     */
    function showResumeNotice(seconds) {
        PlayerCore.showResumeNotice(seconds, config, Utils);
    }

    /**
     * Shows an accessible status message (aria-live) for errors or confirmations.
     * Il messaggio sparisce automaticamente dopo 4 secondi.
     *
     * @param {string}  message  Testo del messaggio.
     * @param {boolean} isError  Se true usa role=alert (assertive); altrimenti status (polite).
     */
    function installGlobalListeners() {
        Tracker.installLifecycleHandlers({
            state: state,
            closeSegment: closeCurrentSegment,
            onHidden: function() {
                setReactionButtons(false);
            },
            hasPlayer: function() { return hasPlayer(['getCurrentTime']); },
            sendBeacon: function() {
                return Tracker.sendUnloadBeacon({
                    state: state,
                    hasPlayer: function() { return hasPlayer(['getCurrentTime']); },
                    getCurrentTime: getCurrentVideoTime,
                    sendSegment: function(start, end) {
                        return PlayerCore.sendBeaconSegment(config, state, start, end, Utils, Log);
                    }
                });
            }
        });
        var root = PlayerCore.getPlayerShell(Log);
        if (!root) { return; }
        // A1 fix: keydown handler for Enter/Space on aria-disabled reaction buttons.
        // Browsers do not consistently fire 'click' for Enter/Space on buttons with
        // aria-disabled=true, so screen reader users got no feedback when pressing
        // these keys. The handler mirrors the click guard and calls the same
        // announceReactionUnavailable() function.
        root.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter' && e.key !== ' ') { return; }
            var reactionbtn = e.target.closest('.videotrack-reaction-btn');
            if (reactionbtn && reactionbtn.getAttribute('aria-disabled') === 'true') {
                e.preventDefault();
                announceReactionUnavailable();
            }
        });

        root.addEventListener('click', function(e) {
            var reactionbtn = e.target.closest('.videotrack-reaction-btn');
            if (reactionbtn && reactionbtn.getAttribute('aria-disabled') === 'true') {
                e.preventDefault();
                e.stopPropagation();
                announceReactionUnavailable();
                return;
            }
            if (reactionbtn) {
                e.preventDefault();
                var currentTime = getCurrentVideoTime();
                // Feedback visivo immediato: disabilita il bottone durante il salvataggio AJAX.
                reactionbtn.classList.add('videotrack-saving');
                saveCurrentProgress('reaction').then(function() {
                    return ajax('mod_videotrack_save_reaction', {
                        cmid: config.cmid,
                        sessionid: state.sessionid,
                        reactionid: Utils.safeInt(reactionbtn.getAttribute('data-reactionid'), 0),
                        videotime: currentTime,
                        playbackrate: Adapter.getPlaybackRate(state, function() {
                            return player.getPlaybackRate ? player.getPlaybackRate() : state.playbackrate;
                        }, Log, 'YouTube reaction')
                    });
                }).then(updateProgress).then(function(response) {
                    reactionbtn.classList.remove('videotrack-saving');
                    if (response && response.reactioneventid) {
                        appendReactionRow(response.reactioneventid, {
                            label: reactionbtn.getAttribute('data-reactionlabel') || '',
                            description: reactionbtn.getAttribute('data-reactiondesc') || '',
                            icontype: reactionbtn.getAttribute('data-reactionicontype') || 'emoji',
                            iconclass: reactionbtn.getAttribute('data-reactioniconclass') || '',
                            iconsrc: reactionbtn.getAttribute('data-reactioniconsrc') || '',
                            icontext: reactionbtn.getAttribute('data-reactionicontext') || ''
                        }, currentTime);
                    }
                }).catch(function(err) {
                    reactionbtn.classList.remove('videotrack-saving');
                    // Use the server message when available (for example, playback is required).
                    PlayerCore.showErrorStatusMessage(err, config.reactionerrorlabel, config.dismisslabel);
                });
                return;
            }
            var replaybtn = e.target.closest('.videotrack-replay');
            if (replaybtn && player) {
                replayFragment(
                    parseFloat(replaybtn.getAttribute('data-start')),
                    parseFloat(replaybtn.getAttribute('data-end')),
                    true
                );
                return;
            }
            var deletebtn = e.target.closest('.videotrack-delete-reaction');
            if (deletebtn) {
                var row = deletebtn.closest('tr');
                // Move focus before removing the row so it is not lost.
                var tbody = document.getElementById('videotrack-my-reactions');
                var rows  = tbody ? Array.from(tbody.querySelectorAll('tr[data-eventid]')) : [];
                var idx   = rows.indexOf(row);
                ajax('mod_videotrack_delete_reaction', {
                    cmid: config.cmid,
                    reactioneventid: Utils.safeInt(deletebtn.getAttribute('data-eventid'), 0)
                }).then(updateProgress).then(function(response) {
                    if (response && response.deleted) {
                        var delrow = deletebtn.closest('tr');
                        if (delrow) { delrow.remove(); }
                        // WCAG 2.4.3: restore focus after removing the row.
                        var remaining = tbody
                            ? Array.from(tbody.querySelectorAll('tr[data-eventid]'))
                            : [];
                        if (remaining.length > 0) {
                            var target = remaining[Math.min(idx, remaining.length - 1)];
                            var focusBtn = target.querySelector('button');
                            if (focusBtn) { focusBtn.focus(); }
                        } else {
                            // No rows left: move focus to the section.
                            var heading = root.querySelector('[id*="videotrack-my-reactions"]');
                            if (heading) {
                                heading.setAttribute('tabindex', '-1');
                                heading.focus();
                            }
                        }
                    }
                });
            }
        });
    }





    function appendReactionRow(eventid, reaction, videotime) {
        var tbody = document.getElementById('videotrack-my-reactions');
        if (!tbody) {
            return;
        }
        // Remove the 'no reactions yet' placeholder row when the first reaction is added.
        var placeholder = tbody.querySelector('.videotrack-no-reactions-placeholder');
        if (placeholder) { placeholder.parentNode.removeChild(placeholder); }
        var start = Math.max(0, videotime - 30);
        var end   = videotime + 30;

        // Build via DOM APIs instead of innerHTML to avoid XSS.
        // nel caso in cui iconhtml contenesse markup non atteso.
        var tr = document.createElement('tr');
        tr.dataset.eventid = eventid;

        // Cella timestamp.
        var tdTime = document.createElement('td');
        tdTime.textContent = Utils.formatSeconds(videotime);
        tr.appendChild(tdTime);

        // Icon + label cell: iconhtml comes from the PHP server already rendered as HTML
        // sicuro; rimuoviamo comunque eventuali tag <script> prima di inserirlo.
        var tdReaction = document.createElement('td');
        var iconSpan = document.createElement('span');
        iconSpan.className = 'videotrack-report-icon';
        Ui.appendIconSafe(iconSpan, reaction);
        var labelSpan = document.createElement('span');
        labelSpan.textContent = reaction.label || '';
        iconSpan.appendChild(labelSpan);
        tdReaction.appendChild(iconSpan);
        tr.appendChild(tdReaction);

        // Cella descrizione.
        var tdDesc = document.createElement('td');
        tdDesc.textContent = reaction.description || '';
        tr.appendChild(tdDesc);

        // Cella replay.
        var tdReplay = document.createElement('td');
        var replayBtn = document.createElement('button');
        replayBtn.type = 'button';
        replayBtn.className = 'btn btn-secondary btn-sm videotrack-replay';
        replayBtn.dataset.start = start;
        replayBtn.dataset.end   = end;
        replayBtn.textContent = config.replaylabel;
        replayBtn.setAttribute('aria-label',
            (config.replaylabel) + ' — ' + Utils.formatSeconds(videotime));
        tdReplay.appendChild(replayBtn);
        tr.appendChild(tdReplay);

        // Cella elimina.
        var tdDel = document.createElement('td');
        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'btn btn-link btn-sm videotrack-delete-reaction';
        delBtn.dataset.eventid = eventid;
        delBtn.textContent = config.removelabel;
        // WCAG 2.4.6: aria-label contestuale per distinguere i bottoni identici agli SR.
        delBtn.setAttribute('aria-label',
            (config.removelabel) + ' — ' + (reaction.label || '') + ' — ' + Utils.formatSeconds(videotime));
        tdDel.appendChild(delBtn);
        tr.appendChild(tdDel);

        tbody.appendChild(tr);
    }

    function buildPlayer() {
        player = new YT.Player('mod-videotrack-player', {
            videoId: config.videoid,
            playerVars: {
                autoplay:       config.autoplay ? 1 : 0,
                mute:           (config.autoplay || config.startmuted) ? 1 : 0,
                loop:           config.loop ? 1 : 0,
                playlist:       config.loop ? config.videoid : undefined,
                controls:       config.showcontrols ? 1 : 0,
                disablekb:      config.disablekeyboard ? 1 : 0,
                fs:             config.showfullscreen ? 1 : 0,
                rel:            0,
                iv_load_policy: 3,
                playsinline:    1,
                enablejsapi:    1,
                origin:         config.origin,
                // Captions: cc_load_policy=1 shows captions by default.
                cc_load_policy: config.captions ? 1 : 0,
                cc_lang_pref:   config.captionslang || '',
            },
            events: {
                onReady: function() {
                    state.duration = Adapter.getDuration(state, function() {
                        return player.getDuration ? player.getDuration() : state.duration;
                    }, Log, 'YouTube ready');
                    state.playbackrate = Adapter.getPlaybackRate(state, function() {
                        return player.getPlaybackRate ? player.getPlaybackRate() : state.playbackrate;
                    }, Log, 'YouTube ready');
                    setReactionButtons(false); // Disabled until playback starts.
                    // Add rewind/ff overlay buttons if configured.
                    buildYouTubeSkipButtons();
                    // replaystart (link diretto a un frammento) ha precedenza sul resume.
                    // If both are configured, respect the user's explicit navigation.
                    if (typeof config.replaystart === 'number' && config.replaystart >= 0) {
                        replayFragment(config.replaystart,
                            typeof config.replayend === 'number' ? config.replayend : null, true);
                    } else if (typeof config.resumeposition === 'number' && config.resumeposition > 2) {
                        // Resume from the last position (only if > 2s to avoid starting at 0:02).
                        state.isProgrammaticSeek = true; // B6 fix: resume is programmatic.
                        Adapter.seek(config.resumeposition, function(target) {
                        player.seekTo(target, true);
                    }, Log, 'YouTube resume seek');
                        showResumeNotice(config.resumeposition);
                    }
                },
                onStateChange: onPlayerStateChange,
                onAutoplayBlocked: function() {
                    // Browser has blocked autoplay. Show a visible play button
                    // so the student can start manually without confusion.
                    var wrap = document.querySelector('.videotrack-player-wrap');
                    if (wrap && !wrap.querySelector('.videotrack-autoplay-notice')) {
                        var notice = document.createElement('div');
                        notice.className = 'videotrack-autoplay-notice alert alert-info mt-1';
                        notice.setAttribute('role', 'status');
                        notice.setAttribute('aria-live', 'polite');
                        notice.textContent = config.autoblockedlabel;
                        wrap.appendChild(notice);
                        // Remove notice once the user starts playing.
                        // YouTube IFrame API addEventListener uses 'on' prefix: 'onStateChange'.
                        var removeNotice = function() {
                            if (notice && notice.parentNode) {
                                notice.parentNode.removeChild(notice);
                            }
                            player.removeEventListener('onStateChange', removeNotice);
                        };
                        player.addEventListener('onStateChange', removeNotice);
                    }
                },
                onError: function() { Log.debug('mod_videotrack: YouTube player error'); }
            }
        });
    }

    /**
     * Builds rewind and fast-forward overlay buttons for the YouTube player.
     *
     * Visibility rules (AND logic — both conditions must be true to show a button):
     *  - Rewind:       rewindstep > 0  AND  allowseekbackward === true
     *  - Fast-forward: fastforwardstep > 0  AND  allowseekforward === true
     *
     * This ensures that if the teacher has disabled forward seeking (to prevent
     * skipping ahead) the FF button never appears, even if a step is configured.
     * The same applies to rewind and allowseekbackward.
     */
    function buildYouTubeSkipButtons() {
        var showRewind = (config.rewindstep > 0) && (config.allowseekbackward !== false);
        var showFF     = (config.fastforwardstep > 0) && (config.allowseekforward !== false);
        if (!showRewind && !showFF) { return; }

        var wrap = document.getElementById('mod-videotrack-player');
        if (!wrap) { return; }
        var container = wrap.closest('.videotrack-player-wrap');
        if (!container) { return; }

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
                (config.rewindlabel) + ' ' + config.rewindstep + ' ' + (config.secondslabel));
            rwBtn.addEventListener('click', function() {
                if (player && player.getCurrentTime) {
                    Adapter.seek(
                        Adapter.resolveSkipTarget(player.getCurrentTime(), -config.rewindstep, state.duration),
                        function(target) {
                            player.seekTo(target, true);
                        },
                        Log,
                        'YouTube rewind'
                    );
                }
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
                (config.fastforwardlabel) + ' ' + config.fastforwardstep + ' ' + (config.secondslabel));
            ffBtn.addEventListener('click', function() {
                if (player && player.getCurrentTime && player.getDuration) {
                    Adapter.seek(
                        Adapter.resolveSkipTarget(player.getCurrentTime(), config.fastforwardstep, player.getDuration()),
                        function(target) {
                            player.seekTo(target, true);
                        },
                        Log,
                        'YouTube fast-forward'
                    );
                }
            });
            bar.appendChild(ffBtn);
        }

        container.appendChild(bar);
    }


    /** Returns the current video timestamp for the YouTube player. */
    function getCurrentVideoTime() {
        return Adapter.getCurrentTime(state, function() {
            return player && player.getCurrentTime ? player.getCurrentTime() : state.lasttime;
        }, Log, 'YouTube');
    }


    /**
     * Registers the reaction button click handler and replay button click handler.
     * In player.js (YouTube), reaction clicks are handled within installGlobalListeners
     * via event delegation on document. This function is a documented no-op that exists
     * to match the interface of vimeo_player.js and html5_player.js, where
     * installReactionHandler is a standalone function called from init().
     *
     * See the document.addEventListener('click', ...) block inside installGlobalListeners
     * for the actual YouTube reaction handling logic.
     */
    function installReactionHandler() {
        // Reactions for YouTube are handled via event delegation in installGlobalListeners.
        // This function intentionally left empty for API consistency across all player modules.
    }


    /**
     * Toggle show/hide del pannello note tramite helper condiviso.
     */
    function installNotesToggle() {
        PlayerCore.installNotesToggle(config, Utils, 'YouTube notes panel state');
    }

    /**
     * Feature 11: Note personali studente.
     * Gestisce salvataggio e cancellazione di note testuali timestampate.
     * The "Save" button is active only during playback (aria-disabled).
     */
    function installNoteHandler() {
        PlayerCore.installNoteHandler({
            Ajax: Ajax,
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
        if (playBtn) {
            playBtn.addEventListener('click', function() {
                removePoster();
                // Start playback if the player is ready.
                if (player && player.playVideo) {
                    Adapter.play(function() {
                        return player.playVideo();
                    }, Log, 'YouTube poster play');
                }
            });
        }

        // Remove the poster on the first YouTube PLAYING state,
        // by listening to the custom event already emitted by setReactionButtons.
        // Non riassegniamo onPlayerStateChange (function declaration, non variabile).
        state._posterRemoved = false;
        state._posterPlayListener = function(e) {
            PlayerCore.onFirstPlay(e, state, removePoster);
        };
        document.addEventListener('videotrack:playstate', state._posterPlayListener);
    }
    return {
        init: function(initConfig) {
            config = initConfig;
            PlayerCore.configureStatus(config);
            // reactionannouncementinterval is provided by PHP in milliseconds; cap matches settings.php max (120000 ms).
            var interval = parseInt(config.reactionannouncementinterval, 10);
            reactionState.unavailableInterval = interval === 0 ? Number.MAX_SAFE_INTEGER :
                Math.max(1000, Math.min(120000, interval || DEFAULT_REACTION_UNAVAILABLE_ANNOUNCE_INTERVAL));
            // reactionreadydebouncems is intentionally configured in milliseconds; cap matches settings.php max (2000 ms).
            var debounce = parseInt(config.reactionreadydebouncems, 10);
            reactionState.debounceMs = debounce === 0 ? 0 :
                Math.max(0, Math.min(2000, debounce || DEFAULT_REACTION_READY_DEBOUNCE_MS));
            // Read the heartbeat interval from the administrator configuration.
            HEARTBEAT_INTERVAL = Tracker.normaliseHeartbeatInterval(config, 30);
            state.sessionid = uuid();
            // Draw the interval bar with data already saved from previous sessions.
            if (config.intervaljson && config.duration) {
                updateIntervalBar(config.intervaljson, config.duration);
            }
            installGlobalListeners();
            installReactionHandler();
            installNoteHandler();
            installNotesToggle();
            installPosterHandler();
            loadApi(buildPlayer);
        }
    };
});
