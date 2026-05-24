/**
 * Shared AJAX API helpers for mod_videotrack player modules.
 *
 * @module mod_videotrack/core/api
 */
define([
    'core/ajax',
    'core/log',
    'mod_videotrack/core/segment'
], function(Ajax, Log, Segment) {

    /**
     * Build the common save_segment payload from a concrete player state.
     *
     * @param {Object} config Player configuration.
     * @param {Object} state Mutable player state.
     * @param {*} start Segment start candidate.
     * @param {*} end Segment end candidate.
     * @param {string} reason Segment close reason.
     * @returns {Object|null} AJAX args or null when the segment is empty.
     */
    function buildSegmentArgs(config, state, start, end, reason) {
        var now = Math.floor(Date.now() / 1000);
        var times = Segment.clampSegmentTimes(start, end, state.duration || config.duration || 0);
        if (times.end <= times.start) {
            return null;
        }
        return {
            cmid: config.cmid,
            sessionid: state.sessionid,
            videotimestart: times.start,
            videotimeend: times.end,
            wallclockstart: state.wallclockstart || now,
            wallclockend: now,
            playbackrate: state.playbackrate || 1,
            endreason: Segment.normaliseSaveReason(reason),
            durationseconds: state.duration || config.duration || 0
        };
    }

    /**
     * Persist a watched segment through Moodle AJAX.
     *
     * @param {Object} config Player configuration.
     * @param {Object} state Mutable player state.
     * @param {*} start Segment start candidate.
     * @param {*} end Segment end candidate.
     * @param {string} reason Segment close reason.
     * @param {Object=} options Optional handling flags.
     * @param {boolean=} options.swallowFailures Resolve to null on AJAX failure.
     * @param {string=} options.errorMessage Debug prefix for swallowed failures.
     * @returns {Promise<Object|null>} AJAX response or null for empty/skipped segments.
     */
    function saveSegment(config, state, start, end, reason, options) {
        options = options || {};
        var args = buildSegmentArgs(config, state, start, end, reason);
        if (!args) {
            return Promise.resolve(null);
        }
        return Ajax.call([{
            methodname: 'mod_videotrack_save_segment',
            args: args
        }])[0].catch(function(error) {
            if (options.swallowFailures) {
                Log.debug((options.errorMessage || 'mod_videotrack: save segment failed') + ' - ' + error);
                return null;
            }
            return Promise.reject(error);
        });
    }

    return {
        buildSegmentArgs: buildSegmentArgs,
        saveSegment: saveSegment
    };
});
