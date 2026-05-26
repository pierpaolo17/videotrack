/**
 * Shared personal notes helpers for mod_videotrack player modules.
 *
 * The concrete player modules pass player-specific callbacks for current time
 * and segment persistence, while note DOM handling and AJAX payloads stay here.
 *
 * @module mod_videotrack/core/notes
 */
define([], function() {

    /**
     * Calculate remaining characters for a note textarea.
     *
     * @param {HTMLTextAreaElement} textarea Note textarea.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     * @returns {number} Remaining characters.
     */
    function getRemainingChars(textarea, config, Utils) {
        var maxLength = parseInt(textarea.getAttribute('maxlength'), 10);
        if (!isFinite(maxLength) || maxLength <= 0) {
            maxLength = Utils.safeInt(config.notemaxlength, 2000);
        }
        return Math.max(0, maxLength - textarea.value.length);
    }

    /**
     * Update the note character counter next to a textarea.
     *
     * @param {HTMLTextAreaElement} textarea Note textarea.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     * @returns {number} Remaining characters.
     */
    function updateCharCounter(textarea, config, Utils) {
        if (!textarea) {
            return 0;
        }
        var remaining = getRemainingChars(textarea, config, Utils);
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
    function setButtonState(saveBtn, playing) {
        if (!saveBtn) {
            return;
        }
        // Keep the button focusable for keyboard and screen-reader users.
        saveBtn.disabled = false;
        saveBtn.setAttribute('aria-disabled', playing ? 'false' : 'true');
        saveBtn.classList.toggle('videotrack-note-save-disabled', !playing);
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
    function appendRow(noteid, videotime, text, config, Utils) {
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
        delBtn.setAttribute('aria-label', (config.removenotelabel || '') + ' — ' + Utils.formatSeconds(videotime));
        li.appendChild(delBtn);

        list.appendChild(li);

        var maxRenderedNotes = Utils.safeInt(config.notesmaxrendered, 200);
        while (list.children.length > maxRenderedNotes) {
            list.removeChild(list.firstElementChild);
        }
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
     * @param {Function} deps.showStatusMessage User-visible status callback.
     */
    function installHandler(deps) {
        var Ajax = deps.Ajax;
        var Log = deps.Log;
        var Utils = deps.Utils;
        var config = deps.config;
        var state = deps.state;
        var getCurrentVideoTime = deps.getCurrentVideoTime;
        var saveCurrentProgress = deps.saveCurrentProgress;
        var showStatusMessage = deps.showStatusMessage;
        var showErrorStatusMessage = deps.showErrorStatusMessage || function(error, fallbackMessage, dismissLabel) {
            var message = (error && error.message) ? error.message : fallbackMessage;
            showStatusMessage(message, true, dismissLabel);
        };

        if (!config.studentnotesenabled) { return; }

        var saveBtn = document.getElementById('videotrack-note-save');
        var textarea = document.getElementById('videotrack-note-input');
        var savingNote = false;
        var charCounterTimer = null;
        if (!saveBtn || !textarea) { return; }

        function ajax(methodname, args) {
            return Ajax.call([{methodname: methodname, args: args}])[0];
        }

        function setLocalButtonState(playing) {
            setButtonState(saveBtn, playing);
        }

        var playStateHandler = function(e) {
            setLocalButtonState(e.detail && e.detail.playing);
        };
        document.addEventListener('videotrack:playstate', playStateHandler);
        window.addEventListener('beforeunload', function() {
            document.removeEventListener('videotrack:playstate', playStateHandler);
            if (charCounterTimer) {
                window.clearTimeout(charCounterTimer);
                charCounterTimer = null;
            }
        }, {once: true});

        saveBtn.addEventListener('click', function() {
            if (savingNote || saveBtn.getAttribute('aria-disabled') === 'true') {
                if (!state.playing) {
                    showStatusMessage(config.noteplaybackrequiredlabel || config.reactionunavailablelabel,
                        false, config.dismisslabel);
                }
                return;
            }
            var maxLength = Utils.safeInt(config.notemaxlength, 2000);
            var text = textarea.value.trim();
            if (maxLength > 0 && text.length > maxLength) {
                text = text.substring(0, maxLength);
                textarea.value = text;
                updateCharCounter(textarea, config, Utils);
            }
            if (!text) {
                textarea.focus();
                return;
            }
            var currentTime = getCurrentVideoTime();
            savingNote = true;
            saveBtn.disabled = true;
            saveBtn.setAttribute('aria-disabled', 'true');
            saveBtn.setAttribute('aria-busy', 'true');
            saveBtn.classList.add('videotrack-note-save-saving');
            saveCurrentProgress('note').then(function() {
                return ajax('mod_videotrack_save_note', {
                    cmid: config.cmid,
                    sessionid: state.sessionid,
                    videotime: currentTime,
                    notetext: text,
                    playbackrate: state.playbackrate || 1
                });
            }).then(function(response) {
                savingNote = false;
                saveBtn.removeAttribute('aria-busy');
                saveBtn.classList.remove('videotrack-note-save-saving');
                setLocalButtonState(state.playing);
                if (response && response.noteeventid) {
                    appendRow(response.noteeventid, currentTime, text, config, Utils);
                    textarea.value = '';
                    updateCharCounter(textarea, config, Utils);
                    textarea.focus();
                    if (config.notesavedlabel) {
                        showStatusMessage(config.notesavedlabel, false, config.dismisslabel);
                    }
                }
            }).catch(function(error) {
                savingNote = false;
                saveBtn.removeAttribute('aria-busy');
                saveBtn.classList.remove('videotrack-note-save-saving');
                setLocalButtonState(state.playing);
                showErrorStatusMessage(error, config.noteerrorlabel, config.dismisslabel);
            });
        });

        var noteList = document.getElementById('videotrack-my-notes');
        if (noteList) {
            noteList.addEventListener('click', function(e) {
                var delBtn = e.target.closest('.videotrack-delete-note');
                if (!delBtn || !noteList.contains(delBtn)) { return; }
                var noteid = Utils.safeInt(delBtn.dataset.noteid, 0);
                if (!noteid) { return; }
                ajax('mod_videotrack_delete_note', {
                    cmid: config.cmid,
                    reactioneventid: noteid
                }).then(function(response) {
                    if (response && response.deleted) {
                        var li = delBtn.closest('li');
                        if (li) {
                            var list = li.parentElement;
                            li.remove();
                            var next = list ? list.querySelector('.videotrack-note-item button') : null;
                            if (next) { next.focus(); } else if (textarea) { textarea.focus(); }
                            if (config.notedeletedlabel) {
                                showStatusMessage(config.notedeletedlabel, false, config.dismisslabel);
                            }
                        }
                    }
                }).catch(function(err) {
                    Log.debug('mod_videotrack: note deletion failed - ' + err);
                    showStatusMessage(config.noteerrorlabel, true, config.dismisslabel);
                });
            });
        }

        textarea.addEventListener('input', function() {
            if (charCounterTimer) {
                window.clearTimeout(charCounterTimer);
            }
            charCounterTimer = window.setTimeout(function() {
                updateCharCounter(textarea, config, Utils);
                charCounterTimer = null;
            }, 120);
        });
    }


    /**
     * Install the personal notes panel collapse/expand toggle.
     *
     * Keeping this in the notes module avoids maintaining three copies of the
     * same DOM/sessionStorage logic in the YouTube, HTML5 and Vimeo entrypoints.
     *
     * @param {Object} config Player configuration.
     * @param {Object} Utils Shared utility module.
     * @param {string} contextLabel Log context used by sessionStorage helpers.
     */
    function installToggle(config, Utils, contextLabel) {
        var btn = document.getElementById('videotrack-notes-toggle');
        var body = document.getElementById('videotrack-notes-body');
        if (!btn || !body) {
            return;
        }

        var key = 'videotrack_notes_collapsed_' + (config.cmid ? String(config.cmid) : 'x');
        var labelContext = contextLabel || 'notes panel state';

        function setCollapsed(collapsed) {
            body.style.display = collapsed ? 'none' : '';
            btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            var label = collapsed ? config.noteshowlabel : config.noteshidelabel;
            btn.textContent = label;
            btn.setAttribute('aria-label', label + ': ' + config.notespaneltitle);
            Utils.sessionSet(key, collapsed ? '1' : '0', labelContext);
        }

        setCollapsed(Utils.sessionGet(key, labelContext) === '1');

        btn.addEventListener('click', function() {
            var isCollapsed = btn.getAttribute('aria-expanded') === 'false';
            setCollapsed(!isCollapsed);
        });
    }

    return {
        appendRow: appendRow,
        getRemainingChars: getRemainingChars,
        updateCharCounter: updateCharCounter,
        setButtonState: setButtonState,
        installHandler: installHandler,
        installToggle: installToggle
    };
});
