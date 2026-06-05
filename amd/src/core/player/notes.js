/**
 * Shared personal notes helpers for mod_videotrack player modules.
 *
 * The concrete player modules pass player-specific callbacks for current time
 * and segment persistence, while note DOM handling and AJAX payloads stay here.
 *
 * @module mod_videotrack/core/player/notes
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, no-control-regex, promise/always-return, promise/no-nesting, promise/catch-or-return, no-throw-literal, promise/no-return-wrap, complexity */
define([
    'mod_videotrack/core/player/notes/row',
    'mod_videotrack/core/player/notes/toggle',
    'mod_videotrack/core/debug'
], function(NoteRow, NoteToggle, Debug) {
    'use strict';


    var CHAR_COUNTER_DEBOUNCE_MS = 120;

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
     * Update the enabled state of the note save button.
     *
     * @param {HTMLButtonElement} saveBtn Save button.
     * @param {boolean} enabled Whether the save action is available.
     */
    function setButtonState(saveBtn, enabled) {
        if (!saveBtn) {
            return;
        }
        saveBtn.disabled = !enabled;
        saveBtn.setAttribute('aria-disabled', enabled ? 'false' : 'true');
        saveBtn.classList.toggle('videotrack-note-save-disabled', !enabled);
    }

    /**
     * Install the personal note save/delete handlers shared by all player types.
     *
     * @param {Object} deps Dependencies and callbacks from the concrete player.
     * @param {Object} deps.Utils Utility module.
     * @param {Object} deps.config Player configuration.
     * @param {Object} deps.state Player mutable state.
     * @param {Function} deps.getCurrentVideoTime Current video time callback.
     * @param {Function} deps.saveCurrentProgress Progress persistence callback.
     * @param {Function} deps.showStatusMessage User-visible status callback.
     */
    function installHandler(deps) {
        var Api = deps.Api;
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
        var noteSaveToken = 0;
        var charCounterTimer = null;
        var lastCharThreshold = null;
        var limitedNotesAnnounced = false;
        if (!saveBtn || !textarea) { return; }

        function ajax(methodname, args) {
            // Notes are user initiated: keep one retry so failures surface quickly to the user.
            return Api.call(methodname, args, {
                retries: 1,
                errorMessage: config.noteerrorlabel || config.statuserrorlabel || 'mod_videotrack_save_note',
                requestScope: state.ajaxRequestScope
            });
        }

        function restoreSaveButtonState(token) {
            if (token && state._noteSaveToken !== token) {
                return;
            }
            savingNote = false;
            state.noteSaveInProgress = false;
            saveBtn.removeAttribute('aria-busy');
            saveBtn.classList.remove('videotrack-note-save-saving');
            setLocalButtonState(state.playing);
        }

        function showResponseWarnings(response) {
            if (!response || !response.warnings || !response.warnings.length) {
                return;
            }
            response.warnings.forEach(function(warning) {
                if (warning && warning.message) {
                    showStatusMessage(warning.message, true, config.dismisslabel);
                }
            });
        }

        function announceLimitedNotes() {
            if (!config.studentnoteslimitedlabel || limitedNotesAnnounced) {
                return;
            }
            limitedNotesAnnounced = true;
            showStatusMessage(config.studentnoteslimitedlabel, false, config.dismisslabel);
        }

        function announceCharThreshold(remaining) {
            if (remaining > 50) {
                lastCharThreshold = null;
                return;
            }
            var live = document.getElementById('videotrack-note-live-status');
            if (!live) {
                return;
            }
            var threshold = null;
            if (remaining === 0) {
                threshold = 0;
            } else if (remaining <= 10) {
                threshold = 10;
            } else if (remaining <= 50) {
                threshold = 50;
            }
            if (threshold === null || threshold === lastCharThreshold) {
                return;
            }
            lastCharThreshold = threshold;
            if (threshold === 0) {
                live.setAttribute('role', 'alert');
                live.setAttribute('aria-live', 'assertive');
            } else {
                live.setAttribute('role', 'status');
                live.setAttribute('aria-live', 'polite');
            }
            live.textContent = remaining + ' ' + config.charsremaininglabel;
        }

        function setLocalButtonState(playing) {
            setButtonState(saveBtn, playing && !savingNote && !state.noteSaveInProgress);
        }

        var playStateHandler = function(e) {
            setLocalButtonState(!!(e.detail && e.detail.playing));
        };
        var cleanupNoteHandler = function() {
            document.removeEventListener('videotrack:playstate', playStateHandler);
            saveBtn.removeEventListener('click', saveClickHandler);
            if (noteList) {
                noteList.removeEventListener('click', noteListClickHandler);
            }
            textarea.removeEventListener('input', textareaInputHandler);
            window.removeEventListener('pagehide', cleanupNoteHandler);
            window.removeEventListener('beforeunload', cleanupNoteHandler);
            if (charCounterTimer) {
                window.clearTimeout(charCounterTimer);
                charCounterTimer = null;
            }
        };
        var noteList = document.getElementById('videotrack-my-notes');

        var saveClickHandler = function(event) {
            if (event) {
                event.preventDefault();
            }
            if (savingNote || state.noteSaveInProgress || saveBtn.getAttribute('aria-disabled') === 'true') {
                if (!state.playing) {
                    showStatusMessage(config.noteplaybackrequiredlabel || config.reactionunavailablelabel,
                        false, config.dismisslabel);
                }
                return;
            }
            var maxLength = Utils.safeInt(config.notemaxlength, 2000);
            var text = textarea.value.trim();
            if (maxLength > 0 && text.length > maxLength) {
                showStatusMessage(config.notetoolonglabel || config.noteerrorlabel, true, config.dismisslabel);
                if (textarea.isConnected) {
                    textarea.focus();
                }
                return;
            }
            if (!text) {
                showStatusMessage(config.noteemptylabel || config.noteerrorlabel, true, config.dismisslabel);
                if (textarea.isConnected) {
                    textarea.focus();
                }
                return;
            }
            var currentTime = getCurrentVideoTime();
            savingNote = true;
            state.noteSaveInProgress = true;
            noteSaveToken += 1;
            var currentNoteSaveToken = noteSaveToken;
            state._noteSaveToken = currentNoteSaveToken;
            saveBtn.disabled = true;
            saveBtn.setAttribute('aria-disabled', 'true');
            saveBtn.setAttribute('aria-busy', 'true');
            saveBtn.classList.add('videotrack-note-save-saving');
            Promise.resolve(saveCurrentProgress('note')).then(function() {
                return ajax('mod_videotrack_save_note', {
                    cmid: config.cmid,
                    sessionid: state.sessionid,
                    videotime: currentTime,
                    notetext: text,
                    playbackrate: state.playbackrate || 1
                });
            }).then(function(response) {
                restoreSaveButtonState(currentNoteSaveToken);
                showResponseWarnings(response);
                if (response && response.noteeventid) {
                    if (NoteRow.appendRow(response.noteeventid, currentTime, text, config, Utils)) {
                        announceLimitedNotes();
                    }
                    textarea.value = '';
                    updateCharCounter(textarea, config, Utils);
                    lastCharThreshold = null;
                    if (textarea.isConnected) {
                        textarea.focus();
                    }
                    if (config.notesavedlabel && !(response && response.warnings && response.warnings.length)) {
                        showStatusMessage(config.notesavedlabel, false, config.dismisslabel);
                    }
                    return;
                }
                showStatusMessage(config.noteerrorlabel, true, config.dismisslabel);
            }).catch(function(error) {
                restoreSaveButtonState(currentNoteSaveToken);
                showErrorStatusMessage(error, config.noteerrorlabel, config.dismisslabel);
            });
        };

        var noteListClickHandler = function(e) {
                var delBtn = e.target.closest('.videotrack-delete-note');
                if (!delBtn || !noteList.contains(delBtn)) { return; }
                e.preventDefault();
                if (delBtn.disabled || delBtn.getAttribute('aria-busy') === 'true') { return; }
                var noteid = Utils.safeInt(delBtn.dataset.noteid, 0);
                if (!noteid) { return; }
                // Native disabled is deliberate here: the button is removed from
                // the DOM after a successful delete, and disabling prevents
                // duplicate destructive requests from rapid double clicks.
                delBtn.disabled = true;
                delBtn.setAttribute('aria-busy', 'true');
                ajax('mod_videotrack_delete_note', {
                    cmid: config.cmid,
                    noteeventid: noteid
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
                    if (delBtn.isConnected) {
                        delBtn.disabled = false;
                        delBtn.removeAttribute('aria-busy');
                    }
                }).catch(function(err) {
                    if (delBtn.isConnected) {
                        delBtn.disabled = false;
                        delBtn.removeAttribute('aria-busy');
                    }
                    Debug.log('notedeletionfailed', {message: err});
                    showErrorStatusMessage(err, config.noteerrorlabel, config.dismisslabel);
                });
        };

        var textareaInputHandler = function() {
            var remaining = updateCharCounter(textarea, config, Utils);
            if (charCounterTimer) {
                window.clearTimeout(charCounterTimer);
            }
            charCounterTimer = window.setTimeout(function() {
                announceCharThreshold(remaining);
                charCounterTimer = null;
            }, CHAR_COUNTER_DEBOUNCE_MS);
        };

        document.addEventListener('videotrack:playstate', playStateHandler);
        saveBtn.addEventListener('click', saveClickHandler);
        if (noteList) {
            noteList.addEventListener('click', noteListClickHandler);
        }
        textarea.addEventListener('input', textareaInputHandler);
        window.addEventListener('pagehide', cleanupNoteHandler, {once: true});
        window.addEventListener('beforeunload', cleanupNoteHandler, {once: true});
    }



    return {
        appendRow: NoteRow.appendRow,
        getRemainingChars: getRemainingChars,
        updateCharCounter: updateCharCounter,
        setButtonState: setButtonState,
        installHandler: installHandler,
        installToggle: NoteToggle.install
    };
});
