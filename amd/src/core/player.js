/**
 * Shared player UI helpers for mod_videotrack AMD player modules.
 *
 * This module contains helper functions used by the HTML5, YouTube and Vimeo
 * player entrypoints. It intentionally avoids player-API-specific code.
 *
 * @module mod_videotrack/core/player
 */
define([], function() {
    var statusTimer = null;


    /**
     * Create a compact session identifier.
     *
     * @returns {string} Session identifier.
     */
    function uuid() {
        if (window.crypto && window.crypto.randomUUID) {
            return window.crypto.randomUUID().replace(/-/g, '');
        }
        return 'sess' + Date.now() + Math.random().toString(36).substring(2, 12);
    }

    /**
     * Read a CSS colour used by the interval canvas.
     *
     * @param {HTMLCanvasElement} canvas Canvas element.
     * @param {string} property CSS custom property name.
     * @param {string} fallback Fallback colour.
     * @returns {string} CSS colour.
     */
    function getIntervalBarColor(canvas, property, fallback) {
        var value = window.getComputedStyle(canvas).getPropertyValue(property);
        return value ? value.trim() : fallback;
    }


    /**
     * Save progress for a currently playing segment before an interaction.
     *
     * @param {Object} state Mutable player state.
     * @param {Function} getCurrentTime Function returning the current video time.
     * @param {Function} saveSegment Function used to persist the segment.
     * @param {string} reason Save reason.
     * @param {boolean} hasPlayer Whether the concrete player is available.
     * @returns {Promise|null} Save promise or null-equivalent promise.
     */
    function saveCurrentProgress(state, getCurrentTime, saveSegment, reason, hasPlayer) {
        if (!state.playing || state.segmentstart === null || !hasPlayer) {
            return Promise.resolve(null);
        }
        var end = getCurrentTime();
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

    /**
     * Draw the watched-interval canvas and keep its text alternative in sync.
     *
     * @param {string} intervaljson JSON encoded list of [start, end] pairs.
     * @param {number} duration Video duration in seconds.
     * @param {Object} Log Moodle log module.
     */
    function updateIntervalBar(intervaljson, duration, Log) {
        var canvas = document.getElementById('videotrack-interval-bar');
        if (!canvas || !duration) {
            return;
        }
        try {
            var intervals = JSON.parse(intervaljson);
            var ctx = canvas.getContext('2d');
            var dpr = window.devicePixelRatio || 1;
            var cssWidth = canvas.offsetWidth || canvas.width;
            var cssHeight = canvas.offsetHeight || canvas.height;
            var w = Math.max(1, Math.round(cssWidth * dpr));
            var h = Math.max(1, Math.round(cssHeight * dpr));
            var covered = 0;
            if (canvas.width !== w || canvas.height !== h) {
                canvas.width = w;
                canvas.height = h;
            }
            ctx.clearRect(0, 0, w, h);
            ctx.fillStyle = getIntervalBarColor(canvas, '--videotrack-interval-bg', '#e9ecef');
            ctx.fillRect(0, 0, w, h);
            ctx.fillStyle = getIntervalBarColor(canvas, '--videotrack-interval-fill', '#28a745');
            intervals.forEach(function(seg) {
                var start = Math.max(0, Number(seg[0]) || 0);
                var end = Math.min(duration, Math.max(start, Number(seg[1]) || 0));
                var x1 = Math.round((start / duration) * w);
                var x2 = Math.round((end / duration) * w);
                ctx.fillRect(x1, 0, Math.max(2, x2 - x1), h);
                covered += Math.max(0, end - start);
            });
            var pct = duration > 0 ? Math.min(100, Math.round((covered / duration) * 100)) : 0;
            var baseLabel = canvas.getAttribute('title') || '';
            var text = baseLabel + ' — ' + pct + '%';
            canvas.setAttribute('aria-label', text);
            var status = document.getElementById('videotrack-interval-bar-status');
            if (status) {
                status.textContent = text;
            }
        } catch (e) {
            if (Log && Log.debug) {
                Log.debug('mod_videotrack: invalid interval JSON - ' + e);
            }
        }
    }

    /**
     * Show the resume-position notice.
     *
     * @param {number} seconds Resume position in seconds.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     */
    function showResumeNotice(seconds, config, Utils) {
        var existing = document.getElementById('videotrack-resume-notice');
        if (existing) {
            existing.parentNode.removeChild(existing);
        }
        var formatted = Utils.formatSeconds(seconds);
        var notice = document.createElement('div');
        notice.id = 'videotrack-resume-notice';
        notice.className = 'videotrack-resume-notice alert alert-info alert-dismissible mt-1';
        notice.setAttribute('role', 'status');
        notice.setAttribute('aria-live', 'polite');

        var text = document.createElement('span');
        text.textContent = config.resumelabel + ' ' + formatted + '.';
        notice.appendChild(text);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-close ms-2';
        btn.setAttribute('aria-label', config.dismisslabel);
        btn.addEventListener('click', function() {
            if (notice.parentNode) {
                notice.parentNode.removeChild(notice);
            }
        });
        notice.appendChild(btn);

        var shell = document.querySelector('.videotrack-player-shell');
        if (shell) {
            shell.insertBefore(notice, shell.firstChild);
        }
        window.setTimeout(function() {
            if (notice.parentNode) {
                notice.parentNode.removeChild(notice);
            }
        }, 6000);
    }


    /**
     * Show an accessible temporary status message in the player shell.
     *
     * @param {string} message Message text.
     * @param {boolean} isError Whether the message should be announced as an error.
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
            if (shell) {
                shell.appendChild(el);
            }
        }
        el.setAttribute('role', isError ? 'alert' : 'status');
        el.textContent = message || '';
        if (statusTimer) {
            window.clearTimeout(statusTimer);
        }
        statusTimer = window.setTimeout(function() {
            el.textContent = '';
            statusTimer = null;
        }, isError ? 8000 : 4000);
    }

    /**
     * Update the note character counter next to a textarea.
     *
     * @param {HTMLTextAreaElement} textarea Note textarea.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     * @returns {number} Remaining characters.
     */
    function updateNoteCharCounter(textarea, config, Utils) {
        if (!textarea) {
            return 0;
        }
        var remaining = getRemainingNoteChars(textarea, config, Utils);
        var panel = textarea.closest('.videotrack-notes-panel');
        var hint = panel ? panel.querySelector('.videotrack-note-charcount') : null;
        if (hint) {
            hint.textContent = remaining + ' ' + config.charsremaininglabel;
        }
        return remaining;
    }

    /**
     * Update the enabled state of the note save button while keeping it focusable.
     *
     * @param {HTMLButtonElement} saveBtn Save button.
     * @param {boolean} playing Whether playback is active.
     */
    function setNoteButtonState(saveBtn, playing) {
        if (!saveBtn) {
            return;
        }
        saveBtn.setAttribute('aria-disabled', playing ? 'false' : 'true');
        saveBtn.classList.toggle('videotrack-note-save-disabled', !playing);
    }

    /**
     * Announce when reactions become available or unavailable.
     *
     * @param {boolean} playing Whether playback is active.
     * @param {Object} config Player configuration.
     * @param {Object} reactionState Mutable reaction announcement state.
     */
    function announceReactionAvailability(playing, config, reactionState) {
        var hint = document.getElementById('videotrack-reactions-hint');
        if (!hint) {
            return;
        }
        if (playing) {
            if (reactionState.timer) {
                window.clearTimeout(reactionState.timer);
                reactionState.timer = null;
            }
            if (Date.now() - reactionState.lastUnavailableAt < reactionState.debounceMs) {
                return;
            }
            if (reactionState.readyAnnounced || reactionState.lastAnnouncement === true) {
                return;
            }
            reactionState.lastAnnouncement = true;
            reactionState.readyAnnounced = true;
            hint.textContent = config.reactionsreadylabel;
            hint.classList.toggle('videotrack-reactions-hint-active', false);
            return;
        }

        if (reactionState.timer) {
            return;
        }
        var now = Date.now();
        if (reactionState.lastAnnouncement === false &&
                now - reactionState.lastUnavailableAt < reactionState.unavailableInterval) {
            return;
        }
        reactionState.timer = window.setTimeout(function() {
            reactionState.timer = null;
            reactionState.lastAnnouncement = false;
            reactionState.lastUnavailableAt = Date.now();
            hint.textContent = config.reactionunavailablelabel;
            hint.classList.toggle('videotrack-reactions-hint-active', true);
        }, 400);
    }

    /**
     * Announce that reactions are unavailable immediately.
     *
     * @param {Object} config Player configuration.
     * @param {Object} reactionState Mutable reaction announcement state.
     */
    function announceReactionUnavailable(config, reactionState) {
        var hint = document.getElementById('videotrack-reactions-hint');
        if (hint) {
            if (reactionState.timer) {
                window.clearTimeout(reactionState.timer);
                reactionState.timer = null;
            }
            var now = Date.now();
            if (reactionState.lastAnnouncement === false && now - reactionState.lastUnavailableAt < 1000) {
                return;
            }
            reactionState.lastAnnouncement = false;
            reactionState.lastUnavailableAt = now;
            hint.textContent = config.reactionunavailablelabel;
            hint.classList.add('videotrack-reactions-hint-active');
            if (reactionState.cssTimer) {
                window.clearTimeout(reactionState.cssTimer);
            }
            reactionState.cssTimer = window.setTimeout(function() {
                hint.classList.remove('videotrack-reactions-hint-active');
                reactionState.cssTimer = null;
            }, 1500);
        }
    }

    /**
     * Remove a poster overlay on first playback event.
     *
     * @param {Event} e Custom playstate event.
     * @param {Object} state Player mutable state.
     * @param {Function} removePosterFn Callback that removes the poster overlay.
     */
    function onFirstPlay(e, state, removePosterFn) {
        if (e.detail && e.detail.playing && !state._posterRemoved) {
            state._posterRemoved = true;
            removePosterFn();
            document.removeEventListener('videotrack:playstate', state._posterPlayListener);
            state._posterPlayListener = null;
        }
    }

    /**
     * Append a newly saved personal note to the notes list.
     *
     * @param {number} noteid Note record id.
     * @param {number} videotime Video timestamp in seconds.
     * @param {string} text Note text.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     */
    function appendNoteRow(noteid, videotime, text, config, Utils) {
        var list = document.getElementById('videotrack-notes-list');
        if (!list) {
            return;
        }
        var li = document.createElement('li');
        li.className = 'videotrack-note-item';
        li.dataset.noteid = noteid;

        var timeSpan = document.createElement('span');
        timeSpan.className = 'videotrack-note-time text-muted me-1 small';
        timeSpan.textContent = Utils.formatSeconds(videotime);
        li.appendChild(timeSpan);

        var textSpan = document.createElement('span');
        textSpan.className = 'videotrack-note-text';
        textSpan.textContent = text;
        li.appendChild(textSpan);

        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'btn btn-link btn-sm videotrack-delete-note ms-1';
        delBtn.dataset.noteid = noteid;
        delBtn.textContent = config.removenotelabel;
        li.appendChild(delBtn);

        list.appendChild(li);
    }

    /**
     * Calculate remaining characters for a note textarea.
     *
     * @param {HTMLTextAreaElement} textarea Note textarea.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     * @returns {number} Remaining characters.
     */
    function getRemainingNoteChars(textarea, config, Utils) {
        var maxLength = parseInt(textarea.getAttribute('maxlength'), 10);
        if (!isFinite(maxLength) || maxLength <= 0) {
            maxLength = Utils.safeInt(config.notemaxlength, 2000);
        }
        return Math.max(0, maxLength - textarea.value.length);
    }


    /**
     * Install the personal note save/delete handlers shared by all player types.
     *
     * @param {Object} deps Dependencies and callbacks from the concrete player.
     * @param {Object} deps.Ajax Ajax module.
     * @param {Object} deps.Log Log module.
     * @param {Object} deps.Utils Utility module.
     * @param {Object} deps.config Player configuration.
     * @param {Object} deps.state Player mutable state.
     * @param {Function} deps.getCurrentVideoTime Current video time callback.
     * @param {Function} deps.saveCurrentProgress Progress persistence callback.
     */
    function installNoteHandler(deps) {
        var Ajax = deps.Ajax;
        var Log = deps.Log;
        var Utils = deps.Utils;
        var config = deps.config;
        var state = deps.state;
        var getCurrentVideoTime = deps.getCurrentVideoTime;
        var saveCurrentProgress = deps.saveCurrentProgress;

        if (!config.studentnotesenabled) { return; }

        var saveBtn = document.getElementById('videotrack-note-save');
        var textarea = document.getElementById('videotrack-note-input');
        if (!saveBtn || !textarea) { return; }

        function ajax(methodname, args) {
            return Ajax.call([{ methodname: methodname, args: args }])[0];
        }

        function setLocalNoteButtonState(playing) {
            setNoteButtonState(saveBtn, playing);
        }

        document.addEventListener('videotrack:playstate', function(e) {
            setLocalNoteButtonState(e.detail && e.detail.playing);
        });

        saveBtn.addEventListener('click', function() {
            if (saveBtn.getAttribute('aria-disabled') === 'true') { return; }
            var text = textarea.value.trim();
            if (!text) {
                textarea.focus();
                return;
            }
            var currentTime = getCurrentVideoTime();
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
                setLocalNoteButtonState(state.playing);
                if (response && response.noteeventid) {
                    appendNoteRow(response.noteeventid, currentTime, text, config, Utils);
                    textarea.value = '';
                    updateNoteCharCounter(textarea, config, Utils);
                    textarea.focus();
                }
            }).catch(function() {
                saveBtn.classList.remove('videotrack-note-save-saving');
                setLocalNoteButtonState(state.playing);
                showStatusMessage(config.noteerrorlabel, true);
            });
        });

        document.addEventListener('click', function(e) {
            var delBtn = e.target.closest('.videotrack-delete-note');
            if (!delBtn) { return; }
            var noteid = Utils.safeInt(delBtn.dataset.noteid, 0);
            if (!noteid) { return; }
            ajax('mod_videotrack_delete_note', {
                cmid:           config.cmid,
                reactioneventid: noteid,
            }).then(function(response) {
                if (response && response.deleted) {
                    var li = delBtn.closest('li');
                    if (li) {
                        var list = li.parentElement;
                        li.remove();
                        var next = list ? list.querySelector('.videotrack-note-item button') : null;
                        if (next) { next.focus(); } else if (textarea) { textarea.focus(); }
                    }
                }
            }).catch(function(err) { Log.debug('mod_videotrack: note deletion failed - ' + err); });
        });

        textarea.addEventListener('input', function() {
            updateNoteCharCounter(textarea, config, Utils);
        });
    }

    /**
     * Remove the poster overlay with the existing fade-out transition.
     *
     * @param {HTMLElement} overlay Poster overlay.
     */
    function removePoster(overlay) {
        if (overlay && overlay.parentElement) {
            overlay.style.opacity = '0';
            window.setTimeout(function() {
                if (overlay && overlay.parentElement) {
                    overlay.parentElement.removeChild(overlay);
                }
            }, 300);
        }
    }

    return {
        uuid: uuid,
        getIntervalBarColor: getIntervalBarColor,
        saveCurrentProgress: saveCurrentProgress,
        updateIntervalBar: updateIntervalBar,
        showResumeNotice: showResumeNotice,
        showStatusMessage: showStatusMessage,
        setNoteButtonState: setNoteButtonState,
        announceReactionAvailability: announceReactionAvailability,
        announceReactionUnavailable: announceReactionUnavailable,
        onFirstPlay: onFirstPlay,
        appendNoteRow: appendNoteRow,
        getRemainingNoteChars: getRemainingNoteChars,
        updateNoteCharCounter: updateNoteCharCounter,
        installNoteHandler: installNoteHandler,
        removePoster: removePoster
    };
});
