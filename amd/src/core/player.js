/**
 * Shared player UI helpers for mod_videotrack AMD player modules.
 *
 * This module contains helper functions used by the HTML5, YouTube and Vimeo
 * player entrypoints. It intentionally avoids player-API-specific code.
 *
 * @module mod_videotrack/core/player
 */
define([], function() {

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
        window.setTimeout(function() {
            el.textContent = '';
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
        showResumeNotice: showResumeNotice,
        showStatusMessage: showStatusMessage,
        setNoteButtonState: setNoteButtonState,
        appendNoteRow: appendNoteRow,
        getRemainingNoteChars: getRemainingNoteChars,
        updateNoteCharCounter: updateNoteCharCounter,
        removePoster: removePoster
    };
});
