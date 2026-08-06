/**
 * Shared private-bookmark helpers for mod_videotrack player modules.
 *
 * @module mod_videotrack/core/player/bookmarks
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, promise/always-return, promise/no-nesting, promise/catch-or-return, complexity */
define(['mod_videotrack/core/debug'], function(Debug) {
    'use strict';

    function resolveBookmarkTime(progressResponse, fallbackTime) {
        var savedEnd = progressResponse && Number(progressResponse.savedvideotimeend);
        var time = Number(fallbackTime);
        if (Number.isFinite(savedEnd) && savedEnd >= 0) {
            return Math.max(0, savedEnd);
        }
        return Number.isFinite(time) ? Math.max(0, time) : 0;
    }

    function appendRow(bookmarkid, videotime, label, config, Utils) {
        var list = document.getElementById('videotrack-bookmarks-list');
        if (!list) {
            return false;
        }
        var placeholder = list.querySelector('.videotrack-no-bookmarks-placeholder');
        if (placeholder) {
            placeholder.remove();
        }
        var li = document.createElement('li');
        li.className = 'videotrack-bookmark-item d-flex flex-wrap align-items-center gap-1';
        li.dataset.bookmarkid = bookmarkid;
        li.dataset.videotime = videotime;

        var timeSpan = document.createElement('span');
        timeSpan.className = 'videotrack-bookmark-time text-muted small';
        timeSpan.textContent = Utils.formatSeconds(videotime);
        li.appendChild(timeSpan);

        var labelSpan = document.createElement('span');
        labelSpan.className = 'videotrack-bookmark-label flex-grow-1';
        labelSpan.textContent = label;
        li.appendChild(labelSpan);

        var replay = document.createElement('button');
        replay.type = 'button';
        replay.className = 'btn btn-secondary btn-sm videotrack-replay';
        replay.dataset.time = videotime;
        replay.dataset.start = videotime;
        replay.textContent = config.bookmarkreplaylabel;
        replay.setAttribute('aria-label', config.bookmarkreplaylabel + ' — ' + label + ' — ' + Utils.formatSeconds(videotime));
        li.appendChild(replay);

        var remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn btn-link btn-sm videotrack-delete-bookmark';
        remove.dataset.bookmarkid = bookmarkid;
        remove.textContent = config.removebookmarklabel;
        remove.setAttribute('aria-label', config.removebookmarklabel + ' — ' + label + ' — ' + Utils.formatSeconds(videotime));
        li.appendChild(remove);

        var inserted = false;
        Array.from(list.querySelectorAll('.videotrack-bookmark-item')).some(function(existing) {
            if (Number(existing.dataset.videotime) > Number(videotime)) {
                list.insertBefore(li, existing);
                inserted = true;
                return true;
            }
            return false;
        });
        if (!inserted) {
            list.appendChild(li);
        }

        var maxRendered = Utils.safeInt(config.bookmarksmaxrendered, 200);
        return list.querySelectorAll('.videotrack-bookmark-item').length > maxRendered;
    }

    function installHandler(deps, statusMessage, errorStatusMessage) {
        var Api = deps.Api;
        var Utils = deps.Utils;
        var config = deps.config;
        var state = deps.state;
        var getCurrentVideoTime = deps.getCurrentVideoTime;
        var saveCurrentProgress = deps.saveCurrentProgress;
        var showStatusMessage = deps.showStatusMessage || statusMessage;
        var showErrorStatusMessage = deps.showErrorStatusMessage || errorStatusMessage || function(error, fallback, dismiss) {
            showStatusMessage(error && error.message ? error.message : fallback, true, dismiss);
        };
        if (!config.bookmarksenabled || typeof showStatusMessage !== 'function') {
            return;
        }

        var input = document.getElementById('videotrack-bookmark-input');
        var save = document.getElementById('videotrack-bookmark-save');
        var list = document.getElementById('videotrack-bookmarks-list');
        if (!input || !save || !list) {
            return;
        }
        var saving = false;
        var limitedAnnounced = false;

        function ajax(methodname, args) {
            return Api.call(methodname, args, {
                retries: 1,
                errorMessage: config.bookmarkerrorlabel || config.statuserrorlabel,
                requestScope: state.ajaxRequestScope
            });
        }

        function restore() {
            saving = false;
            state.bookmarkSaveInProgress = false;
            save.disabled = false;
            save.setAttribute('aria-disabled', 'false');
            save.removeAttribute('aria-busy');
        }

        var saveHandler = function(event) {
            event.preventDefault();
            if (saving || state.bookmarkSaveInProgress) {
                return;
            }
            var label = input.value.trim();
            var maxlength = Utils.safeInt(config.bookmarkmaxlength, 120);
            if (!label) {
                showStatusMessage(config.bookmarkemptylabel, true, config.dismisslabel);
                input.focus();
                return;
            }
            if (label.length > maxlength) {
                showStatusMessage(config.bookmarktoolonglabel, true, config.dismisslabel);
                input.focus();
                return;
            }
            saving = true;
            state.bookmarkSaveInProgress = true;
            save.disabled = true;
            save.setAttribute('aria-disabled', 'true');
            save.setAttribute('aria-busy', 'true');
            var currentTime = 0;
            Promise.resolve(saveCurrentProgress('bookmark')).then(function(progressResponse) {
                return Promise.resolve(getCurrentVideoTime()).then(function(time) {
                    currentTime = resolveBookmarkTime(progressResponse, time);
                });
            }).then(function() {
                return ajax('mod_videotrack_save_bookmark', {
                    cmid: config.cmid,
                    sessionid: state.sessionid,
                    videotime: currentTime,
                    label: label,
                    playbackrate: state.playbackrate || 1
                });
            }).then(function(response) {
                restore();
                if (!response || !response.bookmarkeventid) {
                    showStatusMessage(config.bookmarkerrorlabel, true, config.dismisslabel);
                    return;
                }
                if (appendRow(response.bookmarkeventid, Number(response.videotime), response.label, config, Utils) &&
                        !limitedAnnounced) {
                    limitedAnnounced = true;
                    showStatusMessage(config.bookmarkslimitedlabel, false, config.dismisslabel);
                }
                input.value = '';
                input.focus();
                showStatusMessage(config.bookmarksavedlabel, false, config.dismisslabel);
            }).catch(function(error) {
                restore();
                showErrorStatusMessage(error, config.bookmarkerrorlabel, config.dismisslabel);
            });
        };

        var listHandler = function(event) {
            var button = event.target.closest('.videotrack-delete-bookmark');
            if (!button || !list.contains(button)) {
                return;
            }
            event.preventDefault();
            if (button.disabled || button.getAttribute('aria-busy') === 'true') {
                return;
            }
            var bookmarkid = Utils.safeInt(button.dataset.bookmarkid, 0);
            if (!bookmarkid) {
                return;
            }
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            ajax('mod_videotrack_delete_bookmark', {
                cmid: config.cmid,
                bookmarkeventid: bookmarkid
            }).then(function(response) {
                if (response && response.deleted) {
                    var row = button.closest('.videotrack-bookmark-item');
                    if (row) {
                        row.remove();
                    }
                    if (!list.querySelector('.videotrack-bookmark-item')) {
                        var placeholder = document.createElement('li');
                        placeholder.className = 'videotrack-no-bookmarks-placeholder small text-muted';
                        placeholder.textContent = config.bookmarksnonelabel;
                        list.appendChild(placeholder);
                    }
                    input.focus();
                    showStatusMessage(config.bookmarkdeletedlabel, false, config.dismisslabel);
                }
                if (button.isConnected) {
                    button.disabled = false;
                    button.removeAttribute('aria-busy');
                }
            }).catch(function(error) {
                if (button.isConnected) {
                    button.disabled = false;
                    button.removeAttribute('aria-busy');
                }
                Debug.log('bookmarkdeletionfailed', {message: error && error.message});
                showErrorStatusMessage(error, config.bookmarkerrorlabel, config.dismisslabel);
            });
        };

        var cleanup = function() {
            save.removeEventListener('click', saveHandler);
            list.removeEventListener('click', listHandler);
            window.removeEventListener('pagehide', cleanup);
            window.removeEventListener('beforeunload', cleanup);
        };
        save.addEventListener('click', saveHandler);
        list.addEventListener('click', listHandler);
        window.addEventListener('pagehide', cleanup, {once: true});
        window.addEventListener('beforeunload', cleanup, {once: true});
    }

    return {
        appendRow: appendRow,
        resolveBookmarkTime: resolveBookmarkTime,
        installHandler: installHandler
    };
});
