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
 * Suggests a video duration in the activity form from provider or file metadata.
 *
 * The detected value is only a form suggestion. It becomes authoritative after
 * the teacher saves the activity and remains editable on later form visits.
 *
 * @module     mod_videotrack/form/duration
 * @copyright  2026 VideoTrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/* eslint-disable jsdoc/require-jsdoc, jsdoc/require-param, jsdoc/require-param-type, jsdoc/check-param-names, max-len, promise/always-return, promise/no-nesting, promise/catch-or-return, complexity */
define(['core/log'], function(Log) {
    'use strict';

    var youtubeApiPromise = null;
    var vimeoApiPromise = null;
    var probeSequence = 0;
    var DURATION_TOKEN = '__VIDEOTRACK_DURATION__';

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

    function renderMessage(template, duration) {
        return String(template || '').split(DURATION_TOKEN).join(formatDuration(duration));
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
            var timeout = window.setTimeout(function() {
                if (!settled) {
                    settled = true;
                    reject(new Error('YouTube duration API timeout'));
                }
            }, 20000);
            var ready = function() {
                if (settled || !window.YT || !window.YT.Player) {
                    return;
                }
                settled = true;
                window.clearTimeout(timeout);
                resolve(window.YT);
            };
            window.onYouTubeIframeAPIReady = function() {
                if (typeof previous === 'function') {
                    previous();
                }
                ready();
            };
            var poll = window.setInterval(function() {
                if (settled) {
                    window.clearInterval(poll);
                    return;
                }
                ready();
            }, 100);
            var existing = document.querySelector('script[src="https://www.youtube.com/iframe_api"]');
            if (!existing) {
                var script = document.createElement('script');
                script.src = 'https://www.youtube.com/iframe_api';
                script.async = true;
                script.onerror = function() {
                    if (!settled) {
                        settled = true;
                        window.clearInterval(poll);
                        window.clearTimeout(timeout);
                        reject(new Error('YouTube duration API failed to load'));
                    }
                };
                document.head.appendChild(script);
            }
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
                var settled = false;
                node.id = nodeId;
                host.appendChild(node);
                var cleanup = function() {
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
                        rel: 0
                    },
                    events: {
                        onReady: function(event) {
                            var duration = Number(event.target.getDuration());
                            if (duration > 0) {
                                finish(duration, null);
                            } else {
                                finish(0, new Error('YouTube duration unavailable'));
                            }
                        },
                        onError: function() {
                            finish(0, new Error('YouTube duration unavailable'));
                        }
                    }
                });
            });
        });
    }

    function loadVimeoApi() {
        if (window.Vimeo && window.Vimeo.Player) {
            return Promise.resolve(window.Vimeo);
        }
        if (vimeoApiPromise) {
            return vimeoApiPromise;
        }
        vimeoApiPromise = new Promise(function(resolve, reject) {
            var settled = false;
            var amdDefine = window.define;
            var restoreDefine = function() {
                if (amdDefine && window.define !== amdDefine) {
                    window.define = amdDefine;
                }
            };
            var timeout = window.setTimeout(function() {
                if (!settled) {
                    settled = true;
                    restoreDefine();
                    reject(new Error('Vimeo duration API timeout'));
                }
            }, 20000);
            var ready = function() {
                if (settled || !window.Vimeo || !window.Vimeo.Player) {
                    return;
                }
                settled = true;
                restoreDefine();
                window.clearTimeout(timeout);
                resolve(window.Vimeo);
            };
            var poll = window.setInterval(function() {
                if (settled) {
                    window.clearInterval(poll);
                    return;
                }
                ready();
            }, 100);
            var existing = document.querySelector('script[src="https://player.vimeo.com/api/player.js"]');
            if (existing) {
                return;
            }
            var script = document.createElement('script');
            script.src = 'https://player.vimeo.com/api/player.js';
            script.async = true;
            if (amdDefine && amdDefine.amd) {
                window.define = undefined;
            }
            script.onload = ready;
            script.onerror = function() {
                if (!settled) {
                    settled = true;
                    restoreDefine();
                    window.clearInterval(poll);
                    window.clearTimeout(timeout);
                    reject(new Error('Vimeo duration API failed to load'));
                }
            };
            document.head.appendChild(script);
        });
        return vimeoApiPromise;
    }

    function detectVimeoDuration(source) {
        return loadVimeoApi().then(function() {
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
            var player = new window.Vimeo.Player(iframe);
            var cleanup = function() {
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
                    window.setTimeout(function() {
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
                detect: function() {
                    return detectYouTubeDuration(youtubeId);
                }
            } : null;
        }
        if (type === 'vimeo') {
            var vimeo = extractVimeoSource(elements.vimeo.value);
            return vimeo ? {
                fingerprint: 'vimeo:' + vimeo.id + ':' + vimeo.hash,
                detect: function() {
                    return detectVimeoDuration(vimeo);
                }
            } : null;
        }
        if (type === 'upload') {
            var fileUrl = findLocalFileUrl(elements.file.id);
            return fileUrl ? {
                fingerprint: 'upload:' + fileUrl,
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
            note: document.getElementById(config.noteid)
        };
        return elements.source && elements.youtube && elements.vimeo && elements.file && elements.duration
            ? elements
            : null;
    }

    function install(config) {
        var elements = getElements(config);
        if (!elements) {
            return;
        }
        var state = {
            timer: null,
            sourceRevision: 0,
            durationRevision: 0,
            programmatic: false,
            initialFingerprint: null,
            lastFingerprint: null,
            lastAutomaticValue: null,
            cache: Object.create(null)
        };

        var schedule = function(delay) {
            window.clearTimeout(state.timer);
            state.timer = window.setTimeout(run, typeof delay === 'number' ? delay : 700);
        };

        var run = function() {
            var source = resolveSource(elements);
            if (!source) {
                setStatus(elements.note, config.messages.idle, 'idle');
                return;
            }
            var previousFingerprint = state.lastFingerprint;
            var sourceChanged = previousFingerprint !== null && previousFingerprint !== source.fingerprint;
            if (state.initialFingerprint === null) {
                state.initialFingerprint = source.fingerprint;
            }
            state.lastFingerprint = source.fingerprint;
            var currentValue = normaliseDuration(elements.duration.value, config.maximum);
            if (!sourceChanged && source.fingerprint === state.initialFingerprint && currentValue > 0
                    && state.lastAutomaticValue === null) {
                setStatus(elements.note, config.messages.idle, 'idle');
                return;
            }
            var sourceRevision = state.sourceRevision;
            var durationRevision = state.durationRevision;
            var promise = state.cache[source.fingerprint];
            if (!promise) {
                promise = source.detect().then(function(value) {
                    return normaliseDuration(value, config.maximum);
                }).catch(function(error) {
                    delete state.cache[source.fingerprint];
                    throw error;
                });
                state.cache[source.fingerprint] = promise;
            }
            setStatus(elements.note, config.messages.detecting, 'idle');
            promise.then(function(value) {
                value = normaliseDuration(value, config.maximum);
                if (!value || sourceRevision !== state.sourceRevision) {
                    throw new Error('Duration response is no longer current');
                }
                var latestSource = resolveSource(elements);
                if (!latestSource || latestSource.fingerprint !== source.fingerprint) {
                    return;
                }
                var latestValue = normaliseDuration(elements.duration.value, config.maximum);
                var unchangedByTeacher = durationRevision === state.durationRevision;
                var previousWasAutomatic = state.lastAutomaticValue !== null
                    && Math.abs(latestValue - state.lastAutomaticValue) < 0.001;
                var replace = unchangedByTeacher && (latestValue <= 0 || previousWasAutomatic || sourceChanged);
                if (replace) {
                    state.programmatic = true;
                    elements.duration.value = formatDuration(value);
                    elements.duration.dispatchEvent(new Event('input', {bubbles: true}));
                    elements.duration.dispatchEvent(new Event('change', {bubbles: true}));
                    state.programmatic = false;
                    state.lastAutomaticValue = value;
                    setStatus(elements.note, renderMessage(config.messages.success, value), 'success');
                } else {
                    setStatus(elements.note, renderMessage(config.messages.manual, value), 'success');
                }
            }).catch(function(error) {
                if (sourceRevision === state.sourceRevision) {
                    setStatus(elements.note, config.messages.unavailable, 'error');
                }
                Log.debug('VideoTrack duration detection: ' + error.message);
            });
        };

        var sourceChanged = function() {
            state.sourceRevision++;
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
            schedule(150);
        });
        elements.duration.addEventListener('input', function() {
            if (!state.programmatic) {
                state.durationRevision++;
                setStatus(elements.note, config.messages.idle, 'idle');
            }
        });
        var fileFieldset = document.getElementById(elements.file.id + '_fieldset');
        if (fileFieldset && window.MutationObserver) {
            new MutationObserver(function() {
                if (elements.source.value === 'upload') {
                    state.sourceRevision++;
                    schedule(150);
                }
            }).observe(fileFieldset, {childList: true, subtree: true});
        }
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
        normaliseDuration: normaliseDuration
    };
});
