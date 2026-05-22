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

/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/check-param-names */define([
    'core/ajax',
    'core/log',
    'mod_videotrack/core/utils',
    'mod_videotrack/core/ui',
    'mod_videotrack/core/player'
], function(Ajax, Log, Utils, Ui, PlayerCore) {

    var player  = null;
    var config  = null;
    var lastReactionAvailabilityAnnouncement = null;
    var reactionReadyAnnounced = false;
    var reactionUnavailableTimer = null;
    var lastReactionUnavailableAt = 0;
    var DEFAULT_REACTION_UNAVAILABLE_ANNOUNCE_INTERVAL = 30000;
    var DEFAULT_REACTION_READY_DEBOUNCE_MS = 400;
    var reactionReadyDebounceMs = DEFAULT_REACTION_READY_DEBOUNCE_MS;
    var reactionUnavailableAnnounceInterval = DEFAULT_REACTION_UNAVAILABLE_ANNOUNCE_INTERVAL;
    var HEARTBEAT_INTERVAL = 30;

    var state = {
        sessionid:            null,
        playing:              false,
        segmentstart:         null,
        wallclockstart:       null,
        lasttime:             0,
        duration:             0,
        playbackrate:         1,
        heartbeatid:          null,
        lastHeartbeatWallclock: 0,
        seekblocked:          false,
        currentReplayEnd:     null,
        isProgrammaticSeek:   false, // True durante seek lanciati dal codice (replay, resume).
    };

    // ── Utilities ─────────────────────────────────────────────────────────

    function uuid() {
        return PlayerCore.uuid();
    }


    function ajax(methodname, args) {
        return Ajax.call([{
            methodname: methodname,
            args: args
        }])[0];
    }

    function saveSegment(start, end, reason) {
        if (end <= start) { return; }
        var now = Math.floor(Date.now() / 1000);
        return ajax('mod_videotrack_save_segment', {
            cmid:            config.cmid,
            sessionid:       state.sessionid,
            videotimestart:  Math.round(start * 1000) / 1000,
            videotimeend:    Math.round(end   * 1000) / 1000,
            wallclockstart:  state.wallclockstart || now,
            wallclockend:    now,
            playbackrate:    state.playbackrate || 1,
            endreason:       reason,
            durationseconds: state.duration,
        }).then(updateProgress).catch(Log.debug);
    }

    function saveCurrentProgress(reason) {
        if (!state.playing || state.segmentstart === null || !player) {
            return Promise.resolve(null);
        }
        var end = getCurrentVideoTime();
        if (end <= state.segmentstart) {
            if (reason === 'reaction' || reason === 'note') {
                end = state.segmentstart + 0.25;
                if (state.duration && state.duration > 0) {
                    end = Math.min(end, state.duration);
                }
            }
            if (end <= state.segmentstart) {
                return Promise.resolve(null);
            }
        }
        return saveSegment(state.segmentstart, end, reason || 'interaction');
    }

    function updateProgress(response) {
        if (!response) { return response; }
        if (response.accepted === false) {
            Log.debug('mod_videotrack: segment write deferred due to lock contention');
        }
        var pct = document.getElementById('videotrack-progress-percent');
        if (pct) { pct.textContent = parseFloat(response.completionpercent || 0).toFixed(1) + '%'; }
        var sec = document.getElementById('videotrack-covered-seconds');
        if (sec) { sec.textContent = Utils.formatSeconds(response.uniquecoveredseconds || 0); }
        // Aggiorna contatore reazioni univoche.
        if (typeof response.uniquereactions !== 'undefined') {
            var counter = document.getElementById('videotrack-unique-reactions');
            if (counter) { counter.textContent = response.uniquereactions; }
        }
        // Aggiorna la barra visuale degli intervalli guardati.
        if (response.intervaljson) {
            updateIntervalBar(response.intervaljson, response.durationseconds || state.duration);
        }
        return response;
    }

    // ── Segment lifecycle ─────────────────────────────────────────────────

    function startSegment(currentTime) {
        state.playing              = true;
        state.segmentstart         = currentTime;
        state.wallclockstart       = Math.floor(Date.now() / 1000);
        state.lastHeartbeatWallclock = state.wallclockstart;
        state.lasttime             = currentTime;
    }

    function closeSegment(reason) {
        if (!state.playing || state.segmentstart === null) { return; }
        player.getCurrentTime().then(function(t) {
            saveSegment(state.segmentstart, t, reason);
            state.playing      = false;
            state.segmentstart = null;
        });
    }

    // ── Heartbeat ─────────────────────────────────────────────────────────

    function startHeartbeat() {
        if (state.heartbeatid) { return; }
        state.heartbeatid = window.setInterval(function() {
            if (!state.playing || state.segmentstart === null) { return; }
            var now = Math.floor(Date.now() / 1000);
            if (now - state.lastHeartbeatWallclock >= HEARTBEAT_INTERVAL) {
                player.getCurrentTime().then(function(t) {
                    saveSegment(state.segmentstart, t, 'heartbeat');
                    state.segmentstart           = t;
                    state.wallclockstart         = now;
                    state.lastHeartbeatWallclock = now;
                });
            }
        }, 2000);
    }

    function stopHeartbeat() {
        if (state.heartbeatid) {
            window.clearInterval(state.heartbeatid);
            state.heartbeatid = null;
        }
    }

    // ── Global listeners ──────────────────────────────────────────────────

    /**
     * Mostra un banner temporaneo che informa lo studente del resume automatico.
     * @param {number} seconds Posizione di resume in secondi.
     */
    function showResumeNotice(seconds) {
        PlayerCore.showResumeNotice(seconds, config, Utils);
    }


    /**
     * Disegna la barra colorata degli intervalli guardati su canvas.
     * Identica all'implementazione in player.js e html5_player.js.
     *
     * @param {string} intervaljson  JSON array di [start,end] pairs.
     * @param {number} duration      Durata totale del video in secondi.
     */
    function getIntervalBarColor(canvas, property, fallback) {
        return PlayerCore.getIntervalBarColor(canvas, property, fallback);
    }

    function updateIntervalBar(intervaljson, duration) {
        var canvas = document.getElementById('videotrack-interval-bar');
        if (!canvas || !duration) { return; }
        try {
            var intervals = JSON.parse(intervaljson);
            var ctx = canvas.getContext('2d');
            var w   = canvas.offsetWidth || canvas.width;
            var h   = canvas.height;
            canvas.width = w;
            ctx.clearRect(0, 0, w, h);
            ctx.fillStyle = getIntervalBarColor(canvas, '--videotrack-interval-bg', '#e9ecef');
            ctx.fillRect(0, 0, w, h);
            ctx.fillStyle = getIntervalBarColor(canvas, '--videotrack-interval-fill', '#28a745');
            var covered = 0;
            intervals.forEach(function(seg) {
                var x1 = Math.round((seg[0] / duration) * w);
                var x2 = Math.round((seg[1] / duration) * w);
                ctx.fillRect(x1, 0, Math.max(2, x2 - x1), h);
                covered += Math.max(0, seg[1] - seg[0]);
            });
            var pct = duration > 0 ? Math.min(100, Math.round((covered / duration) * 100)) : 0;
            var baseLabel = canvas.getAttribute('title') || '';
            canvas.setAttribute('aria-label', baseLabel + ' — ' + pct + '%');
        } catch (e) {
            Log.debug('mod_videotrack: invalid interval JSON - ' + e);
        }
    }

    function installGlobalListeners() {
        document.addEventListener('visibilitychange', function() {
            if (document.hidden && state.playing) {
                closeSegment('tab');
                setReactionButtons(false); // Disabilita bottoni quando la tab è nascosta.
            }
        });
        window.addEventListener('pagehide', function() {
            if (state.playing) { closeSegment('pagehide'); }
        });
        // sendBeacon: fallback sincrono per browser che cancellano fetch su unload.
        window.addEventListener('beforeunload', function() {
            if (!state.playing || state.segmentstart === null) { return; }
            var start = state.segmentstart;
            var end   = state.lasttime;
            if (end <= start) { return; }
            var now = Math.floor(Date.now() / 1000);
            var beaconUrl = config.beaconurl || '';
            if (navigator.sendBeacon && beaconUrl) {
                navigator.sendBeacon(
                    beaconUrl,
                    new Blob([JSON.stringify([{
                        methodname: 'mod_videotrack_save_segment',
                        args: {
                            cmid: config.cmid, sessionid: state.sessionid,
                            videotimestart: start, videotimeend: end,
                            wallclockstart: state.wallclockstart || now, wallclockend: now,
                            playbackrate: state.playbackrate || 1,
                            endreason: 'beforeunload',
                            durationseconds: state.duration || 0,
                        }
                    }])], {type: 'application/json'})
                );
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
            // Mostra un messaggio utente leggibile: probabile blocco CSP o rete.
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
            state.duration = d;
            // Resume automatico dal punto lasciato (lastposition > 2s).
            if (typeof config.resumeposition === 'number' && config.resumeposition > 2) {
                state.isProgrammaticSeek = true;
                player.setCurrentTime(config.resumeposition).then(function() {
                    state.isProgrammaticSeek = false;
                    showResumeNotice(config.resumeposition);
                }).catch(function() {
                    // iOS Safari fallisce silenziosamente su setCurrentTime prima del play.
                    // Il seek verrà ritentato sull'evento 'play' la prima volta.
                    state.isProgrammaticSeek = false;
                    state._pendingResume = config.resumeposition;
                });
            }
            // Enforce maxplaybackrate al caricamento.
            // config.maxplaybackrate è in centesimi (150 = 1.5×); convertire a float.
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
            player.getCurrentTime().then(function(t) {
                // iOS Safari workaround: setCurrentTime prima del play fallisce.
                // Riprova il seek alla prima riproduzione se era pendente.
                if (state._pendingResume && state._pendingResume > 2) {
                    var resumePos = state._pendingResume;
                    state._pendingResume = null;
                    state.isProgrammaticSeek = true;
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
                // Enforce max rate ad ogni play (lo studente potrebbe averla cambiata).
                // config.maxplaybackrate è in centesimi (150 = 1.5×).
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
            stopHeartbeat();
            closeSegment('pause');
            setReactionButtons(false);
        });

        player.on('ended', function() {
            reactionReadyAnnounced = false;
            stopHeartbeat();
            closeSegment('ended');
            setReactionButtons(false); // Disabilita bottoni a fine video.
        });

        player.on('seeked', function(data) {
            // Ignora seek programmatici (replay, resume): non devono triggerare
            // il blocco anti-skip né chiudere il segmento corrente.
            if (state.seekblocked || state.isProgrammaticSeek) { return; }
            var newtime = data.seconds;
            var oldtime = state.lasttime;

            if (state.playing) {
                if (!config.allowseekforward && newtime > oldtime) {
                    state.seekblocked = true;
                    window.setTimeout(function() { state.seekblocked = false; }, 600);
                    state.isProgrammaticSeek = true;
                    player.setCurrentTime(oldtime).then(function() {
                        state.isProgrammaticSeek = false;
                    });
                    return;
                }
                if (!config.allowseekbackward && newtime < oldtime) {
                    state.seekblocked = true;
                    window.setTimeout(function() { state.seekblocked = false; }, 600);
                    state.isProgrammaticSeek = true;
                    player.setCurrentTime(oldtime).then(function() {
                        state.isProgrammaticSeek = false;
                    });
                    return;
                }
                // Valid seek: close current segment, open new one.
                saveSegment(state.segmentstart, oldtime, 'seek');
                startSegment(newtime);
            }
        });

        player.on('timeupdate', function(data) {
            state.lasttime     = data.seconds;
            state.playbackrate = data.playbackRate || 1;

            // Replay stop.
            if (state.currentReplayEnd !== null && data.seconds >= state.currentReplayEnd) {
                player.pause();
                state.currentReplayEnd = null;
            }
        });

        // Replay buttons.
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.videotrack-replay');
            if (btn && player) {
                var start = parseFloat(btn.dataset.start) || 0;
                var end   = parseFloat(btn.dataset.end)   || 0;
                state.currentReplayEnd = end > 0 ? end : null;
                // Marca il seek come programmatico per non triggerare il blocco anti-skip.
                state.isProgrammaticSeek = true;
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
                    player.setCurrentTime(Math.max(0, t - config.rewindstep));
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
                    player.setCurrentTime(Math.min(state.duration || 1e9, t + config.fastforwardstep));
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
        Ui.setReactionButtons(playing);
        announceReactionAvailability(playing);
    }


    function announceReactionAvailability(playing) {
        var hint = document.getElementById('videotrack-reactions-hint');
        if (!hint) {
            return;
        }
        if (playing) {
            if (reactionUnavailableTimer) {
                window.clearTimeout(reactionUnavailableTimer);
                reactionUnavailableTimer = null;
            }
            if (Date.now() - lastReactionUnavailableAt < reactionReadyDebounceMs) {
                return;
            }
            if (reactionReadyAnnounced || lastReactionAvailabilityAnnouncement === true) {
                return;
            }
            lastReactionAvailabilityAnnouncement = true;
            reactionReadyAnnounced = true;
            hint.textContent = config.reactionsreadylabel;
            hint.classList.toggle('videotrack-reactions-hint-active', false);
            return;
        }

        if (reactionUnavailableTimer) {
            return;
        }
        var now = Date.now();
        if (lastReactionAvailabilityAnnouncement === false &&
                now - lastReactionUnavailableAt < reactionUnavailableAnnounceInterval) {
            return;
        }
        reactionUnavailableTimer = window.setTimeout(function() {
            reactionUnavailableTimer = null;
            lastReactionAvailabilityAnnouncement = false;
            lastReactionUnavailableAt = Date.now();
            hint.textContent = config.reactionunavailablelabel;
            hint.classList.toggle('videotrack-reactions-hint-active', true);
        }, 400);
    }


    function announceReactionUnavailable() {
        var hint = document.getElementById('videotrack-reactions-hint');
        if (hint) {
            if (reactionUnavailableTimer) {
                window.clearTimeout(reactionUnavailableTimer);
                reactionUnavailableTimer = null;
            }
            var now = Date.now();
            if (lastReactionAvailabilityAnnouncement === false && now - lastReactionUnavailableAt < 1000) {
                return;
            }
            lastReactionAvailabilityAnnouncement = false;
            lastReactionUnavailableAt = now;
            hint.textContent = config.reactionunavailablelabel;
            hint.classList.add('videotrack-reactions-hint-active');
            window.setTimeout(function() {
                hint.classList.remove('videotrack-reactions-hint-active');
            }, 1500);
        }
    }


    /**
     * Installs the click handler for reaction buttons and replay buttons.
     * Mirrors the logic of player.js to ensure consistent behaviour across all sources.
     */
    function showStatusMessage(message, isError) {
        var id = 'videotrack-status-msg';
        var el = document.getElementById(id);
        if (!el) {
            el = document.createElement('div');
            el.id = id;
            el.className = 'sr-only';
            el.setAttribute('aria-atomic', 'true');
            var shell = document.querySelector('.videotrack-player-shell');
            if (shell) { shell.appendChild(el); }
        }
        el.setAttribute('role', isError ? 'alert' : 'status');
        el.textContent = message;
        window.setTimeout(function() { el.textContent = ''; }, isError ? 8000 : 4000);
    }


    function installReactionHandler() {

        function appendReactionRow(eventid, reaction, videotime) {
            var tbody = document.getElementById('videotrack-my-reactions');
            if (!tbody) { return; }
            // Rimuove la riga placeholder 'nessuna reazione' alla prima reazione aggiunta.
            var placeholder = tbody.querySelector('.videotrack-no-reactions-placeholder');
            if (placeholder) { placeholder.parentNode.removeChild(placeholder); }
            var tr = document.createElement('tr');
            tr.setAttribute('data-eventid', eventid);
            // Timestamp cell — formattato MM:SS per leggibilità.
            var tdtime = document.createElement('td');
            tdtime.textContent = Utils.formatSeconds(videotime);
            tr.appendChild(tdtime);
            // Icon cell
            var tdicon = document.createElement('td');
            var span = document.createElement('span');
            span.className = 'videotrack-report-icon';
            Ui.appendIconSafe(span, reaction.iconhtml);
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
            // Aria-label descrittivo per screen reader: contestualizza l'azione.
            delbtn.setAttribute('aria-label',
                (config.removelabel) + ' — ' + (reaction.label || '') + ' — ' + Utils.formatSeconds(videotime));
            tddel.appendChild(delbtn);
            tr.appendChild(tddel);
            tbody.appendChild(tr);
        }

        // A1 fix: keydown handler for Enter/Space on aria-disabled reaction buttons.
        // Browsers do not consistently fire 'click' for Enter/Space on buttons with
        // aria-disabled=true, so screen reader users got no feedback.
        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter' && e.key !== ' ') { return; }
            var reactionbtn = e.target.closest('.videotrack-reaction-btn');
            if (reactionbtn && reactionbtn.getAttribute('aria-disabled') === 'true') {
                e.preventDefault();
                announceReactionUnavailable();
            }
        });

        document.addEventListener('click', function(e) {
            var reactionbtn = e.target.closest('.videotrack-reaction-btn');
            if (reactionbtn && reactionbtn.getAttribute('aria-disabled') === 'true') {
                announceReactionUnavailable();
                return;
            }
            if (reactionbtn) {
                var currentTime = state.lasttime || 0;
                reactionbtn.classList.add('videotrack-saving');
                saveCurrentProgress('reaction').then(function() {
                    return ajax('mod_videotrack_save_reaction', {
                        cmid:       config.cmid,
                        sessionid:  state.sessionid,
                        reactionid: Utils.safeInt(reactionbtn.getAttribute('data-reactionid'), 0),
                        videotime:  currentTime,
                        playbackrate: state.playbackrate || 1,
                    });
                }).then(function(response) {
                    reactionbtn.classList.remove('videotrack-saving');
                    if (response && response.reactioneventid) {
                        appendReactionRow(response.reactioneventid, {
                            label:    reactionbtn.getAttribute('data-reactionlabel'),
                            description: reactionbtn.getAttribute('data-reactiondesc') || '',
                            iconhtml: reactionbtn.getAttribute('data-reactioniconhtml') || '',
                        }, currentTime);
                    }
                }).catch(function(err) {
                    reactionbtn.classList.remove('videotrack-saving');
                    var msg = (err && err.message) ? err.message :
                        (config.reactionerrorlabel);
                    showStatusMessage(msg, true);
                });
                return;
            }

            var deletebtn = e.target.closest('.videotrack-delete-reaction');
            if (deletebtn) {
                var row   = deletebtn.closest('tr');
                var tbody = document.getElementById('videotrack-my-reactions');
                var rows  = tbody ? Array.from(tbody.querySelectorAll('tr[data-eventid]')) : [];
                var idx   = rows.indexOf(row);
                ajax('mod_videotrack_delete_reaction', {
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
                }).catch(Log.debug);
            }
        });
    }






    /** Restituisce il timestamp video corrente per il player Vimeo (usa lasttime — sync). */
    function getCurrentVideoTime() {
        return state.lasttime || 0;
    }


    /**
     * Toggle show/hide del pannello note: gestisce il bottone collapse e persiste
     * la preferenza in sessionStorage per la durata della sessione.
     */

    function installNotesToggle() {
        var btn  = document.getElementById('videotrack-notes-toggle');
        var body = document.getElementById('videotrack-notes-body');
        if (!btn || !body) { return; }

        var KEY = 'videotrack_notes_collapsed_' + (config.cmid ? String(config.cmid) : 'x');

        function setCollapsed(collapsed) {
            body.style.display = collapsed ? 'none' : '';
            btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            var label = collapsed
                ? (config.noteshowlabel)
                : (config.noteshidelabel);
            btn.textContent = label;
            btn.setAttribute('aria-label', label + ': ' + (config.notespaneltitle));
            Utils.sessionSet(KEY, collapsed ? '1' : '0', 'Vimeo notes panel state');
        }

        // Applica lo stato IMMEDIATAMENTE prima del primo paint per evitare flash.
        // sessionStorage è sincrono — nessun rischio di flash se letto qui.
        var saved = null;
        saved = Utils.sessionGet(KEY, 'Vimeo notes panel state');
        setCollapsed(saved === '1');

        btn.addEventListener('click', function() {
            var isCollapsed = btn.getAttribute('aria-expanded') === 'false';
            setCollapsed(!isCollapsed);
        });
    }

    /**
     * Feature 11: Note personali studente.
     * Gestisce salvataggio e cancellazione di note testuali timestampate.
     * Il bottone "Salva" è attivo solo durante la riproduzione (aria-disabled).
     */
    function installNoteHandler() {
        if (!config.studentnotesenabled) { return; }

        var saveBtn = document.getElementById('videotrack-note-save');
        var textarea = document.getElementById('videotrack-note-input');
        if (!saveBtn || !textarea) { return; }

        /**
         * Aggiorna lo stato aria-disabled del bottone note in base alla riproduzione.
         * @param {boolean} playing
         */
        function setNoteButtonState(playing) {
            PlayerCore.setNoteButtonState(saveBtn, playing);
        }

        // Abilita/disabilita il bottone note in risposta all'evento personalizzato
        // 'videotrack:playstate' emesso da setReactionButtons al cambio di stato play.
        // Non riassegniamo setReactionButtons (è una function declaration, non variabile).
        document.addEventListener('videotrack:playstate', function(e) {
            setNoteButtonState(e.detail && e.detail.playing);
        });

        /**
         * Aggiunge una riga nella lista note dopo il salvataggio lato server.
         * @param {number} noteid      ID del record salvato.
         * @param {number} videotime   Timestamp video in secondi.
         * @param {string} text        Testo della nota.
         */
        function appendNoteRow(noteid, videotime, text) {
            PlayerCore.appendNoteRow(noteid, videotime, text, config, Utils);
        }

        // Salva nota al click del bottone.
        saveBtn.addEventListener('click', function() {
            if (saveBtn.getAttribute('aria-disabled') === 'true') { return; }
            var text = textarea.value.trim();
            if (!text) {
                textarea.focus();
                return;
            }
            // Cattura il timestamp PRIMA della chiamata AJAX (il video continua).
            var currentTime = getCurrentVideoTime();
            // Usa aria-disabled durante il salvataggio (non disabled HTML,
            // che rimuoverebbe il bottone dal tab order — incoerente con i bottoni reazione).
            saveBtn.setAttribute('aria-disabled', 'true');
            saveBtn.classList.add('videotrack-note-save-saving');
            saveCurrentProgress('note').then(function() {
                return ajax('mod_videotrack_save_note', {
                    cmid:         config.cmid,
                    sessionid:    state.sessionid,
                    videotime:    currentTime,
                    notetext:     text,
                    playbackrate: state.playbackrate || 1,
                });
            }).then(function(response) {
                saveBtn.classList.remove('videotrack-note-save-saving');
                // Ripristina stato in base alla riproduzione corrente (evita race condition:
                // se il video è stato messo in pausa durante il salvataggio, il bottone
                // deve restare disabilitato).
                setNoteButtonState(state.playing);
                if (response && response.noteeventid) {
                    appendNoteRow(response.noteeventid, currentTime, text);
                    textarea.value = '';
                    // Aggiorna il contatore.
                    var panel = document.getElementById('videotrack-notes-panel');
                    var hint  = panel ? panel.querySelector('.videotrack-note-charcount') : null;
                    if (hint) { hint.textContent = getRemainingNoteChars(textarea) + ' ' + config.charsremaininglabel; }
                    textarea.focus();
                }
            }).catch(function() {
                saveBtn.classList.remove('videotrack-note-save-saving');
                // Ripristina stato corretto anche in caso di errore.
                setNoteButtonState(state.playing);
                // B1/B2/B3 fix: use showStatusMessage() for consistent 8s visibility
                // and correct aria role management (avoids direct role mutation).
                showStatusMessage(config.noteerrorlabel, true);
            });
        });

        // Elimina nota al click.
        document.addEventListener('click', function(e) {
            var delBtn = e.target.closest('.videotrack-delete-note');
            if (!delBtn) { return; }
            var noteid = Utils.safeInt(delBtn.dataset.noteid, 0);
            if (!noteid) { return; }
            // Endpoint dedicato alle note personali (stesso record in videotrack_reactev).
            ajax('mod_videotrack_delete_note', {
                cmid:           config.cmid,
                reactioneventid: noteid,
            }).then(function(response) {
                if (response && response.deleted) {
                    var li = delBtn.closest('li');
                    if (li) {
                        var list = li.parentElement;
                        li.remove();
                        // Sposta il focus al prossimo elemento o al textarea.
                        var next = list ? list.querySelector('.videotrack-note-item button') : null;
                        if (next) { next.focus(); } else if (textarea) { textarea.focus(); }
                    }
                }
            }).catch(function(err) { Log.debug('mod_videotrack: note deletion failed - ' + err); });
        });

        function getRemainingNoteChars(textarea) {
            return PlayerCore.getRemainingNoteChars(textarea, config, Utils);
        }

        // Conta caratteri rimanenti (feedback accessibile).
        textarea.addEventListener('input', function() {
            var remaining = getRemainingNoteChars(textarea);
            // Il charcount span è dopo il bottone Salva, non direttamente dopo la textarea.
            var panel = document.getElementById('videotrack-notes-panel');
            var hint  = panel ? panel.querySelector('.videotrack-note-charcount') : null;
            if (hint) {
                hint.textContent = remaining + ' ' + config.charsremaininglabel;
            }
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
                // Avvia la riproduzione con l'API Vimeo SDK (non player.playVideo che è YouTube).
                if (player && player.play) {
                    player.play().catch(function(err) {
                        Log.debug('mod_videotrack: play request failed - ' + err);
                    });
                }
            });
        }

        // Rimuove il poster al primo evento 'play' del player Vimeo.
        state._posterRemoved = false;
        document.addEventListener('videotrack:playstate', function onFirstPlay(e) {
            if (e.detail && e.detail.playing && !state._posterRemoved) {
                state._posterRemoved = true;
                removePoster();
                document.removeEventListener('videotrack:playstate', onFirstPlay);
            }
        });
    }
    // ── Public API ────────────────────────────────────────────────────────

    return {
        init: function(initConfig) {
            config             = initConfig;
            // reactionannouncementinterval is provided by PHP in milliseconds; cap matches settings.php max (120000 ms).
            var interval = parseInt(config.reactionannouncementinterval, 10);
            reactionUnavailableAnnounceInterval = interval === 0 ? Number.MAX_SAFE_INTEGER :
                Math.max(1000, Math.min(120000, interval || DEFAULT_REACTION_UNAVAILABLE_ANNOUNCE_INTERVAL));
            // reactionreadydebouncems is intentionally configured in milliseconds; cap matches settings.php max (2000 ms).
            var debounce = parseInt(config.reactionreadydebouncems, 10);
            reactionReadyDebounceMs = debounce === 0 ? 0 :
                Math.max(0, Math.min(2000, debounce || DEFAULT_REACTION_READY_DEBOUNCE_MS));
            HEARTBEAT_INTERVAL = (config.heartbeatinterval > 0) ? config.heartbeatinterval : 30;
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
