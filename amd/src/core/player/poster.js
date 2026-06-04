/**
 * Poster overlay helpers for shared Videotrack player UI.
 *
 * @module mod_videotrack/core/player/poster
 */
define([], function() {
    'use strict';

    /**
     * Remove a poster overlay on first playback event.
     *
     * @param {Event} e Custom playstate event.
     * @param {Object} state Player mutable state.
     * @param {Function} removePosterFn Callback that removes the poster overlay.
     */
    function onFirstPlay(e, state, removePosterFn) {
        if (e.detail && e.detail.playing && !state._posterRemoved) {
            state._posterRemoved = true;
            removePosterFn();
            document.removeEventListener('videotrack:playstate', state._posterPlayListener);
            state._posterPlayListener = null;
        }
    }

    /**
     * Remove the poster overlay with the existing fade-out transition.
     *
     * @param {HTMLElement} overlay Poster overlay.
     */
    function remove(overlay) {
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
        onFirstPlay: onFirstPlay,
        remove: remove
    };
});
