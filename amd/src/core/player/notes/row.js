/**
 * Personal notes list row helpers for mod_videotrack player modules.
 *
 * @module mod_videotrack/core/player/notes/row
 */
define([], function() {
    'use strict';

    /**
     * Append a newly saved personal note to the notes list.
     *
     * @param {number} noteid Note record id.
     * @param {number} videotime Video timestamp in seconds.
     * @param {string} text Note text.
     * @param {Object} config Player configuration.
     * @param {Object} Utils Utility module.
     * @returns {boolean} Whether an older rendered note was removed.
     */
    function appendRow(noteid, videotime, text, config, Utils) {
        var list = document.getElementById('videotrack-notes-list');
        if (!list) {
            return false;
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
        var removed = false;
        while (list.children.length > maxRenderedNotes) {
            list.removeChild(list.firstElementChild);
            removed = true;
        }
        return removed;
    }

    return {
        appendRow: appendRow
    };
});
