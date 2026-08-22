(function() {
    'use strict';

    var STATES = {
        UNSTARTED: -1,
        ENDED: 0,
        PLAYING: 1,
        PAUSED: 2,
        BUFFERING: 3,
        CUED: 5
    };

    function FakeYouTubePlayer(elementId, options) {
        var root = document.getElementById(elementId);
        var iframe = document.createElement('iframe');
        var listeners = {};
        var settings = options || {};
        var playerVars = settings.playerVars || {};
        var currentTime = Math.max(0, Number(playerVars.start) || 0);
        var playbackRate = 1;
        var state = STATES.PAUSED;
        var startedAt = 0;
        var startedFrom = currentTime;
        var player = this;

        if (!root) {
            throw new Error('VideoTrack YouTube fixture root not found.');
        }
        iframe.title = 'Deterministic VideoTrack YouTube fixture';
        iframe.setAttribute('data-videotrack-behat-provider', 'youtube');
        root.appendChild(iframe);

        function resolvedTime() {
            if (state === STATES.PLAYING) {
                return Math.min(60, startedFrom + ((Date.now() - startedAt) / 1000) * playbackRate);
            }
            return currentTime;
        }

        function dispatch(name, data) {
            var event = {target: player, data: data};
            var callback = settings.events && settings.events[name];
            if (typeof callback === 'function') {
                callback(event);
            }
            (listeners[name] || []).slice().forEach(function(listener) {
                listener(event);
            });
        }

        this.getDuration = function() {
            return 60;
        };
        this.getCurrentTime = function() {
            currentTime = resolvedTime();
            if (currentTime >= 60 && state === STATES.PLAYING) {
                state = STATES.ENDED;
                dispatch('onStateChange', state);
            }
            return currentTime;
        };
        this.getPlayerState = function() {
            return state;
        };
        this.playVideo = function() {
            if (state !== STATES.PLAYING) {
                startedFrom = this.getCurrentTime();
                startedAt = Date.now();
                state = STATES.PLAYING;
                dispatch('onStateChange', state);
            }
        };
        this.pauseVideo = function() {
            if (state === STATES.PLAYING) {
                currentTime = resolvedTime();
                state = STATES.PAUSED;
                dispatch('onStateChange', state);
            }
        };
        this.seekTo = function(seconds) {
            currentTime = Math.min(60, Math.max(0, Number(seconds) || 0));
            if (state === STATES.PLAYING) {
                startedFrom = currentTime;
                startedAt = Date.now();
            }
        };
        this.getPlaybackRate = function() {
            return playbackRate;
        };
        this.setPlaybackRate = function(rate) {
            currentTime = resolvedTime();
            playbackRate = Number(rate) || 1;
            if (state === STATES.PLAYING) {
                startedFrom = currentTime;
                startedAt = Date.now();
            }
            dispatch('onPlaybackRateChange', playbackRate);
        };
        this.getIframe = function() {
            return iframe;
        };
        this.addEventListener = function(name, listener) {
            listeners[name] = listeners[name] || [];
            listeners[name].push(listener);
        };
        this.removeEventListener = function(name, listener) {
            listeners[name] = (listeners[name] || []).filter(function(candidate) {
                return candidate !== listener;
            });
        };
        this.destroy = function() {
            iframe.remove();
        };

        window.__videotrackBehatProvider = {
            kind: 'youtube',
            ready: false,
            player: this,
            play: function() {
                player.playVideo();
            },
            pause: function() {
                player.pauseVideo();
            },
            seek: function(seconds) {
                player.seekTo(seconds, true);
            },
            getTime: function() {
                return player.getCurrentTime();
            },
            getState: function() {
                return player.getPlayerState();
            }
        };

        window.setTimeout(function() {
            window.__videotrackBehatProvider.ready = true;
            dispatch('onReady');
        }, 0);
    }

    window.YT = {
        Player: FakeYouTubePlayer,
        PlayerState: STATES
    };
}());
