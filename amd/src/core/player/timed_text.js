/**
 * Provider-neutral interactive transcript and chapter navigation.
 *
 * @module mod_videotrack/core/player/timed_text
 */
/* eslint-disable promise/always-return, promise/catch-or-return */
define([
    'mod_videotrack/core/utils',
    'mod_videotrack/core/debug'
], function(Utils, Debug) {
    'use strict';

    var POLL_INTERVAL = 500;

    /**
     * Strip WebVTT inline markup through the browser HTML parser.
     *
     * @param {string} value Cue text.
     * @returns {string} Plain text.
     */
    function stripCueMarkup(value) {
        var raw = String(value || '');
        if (!raw || !window.DOMParser) {
            return '';
        }
        try {
            var documentNode = new window.DOMParser().parseFromString(raw, 'text/html');
            return documentNode.body && documentNode.body.textContent ? documentNode.body.textContent.trim() : '';
        } catch (error) {
            Debug.log('vttloadfailed', {message: error});
            return '';
        }
    }

    /**
     * Convert a WebVTT timestamp to seconds.
     *
     * @param {string} timestamp WebVTT timestamp.
     * @returns {number} Seconds or NaN.
     */
    function vttTime(timestamp) {
        var parts = String(timestamp || '').split(':');
        if (parts.length < 2 || parts.length > 3) {
            return NaN;
        }
        var seconds = parseFloat(parts.pop());
        var minutes = parseInt(parts.pop(), 10);
        var hours = parts.length ? parseInt(parts.pop(), 10) : 0;
        if (!isFinite(seconds) || !isFinite(minutes) || !isFinite(hours)) {
            return NaN;
        }
        if (minutes < 0 || minutes >= 60 || seconds < 0 || seconds >= 60 || hours < 0) {
            return NaN;
        }
        return hours * 3600 + minutes * 60 + seconds;
    }

    /**
     * Parse WebVTT cues.
     *
     * @param {string} text WebVTT payload.
     * @returns {Array} Parsed cues.
     */
    function parseVtt(text) {
        var cues = [];
        if (!text) {
            return cues;
        }
        var normalised = text.replace(/^\uFEFF/, '').replace(/\r\n?/g, '\n');
        var blocks = normalised.split(/\n[ \t]*\n/);
        var timePattern = /^((?:\d{2}:)?\d{2}:\d{2}\.\d{3})[ \t]*-->[ \t]*((?:\d{2}:)?\d{2}:\d{2}\.\d{3})(?:[ \t].*)?$/;

        blocks.forEach(function(block) {
            var lines = block.trim().split('\n').map(function(line) {
                return line.trim();
            });
            if (!lines.length || /^(WEBVTT|NOTE|STYLE|REGION)(?:\s|$)/i.test(lines[0])) {
                return;
            }
            var timeLine = -1;
            for (var index = 0; index < lines.length; index++) {
                if (timePattern.test(lines[index])) {
                    timeLine = index;
                    break;
                }
            }
            if (timeLine < 0) {
                return;
            }
            var match = lines[timeLine].match(timePattern);
            var start = vttTime(match[1]);
            var end = vttTime(match[2]);
            var cueText = stripCueMarkup(lines.slice(timeLine + 1).join(' '));
            if (!isFinite(start) || !isFinite(end) || end <= start || !cueText) {
                return;
            }
            cues.push({start: start, end: end, text: cueText});
        });
        return cues;
    }

    /**
     * Replace the count token in a localised label.
     *
     * @param {string} template Label template.
     * @param {number} count Result count.
     * @returns {string} Label.
     */
    function countLabel(template, count) {
        return String(template || '').replace('__COUNT__', String(count));
    }

    /**
     * Create the provider-neutral timed-text controller.
     *
     * @param {Object} options Controller options.
     * @returns {Object} Controller with destroy and update methods.
     */
    function create(options) {
        var config = options.config || {};
        var transcriptPanel = document.getElementById('videotrack-transcript-panel');
        var transcriptContent = document.getElementById('videotrack-transcript-content');
        var chaptersContainer = document.getElementById('videotrack-chapters-container');
        var transcriptTracks = Array.isArray(config.transcripttracks) ? config.transcripttracks : [];
        var transcriptCues = [];
        var chapterCues = [];
        var transcriptItems = [];
        var chapterButtons = [];
        var activeTranscript = -1;
        var activeChapter = -1;
        var lastScrollAt = 0;
        var pollTimer = null;
        var pollPending = false;
        var destroyed = false;
        var searchInput = null;
        var resultStatus = null;
        var languageSelect = null;
        var statusNode = null;

        /**
         * Announce a short timed-text status message.
         *
         * @param {string} message Message text.
         */
        function announce(message) {
            if (!statusNode) {
                statusNode = document.createElement('p');
                statusNode.className = 'videotrack-timed-text-status small text-muted mb-1';
                statusNode.setAttribute('role', 'status');
                statusNode.setAttribute('aria-live', 'polite');
                if (transcriptPanel) {
                    transcriptPanel.insertBefore(statusNode, transcriptContent || null);
                } else if (chaptersContainer) {
                    chaptersContainer.appendChild(statusNode);
                }
            }
            statusNode.textContent = message || '';
            statusNode.hidden = !message;
        }

        /**
         * Run a provider-specific navigation request.
         *
         * @param {number} target Target timestamp.
         */
        function navigate(target) {
            Promise.resolve(options.navigate(target)).then(function(allowed) {
                if (allowed === false) {
                    announce(config.timedtextseekblockedlabel || '');
                } else {
                    announce('');
                }
            }).catch(function(error) {
                Debug.log('timedtextseekfailed', {message: error});
                announce(config.timedtextseekfailedlabel || config.timedtextseekblockedlabel || '');
            });
        }

        /**
         * Choose the transcript language matching the current Moodle language.
         *
         * @returns {number} Preferred track index.
         */
        function preferredTrackIndex() {
            var preferred = String(config.transcriptdefaultlanguage || '').toLocaleLowerCase();
            if (!preferred) {
                return 0;
            }
            var exact = transcriptTracks.findIndex(function(track) {
                return String(track.language || '').toLocaleLowerCase() === preferred;
            });
            if (exact >= 0) {
                return exact;
            }
            var base = preferred.split('-')[0];
            var baseMatch = transcriptTracks.findIndex(function(track) {
                return String(track.language || '').toLocaleLowerCase().split('-')[0] === base;
            });
            return baseMatch >= 0 ? baseMatch : 0;
        }

        /**
         * Build transcript search and language controls.
         */
        function buildTranscriptControls() {
            if (!transcriptPanel || transcriptPanel.querySelector('.videotrack-transcript-controls')) {
                return;
            }
            var controls = document.createElement('div');
            controls.className = 'videotrack-transcript-controls mb-2';

            if (transcriptTracks.length > 1) {
                var languageLabel = document.createElement('label');
                languageLabel.className = 'form-label small mb-1';
                languageLabel.setAttribute('for', 'videotrack-transcript-language');
                languageLabel.textContent = config.transcriptlanguagelabel || '';
                languageSelect = document.createElement('select');
                languageSelect.id = 'videotrack-transcript-language';
                languageSelect.className = 'form-select form-select-sm mb-2';
                transcriptTracks.forEach(function(track, index) {
                    var option = document.createElement('option');
                    option.value = String(index);
                    option.textContent = track.label || track.language || String(index + 1);
                    languageSelect.appendChild(option);
                });
                languageSelect.value = String(preferredTrackIndex());
                languageSelect.addEventListener('change', function() {
                    loadTranscript(parseInt(languageSelect.value, 10) || 0);
                });
                controls.appendChild(languageLabel);
                controls.appendChild(languageSelect);
            }

            var searchLabel = document.createElement('label');
            searchLabel.className = 'form-label small mb-1';
            searchLabel.setAttribute('for', 'videotrack-transcript-search');
            searchLabel.textContent = config.transcriptsearchlabel || '';
            searchInput = document.createElement('input');
            searchInput.id = 'videotrack-transcript-search';
            searchInput.type = 'search';
            searchInput.className = 'form-control form-control-sm';
            searchInput.placeholder = config.transcriptsearchplaceholder || '';
            searchInput.addEventListener('input', filterTranscript);
            resultStatus = document.createElement('span');
            resultStatus.className = 'videotrack-transcript-results small text-muted d-block mt-1';
            resultStatus.setAttribute('role', 'status');
            resultStatus.setAttribute('aria-live', 'polite');
            controls.appendChild(searchLabel);
            controls.appendChild(searchInput);
            controls.appendChild(resultStatus);
            transcriptPanel.insertBefore(controls, transcriptContent || null);
        }

        /**
         * Filter visible transcript cues.
         */
        function filterTranscript() {
            var query = searchInput ? searchInput.value.trim().toLocaleLowerCase() : '';
            var visible = 0;
            transcriptItems.forEach(function(item) {
                var matches = !query || item.dataset.search.indexOf(query) !== -1;
                item.hidden = !matches;
                if (matches) {
                    visible++;
                }
            });
            if (resultStatus) {
                resultStatus.textContent = countLabel(config.transcriptresultslabel, visible);
            }
        }

        /**
         * Render transcript cues.
         *
         * @param {Array} cues Parsed transcript cues.
         */
        function renderTranscript(cues) {
            transcriptContent.innerHTML = '';
            transcriptItems = [];
            activeTranscript = -1;
            var list = document.createElement('ol');
            list.className = 'videotrack-transcript-list list-unstyled mb-0';
            cues.forEach(function(cue, index) {
                var item = document.createElement('li');
                item.className = 'videotrack-transcript-cue';
                item.dataset.index = String(index);
                item.dataset.search = cue.text.toLocaleLowerCase();
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-link btn-sm text-start videotrack-transcript-btn';
                button.setAttribute('aria-label', Utils.formatSeconds(cue.start) + ' — ' + cue.text);
                button.setAttribute('aria-controls', 'mod-videotrack-player');
                var time = document.createElement('span');
                time.className = 'videotrack-transcript-time text-muted me-1';
                time.textContent = Utils.formatSeconds(cue.start);
                var text = document.createElement('span');
                text.textContent = cue.text;
                button.appendChild(time);
                button.appendChild(text);
                button.addEventListener('click', function() {
                    navigate(cue.start);
                });
                item.appendChild(button);
                list.appendChild(item);
                transcriptItems.push(item);
            });
            transcriptContent.appendChild(list);
            filterTranscript();
        }

        /**
         * Show a transcript-unavailable message.
         */
        function showTranscriptUnavailable() {
            if (!transcriptContent) {
                return;
            }
            transcriptContent.innerHTML = '';
            var message = document.createElement('p');
            message.className = 'videotrack-transcript-empty text-muted mb-0';
            message.setAttribute('role', 'status');
            message.textContent = config.transcriptunavailablelabel || '';
            transcriptContent.appendChild(message);
        }

        /**
         * Load one transcript language.
         *
         * @param {number} index Track index.
         */
        function loadTranscript(index) {
            var track = transcriptTracks[index];
            if (!transcriptContent || !track || !track.url) {
                showTranscriptUnavailable();
                return;
            }
            transcriptContent.textContent = config.transcriptloadinglabel || '';
            Utils.fetchTextWithTimeout(track.url).then(function(text) {
                transcriptCues = parseVtt(text);
                if (!transcriptCues.length) {
                    showTranscriptUnavailable();
                    return;
                }
                renderTranscript(transcriptCues);
            }).catch(function(error) {
                Debug.log('vttloadfailed', {message: error});
                showTranscriptUnavailable();
            });
        }

        /**
         * Render the chapter navigation buttons.
         *
         * @param {Array} cues Parsed chapter cues.
         */
        function renderChapters(cues) {
            chaptersContainer.innerHTML = '';
            chapterButtons = [];
            activeChapter = -1;
            var bar = document.createElement('div');
            bar.className = 'videotrack-chapters-bar';
            bar.setAttribute('role', 'navigation');
            bar.setAttribute('aria-label', config.chapterslabel || '');
            cues.forEach(function(cue, index) {
                var button = document.createElement('button');
                button.type = 'button';
                button.className = 'videotrack-chapter-btn';
                button.setAttribute('aria-label', (config.chapterlabel || '') + ' ' + (index + 1) + ': ' + cue.text);
                var number = document.createElement('span');
                number.className = 'videotrack-chapter-num';
                number.textContent = String(index + 1);
                var text = document.createElement('span');
                text.className = 'videotrack-chapter-text';
                text.textContent = cue.text;
                button.appendChild(number);
                button.appendChild(text);
                button.addEventListener('click', function() {
                    navigate(cue.start);
                });
                bar.appendChild(button);
                chapterButtons.push(button);
            });
            chaptersContainer.appendChild(bar);
        }

        /**
         * Show a chapters-unavailable message.
         */
        function showChaptersUnavailable() {
            if (!chaptersContainer) {
                return;
            }
            chaptersContainer.innerHTML = '';
            var message = document.createElement('p');
            message.className = 'videotrack-chapters-empty text-muted small mb-2';
            message.setAttribute('role', 'status');
            message.textContent = config.chaptersunavailablelabel || '';
            chaptersContainer.appendChild(message);
        }

        /**
         * Load the chapter WebVTT file.
         */
        function loadChapters() {
            if (!chaptersContainer || !config.chapterurl) {
                showChaptersUnavailable();
                return;
            }
            chaptersContainer.textContent = config.chaptersloadinglabel || '';
            Utils.fetchTextWithTimeout(config.chapterurl).then(function(text) {
                chapterCues = parseVtt(text);
                if (config.chapterlegacymode) {
                    chapterCues = chapterCues.filter(function(cue) {
                        return cue.text.length <= 80;
                    });
                }
                if (!chapterCues.length) {
                    showChaptersUnavailable();
                    return;
                }
                renderChapters(chapterCues);
            }).catch(function(error) {
                Debug.log('chaptersfailed', {message: error});
                showChaptersUnavailable();
            });
        }

        /**
         * Update active transcript and chapter markers.
         *
         * @param {number} currentTime Current playback time.
         */
        function update(currentTime) {
            var time = Number(currentTime);
            if (!isFinite(time)) {
                return;
            }
            var transcriptIndex = -1;
            for (var index = 0; index < transcriptCues.length; index++) {
                if (time >= transcriptCues[index].start && time < transcriptCues[index].end) {
                    transcriptIndex = index;
                    break;
                }
            }
            if (transcriptIndex !== activeTranscript) {
                transcriptItems.forEach(function(item, index) {
                    var active = index === transcriptIndex;
                    item.classList.toggle('videotrack-transcript-active', active);
                    var button = item.querySelector('button');
                    if (button) {
                        button.setAttribute('aria-current', active ? 'true' : 'false');
                    }
                });
                activeTranscript = transcriptIndex;
                var activeItem = transcriptItems[transcriptIndex];
                if (activeItem && !activeItem.hidden && transcriptContent) {
                    var panelRect = transcriptContent.getBoundingClientRect();
                    var itemRect = activeItem.getBoundingClientRect();
                    var now = Date.now();
                    if ((itemRect.top < panelRect.top || itemRect.bottom > panelRect.bottom) && now - lastScrollAt > 1000) {
                        activeItem.scrollIntoView({block: 'nearest'});
                        lastScrollAt = now;
                    }
                }
            }

            var chapterIndex = -1;
            for (var chapter = chapterCues.length - 1; chapter >= 0; chapter--) {
                if (time >= chapterCues[chapter].start) {
                    chapterIndex = chapter;
                    break;
                }
            }
            if (chapterIndex !== activeChapter) {
                chapterButtons.forEach(function(button, index) {
                    var active = index === chapterIndex;
                    button.classList.toggle('videotrack-chapter-active', active);
                    button.setAttribute('aria-current', active ? 'true' : 'false');
                });
                activeChapter = chapterIndex;
            }
        }

        /**
         * Poll the provider time without overlapping asynchronous Vimeo calls.
         */
        function poll() {
            if (destroyed || pollPending || typeof options.getCurrentTime !== 'function') {
                return;
            }
            pollPending = true;
            Promise.resolve(options.getCurrentTime()).then(function(currentTime) {
                update(currentTime);
            }).catch(function(error) {
                Debug.log('timedtexttimefailed', {message: error});
            }).then(function() {
                pollPending = false;
            });
        }

        /**
         * Destroy listeners and polling.
         */
        function destroy() {
            destroyed = true;
            if (pollTimer) {
                window.clearInterval(pollTimer);
                pollTimer = null;
            }
            window.removeEventListener('pagehide', destroy);
            window.removeEventListener('beforeunload', destroy);
        }

        if (config.showtranscript && transcriptPanel && transcriptContent && transcriptTracks.length) {
            buildTranscriptControls();
            loadTranscript(preferredTrackIndex());
        }
        if (config.showchapters && chaptersContainer && config.chapterurl) {
            loadChapters();
        }
        if ((config.showtranscript || config.showchapters) && typeof options.getCurrentTime === 'function') {
            pollTimer = window.setInterval(poll, POLL_INTERVAL);
            poll();
        }
        window.addEventListener('pagehide', destroy, {once: true});
        window.addEventListener('beforeunload', destroy, {once: true});
        return {destroy: destroy, update: update};
    }

    return {
        create: create,
        parseVtt: parseVtt
    };
});
