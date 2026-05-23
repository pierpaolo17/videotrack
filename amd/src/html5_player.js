/**
 * HTML5 player module for mod_videotrack.
 *
 * Handles tracking for locally uploaded video/audio files using the
 * native HTML5 <video> / <audio> element events. Mirrors the same
 * segment-tracking contract as the YouTube and Vimeo player modules.
 *
 * @module mod_videotrack/html5_player
 */

/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/check-param-names */
define([
    'core/ajax',
    'core/log',
    'mod_videotrack/core/utils',
    'mod_videotrack/core/ui',
    'mod_videotrack/core/player'
], function(Ajax, Log, Utils, Ui, PlayerCore) {

    var media  = null; // The <video> or <audio> DOM element.
    var config = null;
    var DEFAULT_REACTION_UNAVAILABLE_ANNOUNCE_INTERVAL = 30000;
    var DEFAULT_REACTION_READY_DEBOUNCE_MS = 400;
    var reactionState = {
        timer: null,
        lastAnnouncement: null,
        readyAnnounced: false,
        debounceMs: DEFAULT_REACTION_READY_DEBOUNCE_MS,
        unavailableInterval: DEFAULT_REACTION_UNAVAILABLE_ANNOUNCE_INTERVAL,
        lastUnavailableAt: 0,
        cssTimer: null
    };
    var HEARTBEAT_INTERVAL = 30;

    var state = {
        sessionid:              null,
        playing:                false,
        segmentstart:           null,
        wallclockstart:         null,
        lasttime:               0,
        duration:               0,
        playbackrate:           1,
        heartbeatid:            null,
        lastHeartbeatWallclock: 0,
        seekblocked:            false,
        isSeeking:              false,
        isProgrammaticSeek:     false, // True per seek lanciati dal codice (replay, capitoli, resume).
        currentReplayEnd:       null,
        _posterRemoved:         false,
        _posterPlayListener:    null,
    };

    // ── Utilities ─────────────────────────────────────────────────────────

    function uuid() {
        return PlayerCore.uuid();
    }


    function ajax(methodname, args) {
        return Ajax.call([{ methodname: methodname, args: args }])[0];
    }

    function saveSegment(start, end, reason) {
        if (end <= start) { return Promise.resolve(null); }
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
        return PlayerCore.saveCurrentProgress(state, getCurrentVideoTime, saveSegment, reason, !!media);
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
            // C1 fix: passa duration come da firma allineata agli altri player.
            updateIntervalBar(response.intervaljson, response.durationseconds || state.duration);
        }
        return response;
    }

    // ── Segment lifecycle ─────────────────────────────────────────────────

    function startSegment() {
        state.playing               = true;
        state.segmentstart          = media.currentTime;
        state.wallclockstart        = Math.floor(Date.now() / 1000);
        state.lastHeartbeatWallclock = state.wallclockstart;
        state.lasttime              = media.currentTime;
        state.playbackrate          = media.playbackRate || 1;
    }

    /**
     * Chiude il segmento corrente e lo invia al server.
     * Cattura start ed end prima di azzerare lo stato per evitare
     * il bug "saveSegment(null, end)" che si verificava azzerando
     * state.segmentstart prima di passarlo alla funzione.
     *
     * @param {string} reason  Motivo di chiusura (pause, seek, tab, heartbeat...).
     */
    function closeSegment(reason) {
        if (!state.playing || state.segmentstart === null) { return; }
        var start = state.segmentstart;   // Cattura PRIMA di azzerare.
        var end   = media.currentTime;
        state.playing      = false;
        state.segmentstart = null;
        saveSegment(start, end, reason);
    }

    // ── Heartbeat ─────────────────────────────────────────────────────────

    function startHeartbeat() {
        if (state.heartbeatid) { return; }
        state.heartbeatid = window.setInterval(function() {
            if (!state.playing || state.segmentstart === null || state.isSeeking) { return; }
            var now = Math.floor(Date.now() / 1000);
            if (now - state.lastHeartbeatWallclock >= HEARTBEAT_INTERVAL) {
                var hbEnd   = media.currentTime;
                var hbStart = state.segmentstart;
                state.lastHeartbeatWallclock = now;
                saveSegment(hbStart, hbEnd, 'heartbeat');
                state.segmentstart   = hbEnd;
                state.wallclockstart = now;
            }
        }, 2000);
    }

    function stopHeartbeat() {
        if (state.heartbeatid) {
            window.clearInterval(state.heartbeatid);
            state.heartbeatid = null;
        }
    }

    // ── Progress bar (interval map) ───────────────────────────────────────

    /**
     * Ridisegna la barra canvas degli intervalli guardati e aggiorna aria-label.
     * C1 fix: firma allineata a player.js e vimeo_player.js (parametro duration).
     * B2/A1 fix: aggiunge calcolo covered e aggiornamento aria-label (WCAG 1.1.1).
     * @param {string} intervaljson  JSON array di [start,end] pairs.
     * @param {number} duration      Durata totale in secondi.
     */
    function updateIntervalBar(intervaljson, duration) {
        PlayerCore.updateIntervalBar(intervaljson, duration, Log);
    }

    function showResumeNotice(seconds) {
        PlayerCore.showResumeNotice(seconds, config, Utils);
    }

    function installGlobalListeners() {
        document.addEventListener('visibilitychange', function() {
            if (document.hidden && state.playing) { closeSegment('tab'); }
        });
        window.addEventListener('pagehide', function() {
            if (state.playing) { closeSegment('pagehide'); }
        });
        // sendBeacon: fallback sincrono per browser che cancellano fetch su unload.
        window.addEventListener('beforeunload', function() {
            if (!state.playing || state.segmentstart === null || !media) { return; }
            var start = state.segmentstart;
            var end   = media.currentTime;
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

        // Replay buttons.
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.videotrack-replay');
            if (btn && media) {
                var start = parseFloat(btn.dataset.start) || 0;
                var end   = parseFloat(btn.dataset.end)   || 0;
                state.currentReplayEnd = end > 0 ? end : null;
                // Marca il seek come programmatico: l'handler 'seeking' lo ignorerà.
                // isProgrammaticSeek persiste fino all'evento 'seeked' che lo resetta.
                state.isProgrammaticSeek = true;
                media.currentTime = start;
                media.play().catch(function(err) { Log.debug('mod_videotrack: play request failed - ' + err); });
            }
        });
    }

    // ── Build player ──────────────────────────────────────────────────────

    function buildPlayer() {
        var container = document.getElementById('mod-videotrack-player');
        if (!container) { return; }

        var isAudio = /\.(mp3|aac|m4a|ogg|wav)(\?|$)/i.test(config.videourl || '');
        var tag     = isAudio ? 'audio' : 'video';

        // Se videourl è vuoto (file non ancora caricato dal docente), mostra avviso
        // e non creare l'elemento media — evita media.src='' che causa errori browser.
        if (!config.videourl) {
            var nofileWrap = document.getElementById('mod-videotrack-player');
            if (nofileWrap) {
                var nofileMsg = document.createElement('div');
                nofileMsg.className = 'alert alert-warning mt-2';
                nofileMsg.setAttribute('role', 'alert');
                nofileMsg.textContent = config.nofilelabel;
                nofileWrap.parentNode.insertBefore(nofileMsg, nofileWrap.nextSibling);
            }
            return;
        }

        media = document.createElement(tag);
        media.src        = config.videourl;
        media.autoplay   = !!config.autoplay;
        // Feature 3: Se autoplay è attivo, intercetta il rifiuto del browser.
        // I browser moderni restituiscono una Promise da play(); se viene rifiutata
        // (policy autoplay) mostriamo un messaggio invece di lasciare il player silenzioso.
        if (config.autoplay) {
            var autoplayPromise = media.play();
            if (autoplayPromise !== undefined) {
                autoplayPromise.catch(function() {
                    var wrap = media.parentElement;
                    if (wrap && !wrap.querySelector('.videotrack-autoplay-notice')) {
                        var notice = document.createElement('div');
                        notice.className = 'videotrack-autoplay-notice alert alert-info mt-1';
                        notice.setAttribute('role', 'status');
                        notice.setAttribute('aria-live', 'polite');
                        notice.textContent = config.autoblockedlabel;
                        wrap.appendChild(notice);
                    }
                });
            }
        }
        media.loop       = !!config.loop;
        media.muted      = !!(config.autoplay || config.startmuted);
        media.playsinline = true;
        // Hide native controls: we replace them with a custom bar (or show them
        // if the teacher has enabled 'controls' and no custom bar is configured).
        media.controls   = false;
        media.style.width  = '100%';
        media.style.height = isAudio ? 'auto' : '100%';
        media.style.display = 'block';

        // Prevent download via right-click / context menu if not allowed.
        if (!config.allowdownload) {
            media.setAttribute('controlsList', 'nodownload');
            media.addEventListener('contextmenu', function(e) { e.preventDefault(); });
        }

        container.appendChild(media);

        // Attach VTT subtitles if provided.
        if (config.captions && config.vtturl) {
            var track = document.createElement('track');
            track.kind    = 'subtitles';
            track.src     = config.vtturl;
            track.srclang = config.captionslang || 'und';
            track.label   = config.captionslang;
            track.default = true;
            media.appendChild(track);
        }

        // Build custom control bar.
        buildControlBar(isAudio);

        // Attach segment-tracking event listeners.
        attachTrackingEvents();
    }

    /**
     * Builds the custom HTML5 player control bar according to config.html5controls.
     * Each control is only rendered if its identifier is in the allowed list.
     *
     * @param {boolean} isAudio  True for audio-only files.
     */
    function buildControlBar(isAudio) {
        var container = document.getElementById('mod-videotrack-player');
        if (!container) { return; }
        var controls = config.html5controls || [];
        if (!controls.length) { return; }

        var bar = document.createElement('div');
        bar.className = 'videotrack-html5-controls';
        bar.setAttribute('role', 'toolbar');
        bar.setAttribute('aria-label', config.html5controlslabel);

        // ── Play / Pause ─────────────────────────────────────
        if (controls.indexOf('play') >= 0) {
            var playBtn = makeBtn('videotrack-ctrl-play', '▶', config.html5playlabel);
            playBtn.addEventListener('click', function() {
                if (media.paused) { media.play(); } else { media.pause(); }
            });
            bar.appendChild(playBtn);
        }

        // ── Rewind ───────────────────────────────────────────
        // Shown only if: control is in the allowed list AND rewindstep > 0
        // AND allowseekbackward is not false.
        if (controls.indexOf('rewind') >= 0
                && config.rewindstep > 0
                && config.allowseekbackward !== false) {
            var rwBtn = makeBtn('videotrack-ctrl-rewind',
                '⏪ ' + config.rewindstep + 's',
                config.rewindlabel + ' ' + config.rewindstep + ' ' + config.secondslabel);
            rwBtn.addEventListener('click', function() {
                media.currentTime = Math.max(0, media.currentTime - config.rewindstep);
            });
            bar.appendChild(rwBtn);
        }

        // ── Fast-forward ──────────────────────────────────────
        // Shown only if: control is in the allowed list AND fastforwardstep > 0
        // AND allowseekforward is not false.
        if (controls.indexOf('fastforward') >= 0
                && config.fastforwardstep > 0
                && config.allowseekforward !== false) {
            var ffBtn = makeBtn('videotrack-ctrl-ff',
                config.fastforwardstep + 's ⏩',
                config.fastforwardlabel + ' ' + config.fastforwardstep + ' ' + config.secondslabel);
            ffBtn.addEventListener('click', function() {
                media.currentTime = Math.min(
                    state.duration || media.duration || 1e9,
                    media.currentTime + config.fastforwardstep
                );
            });
            bar.appendChild(ffBtn);
        }

        // ── Progress bar ─────────────────────────────────────
        if (controls.indexOf('progress') >= 0) {
            var progressWrap = document.createElement('div');
            progressWrap.className = 'videotrack-ctrl-progress-wrap';
            var progressBar = document.createElement('input');
            progressBar.type  = 'range';
            progressBar.className = 'videotrack-ctrl-progress';
            progressBar.min   = '0';
            progressBar.max   = '100';
            progressBar.value = '0';
            progressBar.setAttribute('aria-label',    config.html5seeklabel);
            progressBar.setAttribute('aria-valuemin', '0');
            progressBar.setAttribute('aria-valuemax', '100');
            progressBar.setAttribute('aria-valuenow', '0');
            progressBar.setAttribute('aria-valuetext','0:00');
            progressBar.addEventListener('input', function() {
                if (state.duration) {
                    var requested = (parseFloat(progressBar.value) / 100) * state.duration;
                    var current = media.currentTime || 0;
                    var allowed = requested;
                    if (config.allowseekforward === false && requested > current) {
                        allowed = current;
                    }
                    if (config.allowseekbackward === false && requested < current) {
                        allowed = current;
                    }
                    media.currentTime = allowed;
                    progressBar.value = state.duration ? String((allowed / state.duration) * 100) : '0';
                    progressBar.setAttribute('aria-valuenow',  String(Math.round(progressBar.value)));
                    progressBar.setAttribute('aria-valuetext', Utils.formatSeconds(allowed));
                }
            });
            progressWrap.appendChild(progressBar);
            bar.appendChild(progressWrap);
            // Store reference for timeupdate.
            bar._progressBar = progressBar;
        }

        // ── Current time ─────────────────────────────────────
        if (controls.indexOf('current') >= 0) {
            var currentEl = document.createElement('span');
            currentEl.className = 'videotrack-ctrl-time';
            currentEl.textContent = '0:00';
            currentEl.setAttribute('aria-live', 'off');
            bar.appendChild(currentEl);
            bar._currentEl = currentEl;
        }

        // ── Duration ─────────────────────────────────────────
        if (controls.indexOf('duration') >= 0) {
            var durationEl = document.createElement('span');
            durationEl.className = 'videotrack-ctrl-duration text-muted';
            durationEl.textContent = ' / 0:00';
            bar.appendChild(durationEl);
            bar._durationEl = durationEl;
        }

        // ── Mute ─────────────────────────────────────────────
        if (controls.indexOf('mute') >= 0) {
            var muteBtn = makeBtn('videotrack-ctrl-mute', '🔊', config.html5mutelabel);
            muteBtn.addEventListener('click', function() {
                media.muted = !media.muted;
                muteBtn.textContent = media.muted ? '🔇' : '🔊';
                muteBtn.setAttribute('aria-label', media.muted ? (config.html5unmutelabel) : (config.html5mutelabel));
            });
            bar.appendChild(muteBtn);
        }

        // ── Volume ───────────────────────────────────────────
        if (controls.indexOf('volume') >= 0) {
            var volSlider = document.createElement('input');
            volSlider.type  = 'range';
            volSlider.className = 'videotrack-ctrl-volume';
            volSlider.min   = '0';
            volSlider.max   = '100';
            volSlider.step  = '5';
            var initialVolumePercent = media.muted ? 0 : Math.round(media.volume * 100 / 5) * 5;
            initialVolumePercent = Math.max(0, Math.min(100, initialVolumePercent));
            volSlider.value = String(initialVolumePercent);
            volSlider.setAttribute('aria-label', config.html5volumelabel);
            volSlider.setAttribute('aria-valuemin', '0');
            volSlider.setAttribute('aria-valuemax', '100');
            volSlider.setAttribute('aria-valuenow', String(initialVolumePercent));
            volSlider.setAttribute('aria-valuetext', initialVolumePercent + '%');
            volSlider.addEventListener('input', function() {
                var volumePercent = Math.max(0, Math.min(100, parseFloat(volSlider.value) || 0));
                media.volume = volumePercent / 100;
                media.muted  = (media.volume === 0);
                volSlider.value = String(volumePercent);
                volSlider.setAttribute('aria-valuenow', String(volumePercent));
                volSlider.setAttribute('aria-valuetext', volumePercent + '%');
            });
            bar.appendChild(volSlider);
        }

        // ── Speed ─────────────────────────────────────────────
        if (controls.indexOf('speed') >= 0 && config.playbackspeeds && config.playbackspeeds.length) {
            var speedWrap = document.createElement('div');
            speedWrap.className = 'videotrack-speed-controls';
            speedWrap.id = 'videotrack-speed-controls';
            config.playbackspeeds.forEach(function(speed) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-sm btn-outline-secondary videotrack-speed-btn' +
                    (speed === 1 ? ' active' : '');
                btn.textContent = speed + '×';
                btn.dataset.speed = speed;
                btn.setAttribute('aria-label', (config.html5speedlabel) + ' ' + speed + 'x');
                btn.addEventListener('click', function() {
                    media.playbackRate = speed;
                    state.playbackrate = speed;
                    speedWrap.querySelectorAll('.videotrack-speed-btn').forEach(function(b) {
                        b.classList.toggle('active', parseFloat(b.dataset.speed) === speed);
                    });
                });
                speedWrap.appendChild(btn);
            });
            bar.appendChild(speedWrap);
        }

        // ── PiP ──────────────────────────────────────────────
        if (!isAudio && controls.indexOf('pip') >= 0 && document.pictureInPictureEnabled) {
            var pipBtn = makeBtn('videotrack-ctrl-pip', '⧉', config.html5piplabel);
            pipBtn.setAttribute('aria-pressed', 'false');
            var updatePipPressed = function() {
                pipBtn.setAttribute('aria-pressed', document.pictureInPictureElement === media ? 'true' : 'false');
            };
            pipBtn.addEventListener('click', function() {
                if (document.pictureInPictureElement) {
                    document.exitPictureInPicture().then(updatePipPressed).catch(function() { updatePipPressed(); });
                } else {
                    media.requestPictureInPicture().then(updatePipPressed).catch(function() { updatePipPressed(); });
                }
            });
            media.addEventListener('enterpictureinpicture', updatePipPressed);
            media.addEventListener('leavepictureinpicture', updatePipPressed);
            bar.appendChild(pipBtn);
        }

        // ── Fullscreen ────────────────────────────────────────
        if (!isAudio && controls.indexOf('fullscreen') >= 0) {
            var fsWrapper = container.closest('.videotrack-player-wrap') || container;
            var fsBtn = makeBtn('videotrack-ctrl-fs', '⛶', config.html5fullscreenlabel);
            fsBtn.setAttribute('aria-pressed', 'false');
            var updateFullscreenPressed = function() {
                fsBtn.setAttribute('aria-pressed', document.fullscreenElement === fsWrapper ? 'true' : 'false');
            };
            document.addEventListener('fullscreenchange', updateFullscreenPressed);
            fsBtn.addEventListener('click', function() {
                if (!document.fullscreenElement) {
                    if (fsWrapper.requestFullscreen) {
                        fsWrapper.requestFullscreen().then(updateFullscreenPressed).catch(function() {
                            updateFullscreenPressed();
                        });
                    }
                } else if (document.fullscreenElement === fsWrapper && document.exitFullscreen) {
                    document.exitFullscreen().then(updateFullscreenPressed).catch(function() { updateFullscreenPressed(); });
                }
            });
            bar.appendChild(fsBtn);
        }

        // ── Download ──────────────────────────────────────────
        if (config.allowdownload && controls.indexOf('download') >= 0) {
            var dlBtn = document.createElement('a');
            dlBtn.href     = media.src;
            dlBtn.download = '';
            dlBtn.className = 'btn btn-sm btn-outline-secondary videotrack-ctrl-download';
            dlBtn.textContent = '⬇';
            dlBtn.setAttribute('aria-label', config.html5downloadlabel);
            bar.appendChild(dlBtn);
        }

        // ── Wire up timeupdate to control bar ─────────────────
        media.addEventListener('timeupdate', function() {
            if (bar._progressBar && state.duration) {
                var pct = (media.currentTime / state.duration) * 100;
                bar._progressBar.value = pct;
                bar._progressBar.setAttribute('aria-valuenow',  String(Math.round(pct)));
                bar._progressBar.setAttribute('aria-valuetext', Utils.formatSeconds(media.currentTime));
            }
            if (bar._currentEl) {
                bar._currentEl.textContent = Utils.formatSeconds(media.currentTime);
            }
        });

        media.addEventListener('loadedmetadata', function() {
            state.duration = media.duration || 0;
            if (bar._durationEl) {
                bar._durationEl.textContent = ' / ' + Utils.formatSeconds(state.duration);
            }
            if (bar._progressBar) { bar._progressBar.max = '100'; }
            // Wire up initial speed.
            if (config.playbackspeeds && config.playbackspeeds.length) {
                var speeds  = config.playbackspeeds.slice().sort(function(a, b) { return a - b; });
                var nearest = speeds.reduce(function(prev, curr) {
                    return Math.abs(curr - 1) < Math.abs(prev - 1) ? curr : prev;
                });
                media.playbackRate = nearest;
                state.playbackrate = nearest;
            }
            // Enforce maxplaybackrate al caricamento.
            // config.maxplaybackrate è in centesimi (150 = 1.5×); convertire a float.
            if (config.maxplaybackrate > 0) {
                var maxRateLoad = config.maxplaybackrate / 100;
                if (media.playbackRate > maxRateLoad) {
                    media.playbackRate = maxRateLoad;
                    state.playbackrate = maxRateLoad;
                }
            }
            // Resume automatico dal punto lasciato (lastposition > 2s).
            if (typeof config.resumeposition === 'number' && config.resumeposition > 2
                    && config.resumeposition < (state.duration || Infinity)) {
                media.currentTime = config.resumeposition;
                showResumeNotice(config.resumeposition);
            }
        });

        media.addEventListener('play', function() {
            var playBtn2 = bar.querySelector('.videotrack-ctrl-play');
            if (playBtn2) { playBtn2.textContent = '⏸'; playBtn2.setAttribute('aria-label', config.html5pauselabel); }
        });
        media.addEventListener('pause', function() {
            var playBtn2 = bar.querySelector('.videotrack-ctrl-play');
            if (playBtn2) { playBtn2.textContent = '▶'; playBtn2.setAttribute('aria-label', config.html5playlabel); }
        });

        // Append bar after the media element, inside the player wrapper.
        var wrapper = container.closest('.videotrack-player-wrap') || container.parentElement;
        wrapper.appendChild(bar);
    }

    /** Creates a button for the custom control bar. */
    function makeBtn(cls, icon, label) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-dark ' + cls;
        btn.textContent = icon;
        btn.setAttribute('aria-label', label);
        return btn;
    }

    // ── Event handlers (tracking) ─────────────────────────────────────────
    // Called by buildPlayer() after the <video>/<audio> element is in the DOM.

    function attachTrackingEvents() {
        media.addEventListener('play', function() {
            // Avvia un nuovo segmento solo se non c'è già uno in corso.
            // state.playing è sempre false all'arrivo di 'play' perché closeSegment()
            // lo resetta prima (seeking/pause/ended). Il ramo !state.playing è l'unico
            // path percorribile — il ramo state.playing=true era dead code.
            if (!state.isSeeking && !state.playing) {
                startSegment();
                startHeartbeat();
                setReactionButtons(true);
            }
        });

        media.addEventListener('pause', function() {
            if (!state.isSeeking) {
                stopHeartbeat();
                closeSegment('pause');
                setReactionButtons(false);
            }
        });

        media.addEventListener('ended', function() {
            reactionState.readyAnnounced = false;
            stopHeartbeat();
            closeSegment('ended');
            setReactionButtons(false); // Disabilita bottoni a fine video.
        });

        // Seek detection: HTML5 fires 'seeking' then 'seeked'.
        media.addEventListener('seeking', function() {
            state.isSeeking = true;
            // Seek programmatico (replay, capitolo, resume): chiude il segmento corrente
            // se il video era in riproduzione, per salvare il progresso fino a questo punto.
            // Non blocca il seek né applica le regole allowseekforward/allowseekbackward.
            if (state.isProgrammaticSeek) {
                if (state.playing) { closeSegment('seek'); }
                return;
            }
            if (state.playing) {
                var newtime = media.currentTime;
                var oldtime = state.lasttime;
                if (!config.allowseekforward && newtime > oldtime + 0.5) {
                    state.seekblocked = true;
                    media.currentTime = oldtime;
                    return;
                }
                if (!config.allowseekbackward && newtime < oldtime - 0.5) {
                    state.seekblocked = true;
                    media.currentTime = oldtime;
                    return;
                }
                closeSegment('seek');
            }
        });

        media.addEventListener('seeked', function() {
            state.isSeeking         = false;
            state.isProgrammaticSeek = false; // Resetta anche il flag seek programmatico.
            state.seekblocked        = false;
            if (state.playing) { startSegment(); }
            if (state.currentReplayEnd !== null && media.currentTime >= state.currentReplayEnd) {
                media.pause();
                state.currentReplayEnd = null;
            }
        });

        media.addEventListener('timeupdate', function() {
            if (!state.isSeeking) {
                state.lasttime     = media.currentTime;
                state.playbackrate = media.playbackRate || 1;
                if (state.currentReplayEnd !== null && media.currentTime >= state.currentReplayEnd) {
                    media.pause();
                    state.currentReplayEnd = null;
                }
            }
        });

        media.addEventListener('ratechange', function() {
            // Se lo studente alza la velocità oltre il limite, riportiamo al massimo.
            // config.maxplaybackrate è in centesimi (150 = 1.5×); convertire a float.
            if (config.maxplaybackrate > 0) {
                var maxRateChange = config.maxplaybackrate / 100;
                if (media.playbackRate > maxRateChange) {
                    media.playbackRate = maxRateChange;
                }
            }
            state.playbackrate = media.playbackRate || 1;
        });
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
        PlayerCore.announceReactionAvailability(playing, config, reactionState);
    }

    function announceReactionUnavailable() {
        PlayerCore.announceReactionUnavailable(config, reactionState);
    }

    /**
     * Installs the click handler for reaction buttons and replay buttons.
     * Mirrors the logic of player.js to ensure consistent behaviour across all sources.
     */
    function installReactionHandler() {

        function appendReactionRow(eventid, reaction, videotime) {
            var tbody = document.getElementById('videotrack-my-reactions');
            if (!tbody) { return; }
            // Rimuove la riga placeholder 'nessuna reazione' alla prima reazione aggiunta.
            var placeholder = tbody.querySelector('.videotrack-no-reactions-placeholder');
            if (placeholder) { placeholder.parentNode.removeChild(placeholder); }
            var tr = document.createElement('tr');
            tr.setAttribute('data-eventid', eventid);
            // Timestamp cell
            var tdtime = document.createElement('td');
            tdtime.textContent = videotime;
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
                e.preventDefault();
                e.stopPropagation();
                announceReactionUnavailable();
                return;
            }
            if (reactionbtn) {
                e.preventDefault();
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
                    PlayerCore.showStatusMessage(msg, true);
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






    // ── Transcript VTT (upload source only) ──────────────────────────────────

    /**
     * Feature 8: Transcript VTT interattivo.
     * Parsa il file VTT, lo renderizza come lista di cue nel pannello sidebar,
     * e sincronizza la cue attiva con la posizione corrente del video.
     */
    function loadTranscript() {
        if (!config.showtranscript || !config.vtturl) { return; }
        var panel = document.getElementById('videotrack-transcript-content');
        if (!panel) { return; }

        // Fetch del file VTT già servito dal pluginfile Moodle, con timeout
        // per evitare richieste sospese che lasciano transcript/capitoli in stato incerto.
        Utils.fetchTextWithTimeout(config.vtturl)
            .then(function(text) {
                var cues = parseVTT(text);
                if (!cues.length) {
                    showTranscriptUnavailable(panel);
                    return;
                }
                renderTranscript(panel, cues);
                syncTranscript(cues);
            })
            .catch(function(err) {
                Log.debug('mod_videotrack: could not load VTT transcript — ' + err);
                showTranscriptUnavailable(panel);
            });
    }


    /** Mostra un messaggio accessibile quando il transcript non è disponibile. */
    function showTranscriptUnavailable(panel) {
        if (!panel) { return; }
        panel.innerHTML = '';
        var msg = document.createElement('p');
        msg.className = 'videotrack-transcript-empty text-muted mb-0';
        msg.setAttribute('role', 'status');
        msg.textContent = config.transcriptunavailablelabel;
        panel.appendChild(msg);
    }

    /**
     * Parsa un file WebVTT e restituisce un array di cue objects:
     * {start, end, text} dove start/end sono in secondi (float).
     *
     * @param  {string} text  Contenuto del file VTT.
     * @return {Array}
     */
    function parseVTT(text) {
        var cues = [];
        if (!text) { return cues; }

        // Normalizza BOM e CRLF. Ignora header NOTE/STYLE/REGION e cue settings dopo l'end time.
        var normalized = text.replace(/^\uFEFF/, '').replace(/\r\n?/g, '\n');
        var blocks = normalized.split(/\n[ \t]*\n/);
        var timeRe = /^((?:\d{2}:)?\d{2}:\d{2}\.\d{3})[ \t]*-->[ \t]*((?:\d{2}:)?\d{2}:\d{2}\.\d{3})(?:[ \t].*)?$/;

        blocks.forEach(function(block) {
            var lines = block.trim().split('\n').map(function(line) { return line.trim(); });
            if (!lines.length || /^(WEBVTT|NOTE|STYLE|REGION)(?:\s|$)/i.test(lines[0])) { return; }

            var timeLine = -1;
            for (var i = 0; i < lines.length; i++) {
                if (timeRe.test(lines[i])) { timeLine = i; break; }
            }
            if (timeLine < 0) { return; }

            var m = lines[timeLine].match(timeRe);
            var start = vttTime(m[1]);
            var end = vttTime(m[2]);
            if (!isFinite(start) || !isFinite(end) || end <= start) { return; }

            var textLines = lines.slice(timeLine + 1).join(' ').replace(/<[^>]+>/g, '').trim();
            if (!textLines) { return; }
            cues.push({ start: start, end: end, text: textLines });
        });
        return cues;
    }

    /** Converte un timestamp VTT (HH:MM:SS.mmm o MM:SS.mmm) in secondi float. */
    function vttTime(ts) {
        var parts = ts.split(':');
        if (parts.length < 2 || parts.length > 3) { return NaN; }
        var seconds = parseFloat(parts.pop());
        var minutes = parseInt(parts.pop(), 10);
        var hours = parts.length ? parseInt(parts.pop(), 10) : 0;
        if (!isFinite(seconds) || !isFinite(minutes) || !isFinite(hours)) { return NaN; }
        if (minutes < 0 || minutes >= 60 || seconds < 0 || seconds >= 60 || hours < 0) { return NaN; }
        return hours * 3600 + minutes * 60 + seconds;
    }

    /**
     * Renderizza le cue nel pannello come lista di bottoni cliccabili.
     * Ogni bottone porta il video al timestamp della cue.
     *
     * @param {HTMLElement} panel   Contenitore del transcript.
     * @param {Array}       cues    Array di cue objects.
     */
    function renderTranscript(panel, cues) {
        panel.innerHTML = '';
        var list = document.createElement('ol');
        list.className = 'videotrack-transcript-list list-unstyled mb-0';
        cues.forEach(function(cue, idx) {
            var item = document.createElement('li');
            item.className = 'videotrack-transcript-cue';
            item.dataset.idx = idx;
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-link btn-sm text-start videotrack-transcript-btn';
            btn.dataset.start = cue.start;
            btn.setAttribute('aria-label', Utils.formatSeconds(cue.start) + ' — ' + cue.text);
            btn.setAttribute('aria-controls', 'mod-videotrack-player');
            var timeSpan = document.createElement('span');
            timeSpan.className = 'videotrack-transcript-time text-muted me-1';
            timeSpan.textContent = Utils.formatSeconds(cue.start);
            var textSpan = document.createElement('span');
            textSpan.textContent = cue.text;
            btn.appendChild(timeSpan);
            btn.appendChild(textSpan);
            btn.addEventListener('click', function() {
                if (!media) { return; }
                var wasPlaying = !media.paused;
                state.isSeeking = true;
                media.currentTime = cue.start;
                state.isSeeking   = false;
                if (wasPlaying) {
                    media.play();
                }
            });
            item.appendChild(btn);
            list.appendChild(item);
        });
        panel.appendChild(list);
    }

    /**
     * Registra un listener timeupdate che evidenzia la cue attiva nel transcript.
     * Scorre automaticamente il pannello per portare la cue attiva in vista.
     *
     * @param {Array} cues  Array di cue objects (già parsati).
     */
    function syncTranscript(cues) {
        if (!media) { return; }
        var lastActive = -1;
        media.addEventListener('timeupdate', function() {
            var t = media.currentTime;
            var active = -1;
            for (var i = 0; i < cues.length; i++) {
                if (t >= cues[i].start && t < cues[i].end) { active = i; break; }
            }
            if (active === lastActive) { return; }
            lastActive = active;
            var panel = document.getElementById('videotrack-transcript-content');
            if (!panel) { return; }
            panel.querySelectorAll('.videotrack-transcript-cue').forEach(function(el) {
                var isActive = parseInt(el.dataset.idx, 10) === active;
                el.classList.toggle('videotrack-transcript-active', isActive);
                el.querySelector('.videotrack-transcript-btn').setAttribute('aria-current',
                    isActive ? 'true' : 'false');
                // Scroll automatico: solo se la cue è fuori dalla vista del pannello.
                if (isActive) {
                    var panelRect = panel.getBoundingClientRect();
                    var elRect    = el.getBoundingClientRect();
                    if (elRect.top < panelRect.top || elRect.bottom > panelRect.bottom) {
                        var scrollOptions = { block: 'nearest' };
                        if (!prefersReducedMotion()) {
                            scrollOptions.behavior = 'smooth';
                        }
                        el.scrollIntoView(scrollOptions);
                    }
                }
            });
        });
    }


    /** Restituisce il timestamp video corrente per il player HTML5. */
    function getCurrentVideoTime() {
        return media ? media.currentTime : (state.lasttime || 0);
    }

    /** Restituisce true se l'utente ha richiesto animazioni ridotte. */
    function prefersReducedMotion() {
        return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
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
            Utils.sessionSet(KEY, collapsed ? '1' : '0', 'notes panel state');
        }

        // Applica lo stato IMMEDIATAMENTE prima del primo paint per evitare flash.
        // sessionStorage è sincrono — nessun rischio di flash se letto qui.
        var saved = null;
        saved = Utils.sessionGet(KEY, 'notes panel state');
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
     * Feature 10: Barra capitoli VTT navigabili.
     * Parsata dallo stesso file VTT dei sottotitoli (kind=chapters).
     * Funziona solo se il file VTT contiene cue con testo breve (< 80 chars) —
     * tipicamente quelli prodotti come capitoli.
     * Ogni capitolo diventa un bottone che salta a quel punto del video.
     */
    function buildChaptersBar() {
        if (!config.vtturl || !config.showchapters) { return; }

        Utils.fetchTextWithTimeout(config.vtturl)
            .then(function(text) {
                var cues = parseVTT(text);
                // Filtra: considera capitoli solo le cue con testo <= 80 chars.
                var chapters = cues.filter(function(c) { return c.text.length <= 80; });
                if (chapters.length < 2) {
                    showChaptersUnavailable();
                    return;
                }
                renderChaptersBar(chapters);
            })
            .catch(function(err) {
                Log.debug('videotrack chapters: ' + err);
                showChaptersUnavailable();
            });
    }

    /** Mostra un messaggio accessibile quando i capitoli non sono disponibili. */
    function showChaptersUnavailable() {
        var wrapper = document.querySelector('.videotrack-player-wrap');
        if (!wrapper || wrapper.querySelector('.videotrack-chapters-empty') || wrapper.querySelector('.videotrack-chapters-bar')) {
            return;
        }
        var msg = document.createElement('p');
        msg.className = 'videotrack-chapters-empty text-muted small mb-2';
        msg.setAttribute('role', 'status');
        msg.textContent = config.chaptersunavailablelabel || config.transcriptunavailablelabel;
        var controls = wrapper.querySelector('.videotrack-html5-controls');
        if (controls) {
            wrapper.insertBefore(msg, controls);
        } else {
            wrapper.appendChild(msg);
        }
    }

    /**
     * Crea la barra capitoli e la inserisce prima dei controlli.
     * @param {Array} chapters  Array di {start, end, text}.
     */
    function renderChaptersBar(chapters) {
        var wrapper = document.querySelector('.videotrack-player-wrap');
        if (!wrapper) { return; }
        if (wrapper.querySelector('.videotrack-chapters-bar')) { return; } // già presente

        var bar = document.createElement('nav');
        bar.className = 'videotrack-chapters-bar';
        bar.setAttribute('aria-label', config.chapterslabel);
        bar.setAttribute('role', 'navigation');

        chapters.forEach(function(ch, idx) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'videotrack-chapter-btn';
            btn.dataset.start = ch.start;
            btn.dataset.idx   = idx;
            btn.setAttribute('aria-label',
                (config.chapterlabel) + ' ' + (idx + 1) + ': ' + ch.text);
            // Label visuale: numero + testo breve.
            var numSpan = document.createElement('span');
            numSpan.className = 'videotrack-chapter-num';
            numSpan.textContent = idx + 1;
            var textSpan = document.createElement('span');
            textSpan.className = 'videotrack-chapter-text';
            textSpan.textContent = ch.text;
            btn.appendChild(numSpan);
            btn.appendChild(textSpan);
            btn.addEventListener('click', function() {
                // Seek al capitolo (usa isSeeking per non far scattare il blocco anti-skip).
                // Mantiene lo stato precedente: se il video era in pausa, il click non avvia la riproduzione.
                var wasPlaying = state.playing && !media.paused;
                state.isProgrammaticSeek = true;
                media.currentTime = ch.start;
                state.lasttime    = ch.start;
                if (wasPlaying) {
                    media.play().catch(function(err) {
                        Log.debug('mod_videotrack: play request failed - ' + err);
                    }); // Catch autoplay policy rejection.
                }
                // Aggiorna stato attivo.
                bar.querySelectorAll('.videotrack-chapter-btn').forEach(function(b) {
                    b.classList.toggle('videotrack-chapter-active', b === btn);
                    b.setAttribute('aria-current', b === btn ? 'true' : 'false');
                });
            });
            bar.appendChild(btn);
        });

        // Inserisce la barra PRIMA dei controlli custom.
        var controls = wrapper.querySelector('.videotrack-html5-controls');
        if (controls) {
            wrapper.insertBefore(bar, controls);
        } else {
            wrapper.appendChild(bar);
        }

        // Sincronizza il capitolo attivo con timeupdate.
        if (media) {
            media.addEventListener('timeupdate', function() {
                var t = media.currentTime;
                var activeIdx = -1;
                for (var i = chapters.length - 1; i >= 0; i--) {
                    if (t >= chapters[i].start) { activeIdx = i; break; }
                }
                bar.querySelectorAll('.videotrack-chapter-btn').forEach(function(btn, i) {
                    var isActive = i === activeIdx;
                    if (btn.classList.contains('videotrack-chapter-active') !== isActive) {
                        btn.classList.toggle('videotrack-chapter-active', isActive);
                        btn.setAttribute('aria-current', isActive ? 'true' : 'false');
                    }
                });
            });
        }
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
                // Avvia la riproduzione con l'elemento media HTML5 (non player YouTube/Vimeo).
                if (media) {
                    media.play().catch(function(err) {
                        Log.debug('mod_videotrack: play request failed - ' + err);
                    });
                }
            });
        }

        // Rimuove il poster al primo evento 'play' del media HTML5.
        state._posterRemoved = false;
        state._posterPlayListener = function(e) {
            PlayerCore.onFirstPlay(e, state, removePoster);
        };
        document.addEventListener('videotrack:playstate', state._posterPlayListener);
    }
    // ── Public API ────────────────────────────────────────────────────────

    return {
        init: function(initConfig) {
            config             = initConfig;
            // reactionannouncementinterval is provided by PHP in milliseconds; cap matches settings.php max (120000 ms).
            var interval = parseInt(config.reactionannouncementinterval, 10);
            reactionState.unavailableInterval = interval === 0 ? Number.MAX_SAFE_INTEGER :
                Math.max(1000, Math.min(120000, interval || DEFAULT_REACTION_UNAVAILABLE_ANNOUNCE_INTERVAL));
            // reactionreadydebouncems is intentionally configured in milliseconds; cap matches settings.php max (2000 ms).
            var debounce = parseInt(config.reactionreadydebouncems, 10);
            reactionState.debounceMs = debounce === 0 ? 0 :
                Math.max(0, Math.min(2000, debounce || DEFAULT_REACTION_READY_DEBOUNCE_MS));
            HEARTBEAT_INTERVAL = (config.heartbeatinterval > 0) ? config.heartbeatinterval : 30;
            state.sessionid    = uuid();
            installGlobalListeners();
            installReactionHandler();
            installNoteHandler();
            installNotesToggle();
            installPosterHandler();
            buildPlayer();
            loadTranscript();
            buildChaptersBar();
        }
    };
});
