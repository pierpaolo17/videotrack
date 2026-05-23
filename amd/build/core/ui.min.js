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
     * Checks whether a Font Awesome class list matches the subset emitted by the
     * server-side form validation. This avoids parsing arbitrary HTML while still
     * allowing teacher-configured icon classes.
     *
     * @param {string} value Candidate class list.
     * @returns {boolean} True when the class list is safe.
     */
    function isSafeIconClass(value) {
        if (!value) {
            return false;
        }
        var parts = String(value).trim().split(/\s+/);
        if (parts.length === 0 || parts.length > 4) {
            return false;
        }
        return parts.every(function(part) {
            return /^(fa|fas|far|fab|fa-[a-z0-9-]+)$/.test(part);
        });
    }

    /**
     * Safely appends a reaction icon from structured data attributes.
     *
     * No HTML string is parsed here: the server exposes the selected icon type,
     * local pluginfile URL, Font Awesome class list, or plain-text fallback as
     * separate data attributes. This removes the previous regex-based HTML parser
     * and avoids accepting malformed/custom markup at runtime.
     *
     * @param {HTMLElement} target Target node.
     * @param {Object|string} icon Icon descriptor or legacy plain text fallback.
     */
    function appendIconSafe(target, icon) {
        if (!target || !icon) {
            return;
        }
        if (typeof icon === 'string') {
            var legacy = String(icon).trim();
            if (legacy === '') {
                return;
            }
            target.appendChild(document.createTextNode(legacy));
            return;
        }

        var type = String(icon.type || 'emoji');
        if (type === 'file' && isSafeIconSrc(icon.src)) {
            var img = document.createElement('img');
            img.setAttribute('src', String(icon.src));
            img.setAttribute('alt', '');
            img.setAttribute('aria-hidden', 'true');
            img.className = 'videotrack-reaction-icon-file';
            target.appendChild(img);
            return;
        }

        if (type === 'fa' && isSafeIconClass(icon.iconclass)) {
            var fa = document.createElement('i');
            fa.className = String(icon.iconclass).trim();
            fa.setAttribute('aria-hidden', 'true');
            target.appendChild(fa);
            return;
        }

        var text = String(icon.text || '').trim();
        if (text !== '') {
            var span = document.createElement('span');
            span.className = 'videotrack-reaction-icon-text';
            span.textContent = text;
            target.appendChild(span);
        }
    }


    return {
        setReactionButtons: setReactionButtons,
        appendIconSafe: appendIconSafe
    };
});
