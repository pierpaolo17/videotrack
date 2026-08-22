(function() {
    'use strict';

    function FakeVimeoPlayer(element) {
        var root = element;
        var iframe = document.createElement('iframe');
        var listeners = {};
        var currentTime = 0;
        var playbackRate = 1;
        var paused = true;
        var startedAt = 0;
        var startedFrom = 0;
        var updateTimer = null;
        var player = this;

        if (!root) {
            throw new Error('VideoTrack Vimeo fixture root not found.');
        }
        iframe.title = 'Deterministic VideoTrack Vimeo fixture';
        iframe.setAttribute('data-videotrack-behat-provider', 'vimeo');
        root.appendChild(iframe);

        function resolvedTime() {
            if (!paused) {
                return Math.min(60, startedFrom + ((Date.now() - startedAt) / 1000) * playbackRate);
            }
            return currentTime;
        }

        function emit(name, data) {
            (listeners[name] || []).slice().forEach(function(listener) {
                listener(data || {});
            });
        }

        function emitTimeUpdate() {
            currentTime = resolvedTime();
            emit('timeupdate', {
                seconds: currentTime,
                duration: 60,
                playbackRate: playbackRate
            });
            if (currentTime >= 60 && !paused) {
                paused = true;
                window.clearInterval(updateTimer);
                updateTimer = null;
                emit('ended');
            }
        }

        function startUpdates() {
            if (!updateTimer) {
                updateTimer = window.setInterval(emitTimeUpdate, 250);
            }
        }

        this.ready = function() {
            return Promise.resolve().then(function() {
                window.__videotrackBehatProvider.ready = true;
            });
        };
        this.getElement = function() {
            return Promise.resolve(iframe);
        };
        this.getDuration = function() {
            return Promise.resolve(60);
        };
        this.getCurrentTime = function() {
            currentTime = resolvedTime();
            return Promise.resolve(currentTime);
        };
        this.getPaused = function() {
            return Promise.resolve(paused);
        };
        this.getPlaybackRate = function() {
            return Promise.resolve(playbackRate);
        };
        this.setPlaybackRate = function(rate) {
            currentTime = resolvedTime();
            playbackRate = Number(rate) || 1;
            if (!paused) {
                startedFrom = currentTime;
                startedAt = Date.now();
            }
            emit('playbackratechange', {playbackRate: playbackRate});
            return Promise.resolve(playbackRate);
        };
        this.play = function() {
            if (paused) {
                startedFrom = currentTime;
                startedAt = Date.now();
                paused = false;
                startUpdates();
                window.setTimeout(function() {
                    emit('play');
                }, 0);
            }
            return Promise.resolve();
        };
        this.pause = function() {
            if (!paused) {
                currentTime = resolvedTime();
                paused = true;
                if (updateTimer) {
                    window.clearInterval(updateTimer);
                    updateTimer = null;
                }
                emitTimeUpdate();
                window.setTimeout(function() {
                    emit('pause');
                }, 0);
            }
            return Promise.resolve();
        };
        this.setCurrentTime = function(seconds) {
            var target = Math.min(60, Math.max(0, Number(seconds) || 0));
            currentTime = target;
            if (!paused) {
                startedFrom = target;
                startedAt = Date.now();
            }
            emit('seeking', {seconds: target, duration: 60});
            return Promise.resolve(target).then(function() {
                emit('seeked', {seconds: target, duration: 60});
                emitTimeUpdate();
                return target;
            });
        };
        this.enableTextTrack = function() {
            return Promise.resolve();
        };
        this.on = function(name, listener) {
            listeners[name] = listeners[name] || [];
            listeners[name].push(listener);
        };
        this.off = function(name, listener) {
            listeners[name] = (listeners[name] || []).filter(function(candidate) {
                return candidate !== listener;
            });
        };
        this.destroy = function() {
            if (updateTimer) {
                window.clearInterval(updateTimer);
                updateTimer = null;
            }
            iframe.remove();
            return Promise.resolve();
        };

        window.__videotrackBehatProvider = {
            kind: 'vimeo',
            ready: false,
            player: this,
            play: function() {
                return player.play();
            },
            pause: function() {
                return player.pause();
            },
            seek: function(seconds) {
                return player.setCurrentTime(seconds);
            },
            getTime: function() {
                currentTime = resolvedTime();
                return currentTime;
            },
            getState: function() {
                return paused ? 2 : 1;
            }
        };
    }

    window.Vimeo = {
        Player: FakeVimeoPlayer
    };
}());
