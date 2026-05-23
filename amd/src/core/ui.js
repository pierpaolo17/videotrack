/**
 * Shared UI helpers for mod_videotrack AMD player modules.
 *
 * @module mod_videotrack/core/ui
 */
define([], function() {

    /**
     * Enables or disables reaction buttons and mirrors the state to the native
     * disabled attribute. The live-region hint still announces availability
     * changes, while disabled buttons are removed from pointer/keyboard action
     * paths consistently across browsers.
     *
     * @param {boolean} playing True when reactions are available.
     */
    function setReactionButtons(playing) {
        document.dispatchEvent(new CustomEvent('videotrack:playstate', {detail: {playing: !!playing}}));
        document.querySelectorAll('.videotrack-reaction-btn').forEach(function(button) {
            button.disabled = !playing;
            button.setAttribute('aria-disabled', playing ? 'false' : 'true');
            button.classList.toggle('videotrack-reaction-disabled', !playing);
        });
    }

    /**
     * Checks whether an icon src points to an allowed local plugin file URL.
     *
     * @param {string} value URL candidate.
     * @returns {boolean} True when the URL is safe.
     */
    function isSafeIconSrc(value) {
        if (!value) { return false; }
        var trimmed = String(value).trim();
        var lower = trimmed.toLowerCase();
        // eslint-disable-next-line no-script-url
        if (lower.indexOf('javascript:') === 0 || lower.indexOf('data:') === 0 || lower.indexOf('vbscript:') === 0) {
            return false;
        }
        try {
            var url = new URL(trimmed, window.location.origin);
            if (url.origin !== window.location.origin) {
                return false;
            }
            return url.pathname.indexOf('/pluginfile.php/') !== -1 ||
                url.pathname.indexOf('/webservice/pluginfile.php/') !== -1 ||
                url.pathname.indexOf('/theme/image.php/') !== -1;
        } catch (e) {
            return false;
        }
    }

    /**
     * Reads attributes from a single allowed icon tag without handing arbitrary
     * markup to DOMParser or innerHTML.
     *
     * @param {string} html Source icon markup.
     * @returns {Object|null} Parsed descriptor or null.
     */
    function parseSingleIcon(html) {
        var source = String(html || '').trim();
        var match = source.match(/^<(img|span|i)\b([^>]*)>(?:<\/\1>)?$/i) ||
            source.match(/^<(img)\b([^>]*)\/?\s*>$/i);
        if (!match) {
            return null;
        }
        var attrs = {};
        var attrSource = match[2] || '';
        var attrPattern = /([a-zA-Z][a-zA-Z0-9:-]*)\s*=\s*("([^"]*)"|'([^']*)')/g;
        var attr;
        while ((attr = attrPattern.exec(attrSource)) !== null) {
            attrs[attr[1].toLowerCase()] = attr[3] !== undefined ? attr[3] : attr[4];
        }
        return {tag: match[1].toLowerCase(), attrs: attrs};
    }

    /**
     * Safely appends reaction icon markup using a strict single-tag whitelist.
     * Complex or malformed HTML is rejected completely.
     *
     * @param {HTMLElement} target Target node.
     * @param {string} iconhtml Icon HTML.
     */
    function appendIconSafe(target, iconhtml) {
        if (!target || !iconhtml) { return; }
        var icon = parseSingleIcon(iconhtml);
        if (!icon) { return; }
        var el = document.createElement(icon.tag);
        ['class', 'alt', 'aria-hidden'].forEach(function(name) {
            if (Object.prototype.hasOwnProperty.call(icon.attrs, name)) {
                el.setAttribute(name, icon.attrs[name]);
            }
        });
        if (icon.tag === 'img') {
            if (!isSafeIconSrc(icon.attrs.src)) {
                return;
            }
            el.setAttribute('src', icon.attrs.src);
            if (!Object.prototype.hasOwnProperty.call(icon.attrs, 'alt')) {
                el.setAttribute('alt', '');
            }
        }
        target.appendChild(el);
    }


    return {
        setReactionButtons: setReactionButtons,
        appendIconSafe: appendIconSafe
    };
});
