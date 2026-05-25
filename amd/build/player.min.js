/* global YT */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/check-param-names */
define([
    'core/log',
    'core/ajax',
    'mod_videotrack/core/api',
    'mod_videotrack/core/utils',
    'mod_videotrack/core/ui',
    'mod_videotrack/core/progress',
    'mod_videotrack/core/state',
    'mod_videotrack/core/reactions',
    'mod_videotrack/core/tracker',
    'mod_videotrack/core/player'
], function(Log, Ajax, Api, Utils, Ui, Progress, State, Reactions, Tracker, PlayerCore) {
    var player = null;
    var config = null;
    var reactionState = Reactions.createState();
    // HEARTBEAT_INTERVAL viene inizializzato in init() dal valore configurato
    // dall'amministratore in Amministrazione sito → Plugin → Moduli attività → Video track.
    var HEARTBEAT_INTERVAL = 30; // valore di fallback, sovrascritto da config.heartbeatinterval
    var state = State.create();

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
     * Disegna la barra colorata degli intervalli guardati su canvas.
     * Verde = guardato, grigio chiaro = non guardato.
     *
     * @param {string} intervaljson  JSON array di [start,end] pairs.
     * @param {number} duration      Durata totale del video in secondi.
     */
    function updateIntervalBar(intervaljson, duration) {
        PlayerCore.updateIntervalBar(intervaljson, duration, Log);
    }

    function saveSegment(start, end, reason) {
        return Api.saveSegment(config, state, start, end, reason, {
            swallowFailures: true,
            errorMessage: 'mod_videotrack: YouTube player event failed'
        }).then(updateProgress);
    }

    function saveCurrentProgress(reason) {
        return PlayerCore.saveCurrentProgress(state, getCurrentVideoTime, saveSegment, reason, !!player);
    }

    function closeCurrentSegment(reason) {
        return Tracker.closeAndSaveSegment(state, getCurrentVideoTime, saveSegment, reason, !!player)
            .catch(Log.debug);
    }

    function startCurrentSegment() {
        var currentTime = player.getCurrentTime();
        var wallclock = Math.floor(Date.now() / 1000);
        // Feature 6: applica il limite massimo di velocità se configurato.
        var currentRate = player.getPlaybackRate ? player.getPlaybackRate() : 1;
        if (config.maxplaybackrate > 0) {
            var maxRate = config.maxplaybackrate / 100;
            if (currentRate > maxRate) {
                if (player.setPlaybackRate) {
                    player.setPlaybackRate(maxRate);
                }
                currentRate = maxRate;
            }
        }
        Tracker.openSegment(state, currentTime, wallclock, currentRate);
        setReactionButtons(true);
        // Riavvia il polling se era stato sospeso (tab hidden → visibile di nuovo).
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
        if (!player) {
            return;
        }
        state.currentReplayEnd = typeof end === 'number' ? end : null;
        // B6 fix: mark as programmatic so handleSeekByPolling ignores this seek.
        Tracker.markProgrammaticSeek(state);
        player.seekTo(Math.max(0, start || 0), true);
        if (autoplay !== false) {
            player.playVideo();
        }
    }

    function handleSeekByPolling() {
        if (!player || typeof player.getCurrentTime !== 'function') {
            return;
        }
        // B6 fix: ignore polling during programmatic seeks (replay, resume, skip buttons).
        // Reset the flag here so it stays active for exactly one polling cycle.
        if (Tracker.consumeProgrammaticSeek(state, player.getCurrentTime())) {
            return;
        }
        // Se un seek è stato appena bloccato, ignoriamo il polling per 500ms
        // per evitare che il rimbalzo del seekTo venga rilevato come nuovo seek anomalo.
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
        var rate = player.getPlaybackRate ? player.getPlaybackRate() : 1;
        var threshold = Math.max(2, rate * 3);
        if (state.playing && Math.abs(delta) > threshold) {
            var seek = Tracker.resolveSeek(state, current, config, 0);
            var oldtime = seek.oldTime;
            var newtime = seek.newTime;
            closeCurrentSegment('seek');
            if (seek.blocked && seek.forward) {
                state.seekblocked = true;
                window.setTimeout(function() { state.seekblocked = false; }, 500);
                player.seekTo(oldtime, true);
                startCurrentSegment();
                return;
            }
            if (seek.blocked && seek.backward) {
                state.seekblocked = true;
                window.setTimeout(function() { state.seekblocked = false; }, 500);
                player.seekTo(oldtime, true);
                startCurrentSegment();
                return;
            }
            // Seek permesso: apre nuovo segmento dalla posizione corrente.
            Tracker.openSegment(state, player.getCurrentTime(), Math.floor(Date.now() / 1000), state.playbackrate);
        }
        Tracker.syncTime(state, current);
        if (Tracker.shouldStopReplay(state, current)) {
            player.pauseVideo();
        }

        // Heartbeat: provider-neutral guard and error handling live in core/tracker.
        Tracker.runHeartbeat({
            state: state,
            heartbeatInterval: HEARTBEAT_INTERVAL,
            getCurrentTime: getCurrentVideoTime,
            saveSegment: saveSegment,
            hasPlayer: function() {
                return !!player;
            },
            log: Log
        });
    }

    function onPlayerStateChange(event) {
        state.duration = player.getDuration ? player.getDuration() : state.duration;
        if (event.data === YT.PlayerState.PLAYING) {
            // Enforce maxplaybackrate: se lo studente ha alzato la velocità oltre il
            // limite configurato, la riportiamo al massimo consentito silenziosamente.
            // config.maxplaybackrate è in centesimi (150 = 1.5×); getPlaybackRate() restituisce float.
            if (config.maxplaybackrate > 0 && player.getPlaybackRate) {
                var maxRateEnforced = config.maxplaybackrate / 100;
                var currentRate = player.getPlaybackRate();
                if (currentRate > maxRateEnforced) {
                    player.setPlaybackRate(maxRateEnforced);
                    state.playbackrate = maxRateEnforced;
                }
            }
            if (!state.playing) {
                startCurrentSegment();
            }
        } else if (event.data === YT.PlayerState.PAUSED) {
            setReactionButtons(false); // CRIT-2: disabilita bottoni su pausa
            closeCurrentSegment('pause');
        } else if (event.data === YT.PlayerState.ENDED) {
            reactionState.readyAnnounced = false;
            setReactionButtons(false); // CRIT-2: disabilita bottoni a fine video
            closeCurrentSegment('ended');
        }
    }

    /**
     * Mostra un banner temporaneo che informa lo studente del resume automatico.
     * @param {number} seconds Posizione di resume in secondi.
     */
    function showResumeNotice(seconds) {
        PlayerCore.showResumeNotice(seconds, config, Utils);
    }

    /**
     * Mostra un messaggio di stato accessibile (aria-live) per errori o conferme.
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
            hasPlayer: function() { return !!player; },
            sendBeacon: function() {
                return Tracker.sendUnloadBeacon({
                    state: state,
                    hasPlayer: function() { return !!player; },
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
                        playbackrate: player.getPlaybackRate ? player.getPlaybackRate() : 1
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
                    // Usa il messaggio del server se disponibile (es. 'Il video deve essere in riproduzione').
                    var msg = (err && err.message) ? err.message :
                        (config.reactionerrorlabel);
                    PlayerCore.showStatusMessage(msg, true, config.dismisslabel);
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
                // Sposta il focus prima di rimuovere la riga per non perderlo nel vuoto.
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
                        // WCAG 2.4.3: ripristina il focus dopo rimozione della riga.
                        var remaining = tbody
                            ? Array.from(tbody.querySelectorAll('tr[data-eventid]'))
                            : [];
                        if (remaining.length > 0) {
                            var target = remaining[Math.min(idx, remaining.length - 1)];
                            var focusBtn = target.querySelector('button');
                            if (focusBtn) { focusBtn.focus(); }
                        } else {
                            // Nessuna riga rimasta: sposta focus sulla sezione.
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
        // Rimuove la riga placeholder 'nessuna reazione' alla prima reazione aggiunta.
        var placeholder = tbody.querySelector('.videotrack-no-reactions-placeholder');
        if (placeholder) { placeholder.parentNode.removeChild(placeholder); }
        var start = Math.max(0, videotime - 30);
        var end   = videotime + 30;

        // Costruzione via DOM API invece di innerHTML per evitare XSS
        // nel caso in cui iconhtml contenesse markup non atteso.
        var tr = document.createElement('tr');
        tr.dataset.eventid = eventid;

        // Cella timestamp.
        var tdTime = document.createElement('td');
        tdTime.textContent = Utils.formatSeconds(videotime);
        tr.appendChild(tdTime);

        // Cella icona + label: iconhtml viene dal server PHP già renderizzato come HTML
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
                    state.duration = player.getDuration ? player.getDuration() : 0;
                    state.playbackrate = player.getPlaybackRate ? player.getPlaybackRate() : 1;
                    setReactionButtons(false); // Disabilitati: video non ancora in play.
                    // Add rewind/ff overlay buttons if configured.
                    buildYouTubeSkipButtons();
                    // replaystart (link diretto a un frammento) ha precedenza sul resume.
                    // Se entrambi sono configurati, si rispetta la navigazione esplicita dell'utente.
                    if (typeof config.replaystart === 'number' && config.replaystart >= 0) {
                        replayFragment(config.replaystart,
                            typeof config.replayend === 'number' ? config.replayend : null, true);
                    } else if (typeof config.resumeposition === 'number' && config.resumeposition > 2) {
                        // Resume dal punto lasciato (solo se > 2s per non partire da 0:02).
                        state.isProgrammaticSeek = true; // B6 fix: resume is programmatic.
                        player.seekTo(config.resumeposition, true);
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
                    player.seekTo(Math.max(0, player.getCurrentTime() - config.rewindstep), true);
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
                    player.seekTo(
                        Math.min(player.getDuration(), player.getCurrentTime() + config.fastforwardstep), true);
                }
            });
            bar.appendChild(ffBtn);
        }

        container.appendChild(bar);
    }


    /** Restituisce il timestamp video corrente per il player YouTube. */
    function getCurrentVideoTime() {
        try {
            if (player && player.getCurrentTime) {
                var current = player.getCurrentTime();
                if (isFinite(current)) {
                    state.lasttime = current;
                    return current;
                }
            }
        } catch (e) {
            Log.debug('mod_videotrack: could not read YouTube current time - ' + e);
        }
        return state.lasttime || 0;
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
     * Il bottone "Salva" è attivo solo durante la riproduzione (aria-disabled).
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
     * Rimuove l'overlay al primo evento PLAYING / click sul bottone play overlay.
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
                // Avvia la riproduzione se il player è pronto.
                if (player && player.playVideo) { player.playVideo(); }
            });
        }

        // Rimuove il poster al primo stato PLAYING del player YouTube,
        // ascoltando l'evento custom già emesso da setReactionButtons.
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
            // reactionannouncementinterval is provided by PHP in milliseconds; cap matches settings.php max (120000 ms).
            var interval = parseInt(config.reactionannouncementinterval, 10);
            reactionState.unavailableInterval = interval === 0 ? Number.MAX_SAFE_INTEGER :
                Math.max(1000, Math.min(120000, interval || DEFAULT_REACTION_UNAVAILABLE_ANNOUNCE_INTERVAL));
            // reactionreadydebouncems is intentionally configured in milliseconds; cap matches settings.php max (2000 ms).
            var debounce = parseInt(config.reactionreadydebouncems, 10);
            reactionState.debounceMs = debounce === 0 ? 0 :
                Math.max(0, Math.min(2000, debounce || DEFAULT_REACTION_READY_DEBOUNCE_MS));
            // Legge l'intervallo heartbeat dalla configurazione admin.
            HEARTBEAT_INTERVAL = Tracker.normaliseHeartbeatInterval(config, 30);
            state.sessionid = uuid();
            // Disegna la barra degli intervalli con i dati già salvati (sessioni precedenti).
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
