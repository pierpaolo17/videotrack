// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Checks the teacher's video duration against provider or file metadata.
 *
 * Metadata supplies an upper bound, including a fresh check before each save.
 * Shorter manual values remain valid; unavailable metadata permits a manual save.
 *
 * @module     mod_videotrack/form/duration
 * @copyright  2026 VideoTrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, promise/always-return, promise/no-nesting, promise/catch-or-return, complexity */
define(['core/log', 'core/notification', 'core/modal_events'], function(Log, Notification, ModalEvents) {
    'use strict';

    var youtubeApiPromise = null;
    var vimeoApiPromise = null;
    var probeSequence = 0;
    var DURATION_TOKEN = '__VIDEOTRACK_DURATION__';
    var HUMAN_DURATION_TOKEN = '__VIDEOTRACK_DURATION_HUMAN__';
    var CONFIGURED_DURATION_TOKEN = '__VIDEOTRACK_CONFIGURED_DURATION__';

    function normaliseDuration(value, maximum) {
        value = Number(value);
        maximum = Number(maximum) || 86400;
        if (!Number.isFinite(value) || value <= 0 || value > maximum) {
            return 0;
        }
        return Math.round(value * 1000) / 1000;
    }

    function formatDuration(value) {
        return normaliseDuration(value, 86400).toFixed(3).replace(/\.000$/, '').replace(/(\.\d*?)0+$/, '$1');
    }

    function formatHumanDuration(value) {
        value = Number(value);
        var totalSeconds = Number.isFinite(value) && value > 0 ? Math.min(86400, Math.round(value)) : 0;
        var hours = Math.floor(totalSeconds / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;
        return [hours, minutes, seconds].map(function(part) {
            return part < 10 ? '0' + part : String(part);
        }).join(':');
    }

    function setHumanDuration(output, template, value) {
        if (output) {
            output.textContent = String(template || '').split(HUMAN_DURATION_TOKEN).join(formatHumanDuration(value));
        }
    }

    function renderMessage(template, duration) {
        return String(template || '').split(DURATION_TOKEN).join(formatDuration(duration));
    }

    function exceedsDuration(configured, detected, maximum) {
        configured = normaliseDuration(configured, maximum);
        detected = normaliseDuration(detected, maximum);
        return detected > 0 && Math.round(configured * 1000) > Math.round(detected * 1000);
    }

    function setStatus(note, message, state) {
        if (!note) {
            return;
        }
        note.textContent = message || '';
        note.classList.toggle('text-danger', state === 'error');
        note.classList.toggle('text-success', state === 'success');
        note.classList.toggle('text-muted', state !== 'error' && state !== 'success');
    }

    function parseHttpsUrl(value) {
        try {
            var parsed = new URL(String(value || '').trim(), window.location.href);
            return parsed.protocol === 'https:' ? parsed : null;
        } catch (error) {
            return null;
        }
    }

    function extractYouTubeId(value) {
        var parsed = parseHttpsUrl(value);
        if (!parsed) {
            return '';
        }
        var host = parsed.hostname.toLowerCase().replace(/\.$/, '').replace(/^(?:www|m|music)\./, '');
        var candidate = '';
        if (host === 'youtu.be') {
            candidate = parsed.pathname.replace(/^\/+/, '').split('/')[0] || '';
        } else if (host === 'youtube.com' || host === 'youtube-nocookie.com') {
            var path = parsed.pathname.replace(/\/{2,}/g, '/');
            if (path === '/watch') {
                candidate = parsed.searchParams.get('v') || '';
            } else {
                var match = path.match(/^\/(?:embed|shorts|live)\/([A-Za-z0-9_-]{11})\/?$/);
                candidate = match ? match[1] : '';
            }
        }
        return /^[A-Za-z0-9_-]{11}$/.test(candidate) ? candidate : '';
    }

    function extractVimeoSource(value) {
        var parsed = parseHttpsUrl(value);
        if (!parsed) {
            return null;
        }
        var host = parsed.hostname.toLowerCase().replace(/\.$/, '').replace(/^www\./, '');
        if (host !== 'vimeo.com' && host !== 'player.vimeo.com') {
            return null;
        }
        var path = parsed.pathname.replace(/\/{2,}/g, '/');
        var patterns = [
            /^\/(?:video\/)?(\d+)(?:\/([A-Za-z0-9_-]{6,}))?\/?$/,
            /^\/(?:channels\/[^/]+|groups\/[^/]+\/videos|showcase\/\d+)\/(\d+)(?:\/([A-Za-z0-9_-]{6,}))?\/?$/
        ];
        var match = null;
        patterns.some(function(pattern) {
            match = path.match(pattern);
            return Boolean(match);
        });
        if (!match) {
            return null;
        }
        return {
            id: match[1],
            hash: parsed.searchParams.get('h') || match[2] || ''
        };
    }

    function getProbeHost() {
        var host = document.getElementById('videotrack-duration-probes');
        if (host) {
            return host;
        }
        host = document.createElement('div');
        host.id = 'videotrack-duration-probes';
        host.setAttribute('aria-hidden', 'true');
        host.style.position = 'fixed';
        host.style.left = '-10000px';
        host.style.top = '0';
        host.style.width = '220px';
        host.style.height = '220px';
        host.style.overflow = 'hidden';
        host.style.opacity = '0';
        host.style.pointerEvents = 'none';
        document.body.appendChild(host);
        return host;
    }

    function loadYouTubeApi() {
        if (window.YT && window.YT.Player) {
            return Promise.resolve(window.YT);
        }
        if (youtubeApiPromise) {
            return youtubeApiPromise;
        }
        youtubeApiPromise = new Promise(function(resolve, reject) {
            var settled = false;
            var previous = window.onYouTubeIframeAPIReady;
            var script = document.querySelector('script[src="https://www.youtube.com/iframe_api"]');
            var poll = null;
            var timeout = null;
            var handler = null;
            var cleanup = function(removeScript) {
                if (poll !== null) {
                    window.clearInterval(poll);
                }
                if (timeout !== null) {
                    window.clearTimeout(timeout);
                }
                if (window.onYouTubeIframeAPIReady === handler) {
                    window.onYouTubeIframeAPIReady = previous;
                }
                if (removeScript && script && script.parentNode && (!window.YT || !window.YT.Player)) {
                    script.parentNode.removeChild(script);
                }
            };
            var fail = function(error) {
                if (settled) {
                    return;
                }
                settled = true;
                cleanup(true);
                reject(error);
            };
            var ready = function() {
                if (settled || !window.YT || !window.YT.Player) {
                    return;
                }
                settled = true;
                cleanup(false);
                resolve(window.YT);
            };
            handler = function() {
                if (typeof previous === 'function') {
                    previous();
                }
                ready();
            };
            window.onYouTubeIframeAPIReady = handler;
            timeout = window.setTimeout(function() {
                fail(new Error('YouTube duration API timeout'));
            }, 20000);
            poll = window.setInterval(ready, 100);
            if (!script) {
                script = document.createElement('script');
                script.src = 'https://www.youtube.com/iframe_api';
                script.async = true;
                script.onerror = function() {
                    fail(new Error('YouTube duration API failed to load'));
                };
                document.head.appendChild(script);
            }
        }).catch(function(error) {
            youtubeApiPromise = null;
            throw error;
        });
        return youtubeApiPromise;
    }

    function detectYouTubeDuration(videoId) {
        return loadYouTubeApi().then(function() {
            return new Promise(function(resolve, reject) {
                var host = getProbeHost();
                var node = document.createElement('div');
                var nodeId = 'videotrack-youtube-duration-' + (++probeSequence);
                var player = null;
                var metadataPoll = null;
                var settled = false;
                node.id = nodeId;
                host.appendChild(node);
                var cleanup = function() {
                    if (metadataPoll !== null) {
                        window.clearInterval(metadataPoll);
                    }
                    if (player && typeof player.destroy === 'function') {
                        try {
                            player.destroy();
                        } catch (error) {
                            Log.debug('VideoTrack could not destroy the YouTube duration probe.');
                        }
                    }
                    if (node.parentNode) {
                        node.parentNode.removeChild(node);
                    }
                };
                var finish = function(value, error) {
                    if (settled) {
                        return;
                    }
                    settled = true;
                    window.clearTimeout(timeout);
                    cleanup();
                    if (error) {
                        reject(error);
                    } else {
                        resolve(value);
                    }
                };
                var timeout = window.setTimeout(function() {
                    finish(0, new Error('YouTube duration probe timeout'));
                }, 20000);
                player = new window.YT.Player(nodeId, {
                    width: '200',
                    height: '200',
                    videoId: videoId,
                    playerVars: {
                        autoplay: 0,
                        controls: 0,
                        playsinline: 1,
                        rel: 0,
                        origin: window.location.origin
                    },
                    events: {
                        onReady: function(event) {
                            var duration = Number(event.target.getDuration());
                            if (duration > 0) {
                                finish(duration, null);
                                return;
                            }
                            try {
                                event.target.mute();
                                event.target.playVideo();
                            } catch (error) {
                                Log.debug('VideoTrack could not start the muted YouTube duration probe.');
                            }
                        },
                        onError: function() {
                            finish(0, new Error('YouTube duration unavailable'));
                        }
                    }
                });
                if (!settled) {
                    metadataPoll = window.setInterval(function() {
                        if (!player || typeof player.getDuration !== 'function') {
                            return;
                        }
                        var duration = Number(player.getDuration());
                        if (duration > 0) {
                            finish(duration, null);
                        }
                    }, 100);
                }
            });
        });
    }

    function forgetRequireModule(loader, moduleId) {
        if (typeof loader.undef !== 'function') {
            return;
        }
        if (typeof loader.specified === 'function' && !loader.specified(moduleId)) {
            return;
        }
        loader.undef(moduleId);
    }

    function loadVimeoApi() {
        var vimeoUrl = 'https://player.vimeo.com/api/player.js';
        if (window.Vimeo && window.Vimeo.Player) {
            return Promise.resolve(window.Vimeo.Player);
        }
        if (vimeoApiPromise) {
            return vimeoApiPromise;
        }
        vimeoApiPromise = new Promise(function(resolve, reject) {
            var loader = window.requirejs || window.require;
            if (typeof loader !== 'function') {
                reject(new Error('RequireJS is unavailable for Vimeo duration API'));
                return;
            }
            loader([vimeoUrl], function(Player) {
                if (typeof Player === 'function') {
                    resolve(Player);
                    return;
                }
                if (window.Vimeo && typeof window.Vimeo.Player === 'function') {
                    resolve(window.Vimeo.Player);
                    return;
                }
                forgetRequireModule(loader, vimeoUrl);
                reject(new Error('Vimeo duration API returned no Player constructor'));
            }, function(error) {
                forgetRequireModule(loader, vimeoUrl);
                reject(error || new Error('Vimeo duration API failed to load'));
            });
        }).catch(function(error) {
            vimeoApiPromise = null;
            throw error;
        });
        return vimeoApiPromise;
    }

    function detectVimeoDuration(source) {
        return loadVimeoApi().then(function(Player) {
            var host = getProbeHost();
            var iframe = document.createElement('iframe');
            var parameters = ['dnt=1', 'autoplay=0', 'controls=0', 'playsinline=1'];
            if (source.hash) {
                parameters.unshift('h=' + encodeURIComponent(source.hash));
            }
            iframe.src = 'https://player.vimeo.com/video/' + encodeURIComponent(source.id) + '?' + parameters.join('&');
            iframe.width = '200';
            iframe.height = '112';
            iframe.tabIndex = -1;
            iframe.setAttribute('aria-hidden', 'true');
            iframe.setAttribute('title', '');
            host.appendChild(iframe);
            var player = new Player(iframe);
            var timeout = null;
            var cleanup = function() {
                window.clearTimeout(timeout);
                var result = player && typeof player.destroy === 'function' ? player.destroy() : null;
                if (result && typeof result.catch === 'function') {
                    result.catch(function() {
                        return null;
                    });
                }
                if (iframe.parentNode) {
                    iframe.parentNode.removeChild(iframe);
                }
            };
            return Promise.race([
                player.getDuration(),
                new Promise(function(resolve, reject) {
                    timeout = window.setTimeout(function() {
                        reject(new Error('Vimeo duration probe timeout'));
                    }, 20000);
                })
            ]).then(function(duration) {
                cleanup();
                if (Number(duration) <= 0) {
                    throw new Error('Vimeo duration unavailable');
                }
                return Number(duration);
            }).catch(function(error) {
                cleanup();
                throw error;
            });
        });
    }

    function findLocalFileUrl(fileFieldId) {
        var fieldset = document.getElementById(fileFieldId + '_fieldset');
        if (!fieldset) {
            return '';
        }
        var link = fieldset.querySelector('.filepicker-filename a[href], .fp-filename a[href]');
        if (!link) {
            return '';
        }
        try {
            var parsed = new URL(link.href, window.location.href);
            return parsed.origin === window.location.origin ? parsed.href : '';
        } catch (error) {
            return '';
        }
    }

    function detectLocalDuration(url) {
        return new Promise(function(resolve, reject) {
            var path = '';
            try {
                path = new URL(url, window.location.href).pathname.toLowerCase();
            } catch (error) {
                path = String(url || '').toLowerCase();
            }
            var media = /\.(?:mp3|m4a|aac)$/.test(path) ? document.createElement('audio') : document.createElement('video');
            var settled = false;
            var cleanup = function() {
                media.removeAttribute('src');
                media.load();
                if (media.parentNode) {
                    media.parentNode.removeChild(media);
                }
            };
            var finish = function(value, error) {
                if (settled) {
                    return;
                }
                settled = true;
                window.clearTimeout(timeout);
                cleanup();
                if (error) {
                    reject(error);
                } else {
                    resolve(value);
                }
            };
            var timeout = window.setTimeout(function() {
                finish(0, new Error('Local duration probe timeout'));
            }, 20000);
            media.preload = 'metadata';
            media.muted = true;
            media.tabIndex = -1;
            media.setAttribute('aria-hidden', 'true');
            media.addEventListener('loadedmetadata', function() {
                var duration = Number(media.duration);
                if (Number.isFinite(duration) && duration > 0) {
                    finish(duration, null);
                } else {
                    finish(0, new Error('Local duration unavailable'));
                }
            }, {once: true});
            media.addEventListener('error', function() {
                finish(0, new Error('Local duration unavailable'));
            }, {once: true});
            getProbeHost().appendChild(media);
            media.src = url;
            media.load();
        });
    }

    function resolveSource(elements) {
        var type = elements.source.value;
        if (type === 'youtube') {
            var youtubeId = extractYouTubeId(elements.youtube.value);
            return youtubeId ? {
                fingerprint: 'youtube:' + youtubeId,
                input: String(elements.youtube.value).trim(),
                detect: function() {
                    return detectYouTubeDuration(youtubeId);
                }
            } : null;
        }
        if (type === 'vimeo') {
            var vimeo = extractVimeoSource(elements.vimeo.value);
            return vimeo ? {
                fingerprint: 'vimeo:' + vimeo.id + ':' + vimeo.hash,
                input: String(elements.vimeo.value).trim(),
                detect: function() {
                    return detectVimeoDuration(vimeo);
                }
            } : null;
        }
        if (type === 'upload') {
            var fileUrl = findLocalFileUrl(elements.file.id);
            return fileUrl ? {
                fingerprint: 'upload:' + fileUrl,
                input: String(elements.file.value).trim(),
                detect: function() {
                    return detectLocalDuration(fileUrl);
                }
            } : null;
        }
        return null;
    }

    function getElements(config) {
        var elements = {
            source: document.getElementById(config.sourceid),
            youtube: document.getElementById(config.youtubeid),
            vimeo: document.getElementById(config.vimeoid),
            file: document.getElementById(config.fileid),
            duration: document.getElementById(config.durationid),
            probe: document.getElementById(config.probeid),
            note: document.getElementById(config.noteid),
            human: document.getElementById(config.humanid)
        };
        return elements.source && elements.youtube && elements.vimeo && elements.file && elements.duration && elements.human
            && elements.probe && elements.duration.form
            ? elements
            : null;
    }

    function install(config) {
        var elements = getElements(config);
        if (!elements) {
            return;
        }
        var form = elements.duration.form;
        var state = {
            timer: null,
            sourceRevision: 0,
            durationRevision: 0,
            programmatic: false,
            lastAutomaticValue: null,
            probeRevision: 0,
            detected: 0,
            busy: false,
            permit: false
        };

        var schedule = function(delay) {
            window.clearTimeout(state.timer);
            state.timer = window.setTimeout(run, typeof delay === 'number' ? delay : 700);
        };

        var clearProbe = function() {
            elements.probe.value = '';
            state.detected = 0;
        };

        var matches = function(source, revision) {
            var current = resolveSource(elements);
            return revision === state.sourceRevision && current
                && current.fingerprint === source.fingerprint && current.input === source.input;
        };

        var showResult = function(value) {
            var current = normaliseDuration(elements.duration.value, config.maximum);
            if (exceedsDuration(current, value, config.maximum)) {
                var message = renderMessage(config.messages.exceeds, value)
                    .split(CONFIGURED_DURATION_TOKEN).join(formatDuration(current));
                setStatus(elements.note, message, 'error');
                return message;
            }
            setStatus(elements.note, renderMessage(config.messages.manual, value), 'success');
            return '';
        };

        var detect = function(source, revision, automatic) {
            var probeRevision = ++state.probeRevision;
            var durationRevision = state.durationRevision;
            clearProbe();
            setStatus(elements.note, config.messages.detecting, 'idle');
            // Every attempt calls the selected provider. No settled duration is reused on save.
            return Promise.resolve().then(source.detect).then(function(value) {
                value = normaliseDuration(value, config.maximum);
                if (!matches(source, revision) || probeRevision !== state.probeRevision) {
                    return null;
                }
                if (!value) {
                    throw new Error('Duration metadata is unavailable');
                }
                var latestValue = normaliseDuration(elements.duration.value, config.maximum);
                var unchangedByTeacher = durationRevision === state.durationRevision;
                var previousWasAutomatic = state.lastAutomaticValue !== null
                    && Math.abs(latestValue - state.lastAutomaticValue) < 0.001;
                var replace = automatic && unchangedByTeacher && state.durationRevision === 0
                    && (latestValue <= 0 || previousWasAutomatic);
                if (replace) {
                    state.programmatic = true;
                    elements.duration.value = formatDuration(value);
                    elements.duration.dispatchEvent(new Event('input', {bubbles: true}));
                    elements.duration.dispatchEvent(new Event('change', {bubbles: true}));
                    state.programmatic = false;
                    state.lastAutomaticValue = value;
                }
                state.detected = value;
                elements.probe.value = JSON.stringify({source: elements.source.value, input: source.input, seconds: value});
                var errorMessage = showResult(value);
                return {value: value, error: errorMessage};
            }).catch(function(error) {
                if (!matches(source, revision) || probeRevision !== state.probeRevision) {
                    return null;
                }
                clearProbe();
                setStatus(elements.note, config.messages.unavailable, 'error');
                Log.debug('VideoTrack duration detection: ' + error.message);
                return {value: 0, error: ''};
            });
        };

        var release = function() {
            state.busy = false;
            form.removeAttribute('aria-busy');
        };

        var focusDuration = function() {
            elements.duration.focus();
        };

        var resume = function(submitter, source, revision, duration, cancelling) {
            release();
            if (!cancelling && (!matches(source, revision) || elements.duration.value !== duration)) {
                schedule(0);
                return;
            }
            // requestSubmit preserves Moodle validation and the original save button's name/value.
            state.permit = true;
            try {
                form.requestSubmit(submitter || undefined);
            } finally {
                state.permit = false;
            }
        };

        form.addEventListener('submit', function(event) {
            var submitter = event.submitter;
            var cancelling = submitter && submitter.name === 'cancel';
            if (submitter && ['submitbutton', 'submitbutton2', 'cancel'].indexOf(submitter.name) === -1) {
                return;
            }
            if (state.permit) {
                state.permit = false;
                return;
            }
            var source = resolveSource(elements);
            if (!source) {
                clearProbe();
                return;
            }
            event.preventDefault();
            event.stopImmediatePropagation();
            if (state.busy) {
                return;
            }
            state.busy = true;
            form.setAttribute('aria-busy', 'true');
            window.clearTimeout(state.timer);
            var revision = state.sourceRevision;
            detect(source, revision, false).then(function(result) {
                if (!result) {
                    if (cancelling) {
                        resume(submitter, source, revision, elements.duration.value, true);
                        return;
                    }
                    release();
                    schedule(0);
                    return;
                }
                var duration = elements.duration.value;
                if (result.error || (cancelling && result.value <= 0)) {
                    var message = result.error || config.messages.unavailable;
                    if (cancelling) {
                        message += ' ' + config.messages.cancelnotice;
                    }
                    return Notification.alert(config.messages.title, message, config.messages.close).then(function(modal) {
                        modal.getRoot().one(ModalEvents.hidden, function() {
                            if (cancelling) {
                                resume(submitter, source, revision, duration, true);
                                return;
                            }
                            release();
                            focusDuration();
                        });
                    });
                }
                if (result.value > 0) {
                    resume(submitter, source, revision, duration, cancelling);
                    return;
                }
                // A provider outage must not force zero or prevent an explicit manual save.
                return Notification.confirm(config.messages.title, config.messages.unavailable, config.messages.save,
                    config.messages.close, function() {
                        resume(submitter, source, revision, duration);
                    }, release).then(function(modal) {
                        modal.getRoot().one(ModalEvents.hidden, release);
                    });
            }).catch(function(error) {
                release();
                setStatus(elements.note, config.messages.unavailable, 'error');
                Log.debug('VideoTrack duration save check: ' + error.message);
                if (cancelling) {
                    resume(submitter, source, revision, elements.duration.value, true);
                    return;
                }
                focusDuration();
            });
        }, true);

        var run = function() {
            if (state.busy) {
                return;
            }
            var source = resolveSource(elements);
            if (!source) {
                clearProbe();
                setStatus(elements.note, config.messages.idle, 'idle');
                return;
            }
            detect(source, state.sourceRevision, true);
        };

        var sourceChanged = function() {
            state.sourceRevision++;
            clearProbe();
            schedule();
        };
        elements.source.addEventListener('change', sourceChanged);
        [elements.youtube, elements.vimeo].forEach(function(input) {
            input.addEventListener('input', sourceChanged);
            input.addEventListener('change', sourceChanged);
            input.addEventListener('blur', function() {
                schedule(0);
            });
        });
        elements.file.addEventListener('change', function() {
            state.sourceRevision++;
            clearProbe();
            schedule(150);
        });
        elements.duration.addEventListener('input', function() {
            setHumanDuration(elements.human, config.messages.human, elements.duration.value);
            if (!state.programmatic) {
                state.durationRevision++;
                if (state.detected > 0) {
                    showResult(state.detected);
                } else {
                    setStatus(elements.note, config.messages.idle, 'idle');
                }
            }
        });
        var fileFieldset = document.getElementById(elements.file.id + '_fieldset');
        if (fileFieldset && window.MutationObserver) {
            new MutationObserver(function() {
                if (elements.source.value === 'upload') {
                    state.sourceRevision++;
                    clearProbe();
                    schedule(150);
                }
            }).observe(fileFieldset, {childList: true, subtree: true});
        }
        setHumanDuration(elements.human, config.messages.human, elements.duration.value);
        setStatus(elements.note, config.messages.idle, 'idle');
        schedule(400);
    }

    return {
        init: function(initConfig) {
            var config = initConfig || {};
            if (config.configid) {
                var node = document.getElementById(config.configid);
                if (!node) {
                    Log.debug('VideoTrack duration configuration node was not found.');
                    return;
                }
                try {
                    config = JSON.parse(node.textContent || '{}');
                } catch (error) {
                    Log.debug('VideoTrack duration configuration could not be parsed.');
                    return;
                }
            }
            config.maximum = Number(config.maximum) || 86400;
            config.messages = config.messages || {};
            install(config);
        },
        extractYouTubeId: extractYouTubeId,
        extractVimeoSource: extractVimeoSource,
        normaliseDuration: normaliseDuration,
        formatHumanDuration: formatHumanDuration,
        exceedsDuration: exceedsDuration
    };
});
