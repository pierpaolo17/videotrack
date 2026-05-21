/**
 * Shared UI helpers for mod_videotrack AMD player modules.
 *
 * @module mod_videotrack/core/ui
 */
define([], function() {

    /**
     * Enables or disables reaction buttons while keeping disabled controls focusable.
     *
     * @param {boolean} playing True when reactions are available.
     */
    function setReactionButtons(playing) {
        document.dispatchEvent(new CustomEvent('videotrack:playstate', {detail: {playing: !!playing}}));
        document.querySelectorAll('.videotrack-reaction-btn').forEach(function(button) {
            button.setAttribute('aria-disabled', playing ? 'false' : 'true');
            button.classList.toggle('videotrack-reaction-disabled', !playing);
        });
    }

    return {
        setReactionButtons: setReactionButtons
    };
});
