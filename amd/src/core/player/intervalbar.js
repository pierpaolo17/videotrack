/**
 * Watched interval canvas helpers for mod_videotrack player modules.
 *
 * The shared player facade keeps backwards-compatible exports, while this
 * module owns the DOM/canvas work for the progress interval bar.
 *
 * @module mod_videotrack/core/player/intervalbar
 */
define(['mod_videotrack/core/debug'], function(Debug) {
    'use strict';

    var intervalBarCache = {json: null, duration: null, width: null, height: null};

    /**
     * Read a CSS colour used by the interval canvas.
     *
     * @param {HTMLCanvasElement} canvas Canvas element.
     * @param {string} property CSS custom property name.
     * @param {string} fallback Fallback colour.
     * @returns {string} CSS colour.
     */
    function getColor(canvas, property, fallback) {
        var value = window.getComputedStyle(canvas).getPropertyValue(property);
        return value ? value.trim() : fallback;
    }

    /**
     * Parse stored watched intervals for the interval bar.
     *
     * @param {string|Array} intervaljson JSON encoded list of [start, end] pairs.
     * @returns {Array} Parsed interval list.
     */
    function parse(intervaljson) {
        if (Array.isArray(intervaljson)) {
            return intervaljson;
        }
        if (typeof intervaljson !== 'string' || intervaljson.trim() === '') {
            return [];
        }
        try {
            var parsed = JSON.parse(intervaljson);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            Debug.log('invalidintervaljson', {message: e});
            return [];
        }
    }

    /**
     * Update accessible text for the interval bar elements.
     *
     * @param {HTMLCanvasElement} canvas Interval canvas element.
     * @param {number} pct Covered percentage.
     */
    function updateTextAlternative(canvas, pct) {
        var baseLabel = canvas.getAttribute('title') || '';
        var text = baseLabel + ' — ' + pct + '%';
        canvas.setAttribute('aria-label', text);

        var status = document.getElementById('videotrack-interval-bar-status');
        if (status) {
            status.textContent = text;
        }

        var progress = document.getElementById('videotrack-interval-progress');
        if (progress) {
            progress.max = 100;
            progress.value = pct;
            progress.textContent = pct + '%';
            progress.setAttribute('aria-valuemin', '0');
            progress.setAttribute('aria-valuemax', '100');
            progress.setAttribute('aria-valuenow', String(pct));
            progress.setAttribute('aria-valuetext', pct + '%');
        }
    }

    /**
     * Draw watched intervals on the canvas and return covered seconds.
     *
     * @param {CanvasRenderingContext2D} ctx Canvas context.
     * @param {HTMLCanvasElement} canvas Interval canvas element.
     * @param {Array} intervals Parsed interval list.
     * @param {number} duration Video duration in seconds.
     * @param {number} width Canvas backing width.
     * @param {number} height Canvas backing height.
     * @returns {number} Covered seconds.
     */
    function drawIntervals(ctx, canvas, intervals, duration, width, height) {
        var covered = 0;
        ctx.clearRect(0, 0, width, height);
        ctx.fillStyle = getColor(canvas, '--videotrack-interval-bg', '#e9ecef');
        ctx.fillRect(0, 0, width, height);
        ctx.fillStyle = getColor(canvas, '--videotrack-interval-fill', '#28a745');

        intervals.forEach(function(seg) {
            if (!Array.isArray(seg) || seg.length < 2) {
                return;
            }
            var start = Math.max(0, Number(seg[0]) || 0);
            var end = Math.min(duration, Math.max(start, Number(seg[1]) || 0));
            if (end <= start) {
                return;
            }
            var x1 = Math.round((start / duration) * width);
            var x2 = Math.round((end / duration) * width);
            ctx.fillRect(x1, 0, Math.max(2, x2 - x1), height);
            covered += Math.max(0, end - start);
        });

        return covered;
    }

    /**
     * Draw the watched-interval canvas and keep its text alternative in sync.
     *
     * @param {string|Array} intervaljson JSON encoded list of [start, end] pairs.
     * @param {number} duration Video duration in seconds.
     */
    function update(intervaljson, duration) {
        var canvas = document.getElementById('videotrack-interval-bar');
        duration = Number(duration) || 0;
        if (!canvas || duration <= 0 || document.hidden) {
            return;
        }

        var intervals = parse(intervaljson);
        var ctx = canvas.getContext('2d');
        if (!ctx) {
            return;
        }

        try {
            var dpr = window.devicePixelRatio || 1;
            var cssWidth = canvas.offsetWidth || canvas.width;
            var cssHeight = canvas.offsetHeight || canvas.height;
            var width = Math.max(1, Math.round(cssWidth * dpr));
            var height = Math.max(1, Math.round(cssHeight * dpr));

            if (intervalBarCache.json === intervaljson && intervalBarCache.duration === duration &&
                    intervalBarCache.width === width && intervalBarCache.height === height) {
                return;
            }
            intervalBarCache = {json: intervaljson, duration: duration, width: width, height: height};

            if (canvas.width !== width || canvas.height !== height) {
                canvas.width = width;
                canvas.height = height;
            }

            var covered = drawIntervals(ctx, canvas, intervals, duration, width, height);
            var pct = duration > 0 ? Math.min(100, Math.round((covered / duration) * 100)) : 0;
            updateTextAlternative(canvas, pct);
        } catch (e) {
            Debug.log('invalidintervaljson', {message: e});
        }
    }

    return {
        getColor: getColor,
        parse: parse,
        update: update
    };
});
