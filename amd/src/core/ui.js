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



    /**
     * Safely appends reaction icon HTML using a strict tag and attribute whitelist.
     *
     * @param {HTMLElement} target Target node.
     * @param {string} iconhtml Icon HTML.
     */
    function appendIconSafe(target, iconhtml) {
        if (!target || !iconhtml) { return; }
        var allowedTags  = {'IMG': true, 'I': true, 'SPAN': true};
        var allowedAttrs = {'class': true, 'src': true, 'alt': true, 'aria-hidden': true};

        function isSafeIconSrc(value) {
            if (!value) { return false; }
            var trimmed = String(value).trim();
            var lower = trimmed.toLowerCase();
            if (lower.indexOf('javascript:') === 0 || lower.indexOf('data:') === 0 || lower.indexOf('vbscript:') === 0) {
                return false;
            }
            try {
                var url = new URL(trimmed, window.location.origin);
                if (url.origin !== window.location.origin) {
                    return false;
                }
                return url.pathname.indexOf('/pluginfile.php/') !== -1 ||
                    url.pathname.indexOf('/webservice/pluginfile.php/') !== -1;
            } catch (e) {
                return false;
            }
        }

        function sanitizeNode(node) {
            if (node.nodeType === Node.TEXT_NODE) {
                return document.createTextNode(node.textContent);
            }
            if (node.nodeType !== Node.ELEMENT_NODE || !allowedTags[node.nodeName]) {
                return null;
            }
            var el = document.createElement(node.nodeName.toLowerCase());
            Array.from(node.attributes).forEach(function(attr) {
                if (!allowedAttrs[attr.name]) {
                    return;
                }
                if (attr.name === 'src' && (node.nodeName !== 'IMG' || !isSafeIconSrc(attr.value))) {
                    return;
                }
                el.setAttribute(attr.name, attr.value);
            });
            Array.from(node.childNodes).forEach(function(child) {
                var clean = sanitizeNode(child);
                if (clean) {
                    el.appendChild(clean);
                }
            });
            return el;
        }

        var template = document.createElement('template');
        template.innerHTML = iconhtml;
        Array.from(template.content.childNodes).forEach(function(node) {
            var clean = sanitizeNode(node);
            if (clean) {
                target.appendChild(clean);
            }
        });
    }

    return {
        setReactionButtons: setReactionButtons,
        appendIconSafe: appendIconSafe
    };
});
