#!/usr/bin/env node
/* Production AMD regressions with deterministic provider/AJAX/time doubles.
 * No Moodle database, live provider or browser acceptance is claimed here.
 * Run: node tests/tracking_lifecycle_test.js [--build]
 */
const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const root = process.env.VT_TRACKING_BASELINE_ROOT || path.resolve(__dirname, '..');
const built = process.argv.includes('--build');
const pause = () => new Promise(setImmediate);
const json = value => JSON.parse(JSON.stringify(value));
function deferred() {
    let resolve;
    const promise = new Promise(r => { resolve = r; });
    return {promise, resolve};
}
function harness() {
    let now = 100000;
    let id = 0;
    const modules = new Map();
    const listeners = {};
    const timers = new Map();
    const requests = [];
    const nodes = new Map();
    const document = {
        hidden: false,
        addEventListener: (name, fn) => { (listeners[name] ||= []).push(fn); },
        removeEventListener: (name, fn) => { listeners[name] = (listeners[name] || []).filter(x => x !== fn); },
        getElementById: name => nodes.get(name) || null,
        querySelector: () => null,
        hasFocus: () => true
    };
    const window = {
        setInterval: fn => { timers.set(++id, fn); return id; },
        clearInterval: key => timers.delete(key),
        setTimeout: fn => { timers.set(++id, fn); return id; },
        clearTimeout: key => timers.delete(key),
        addEventListener: document.addEventListener,
        removeEventListener: document.removeEventListener,
        crypto: {getRandomValues: a => { a[0] = 0; return a; }},
        localStorage: {setItem() {}, getItem() { return null; }}
    };
    const Clock = class extends Date { static now() { return now; } };
    const noop = () => {};
    const log = {debug: noop, log: noop};
    const core = {parseIntervals: v => JSON.parse(v), updateIntervalBar: noop,
        configureStatus: noop, setButtons: noop};
    const stubs = {
        'core/log': log,
        'mod_videotrack/core/debug': log,
        'mod_videotrack/core/api/validator': {normaliseMethodName: v => v, validateArgs: (m, a) => a},
        'mod_videotrack/core/api/retry': {MAX_RETRIES: 0, normalizeRetryCount: () => 0},
        'mod_videotrack/core/api/error': {isBrowserOffline: () => false, classifyAjaxError: () => 'unknown'},
        'mod_videotrack/core/session': {uuid: () => String(++id).padStart(32, '0')},
        'mod_videotrack/core/api/transport': {send: (method, args) => {
            requests.push({method, args: json(args)});
            return Promise.resolve({accepted: true});
        }},
        'mod_videotrack/core/utils': {formatSeconds: n => String(n), updateCompletionInfo: noop},
        'mod_videotrack/core/ui': {},
        'mod_videotrack/core/reactions': {createState: () => ({}), setButtons: noop},
        'mod_videotrack/core/player': core,
        'mod_videotrack/core/player/forum': {},
        'mod_videotrack/core/player/timed_text': {}
    };
    const context = vm.createContext({console, Promise, Number, Date: Clock, window, document,
        navigator: {onLine: true}, YT: {PlayerState: {PLAYING: 1, PAUSED: 2, ENDED: 0}}, setTimeout, clearTimeout});
    function load(name) {
        if (modules.has(name)) return modules.get(name);
        if (stubs[name]) return stubs[name];
        const rel = name.replace('mod_videotrack/', '');
        let source = fs.readFileSync(path.join(root, 'amd', built ? 'build' : 'src', rel + (built ? '.min.js' : '.js')), 'utf8');
        // Expose private provider entrypoints only in this VM; shipped AMD stays unchanged.
        if (rel === 'player') {
            source = source.replace(/\binit\s*:\s*function\s*\(/, `
                testSetup: function(c, p) { config = c; player = p; },
                testState: state, testStart: startCurrentSegment,
                testEvent: onPlayerStateChange, testLifecycle: installGlobalListeners,
                init: function (`);
        }
        context.define = (...args) => {
            if (typeof args[0] === 'string') args.shift();
            const deps = args[0];
            const factory = args[1];
            modules.set(name, factory(...deps.map(load)));
        };
        vm.runInContext(source, context, {filename: rel + '.js'});
        return modules.get(name);
    }
    return {load, stubs, requests, timers, nodes, document, window, core,
        time: value => { now = value; }, emit: name => (listeners[name] || []).slice().forEach(fn => fn()),
        observerCount: () => /IntersectionObserver/.test(fs.readFileSync(path.join(root, 'amd/src/core/player/focus_guard.js'), 'utf8'))};
}
async function boundaryRace() {
    const h = harness();
    const t = h.load('mod_videotrack/core/tracker');
    const api = h.load('mod_videotrack/core/api');
    const state = {segmentstart: null, sessionid: 'browser1'};
    const response = deferred();
    const calls = [];
    let current = 30;
    t.openSegment(state, 0, 100, 1);
    const heartbeat = t.saveHeartbeatIfDue(state, 30, () => current, (a, b) => {
        calls.push([a, b]); return response.promise;
    }, 130);
    await pause();
    current = 31;
    const closed = t.closeAndSaveSegment(state, () => current, (a, b, reason) => {
        calls.push([a, b, reason]); return Promise.resolve({accepted: true});
    }, 'pause', true);
    assert.strictEqual(state.playing, false, 'pause detaches the segment immediately');
    current = 0; // A provider reset after the event must not change the captured end.
    const restart = api.beginPlayback({cmid: 47}, state, 31);
    await pause();
    assert.strictEqual(h.requests.length, 0, 'resume cannot overtake the final segment');
    response.resolve({accepted: true});
    assert.strictEqual(await heartbeat, true);
    assert.strictEqual(await closed, true);
    await restart;
    assert.deepStrictEqual(calls, [[0, 30], [30, 31, 'pause']], 'pending heartbeat must not be charged twice');
    assert.strictEqual(state.segmentstart, null, 'late success never reopens a paused segment');
    assert.strictEqual(h.requests[0].method, 'mod_videotrack_start_playback');
    t.openSegment(state, 31, 131, 1);
    await t.saveCurrentProgress(state, () => 40, (a, b, reason) => {
        assert.deepStrictEqual([a, b, reason], [31, 40, 'note']); return {accepted: true};
    }, 'note', true);
    assert.strictEqual(state.segmentstart, 40);
}
async function rejectionAndNewSegment() {
    const h = harness();
    const t = h.load('mod_videotrack/core/tracker');
    const state = {segmentstart: null};
    t.openSegment(state, 10, 100, 1);
    assert.strictEqual(await t.saveHeartbeatIfDue(state, 30, () => 40, () => ({accepted: false}), 130), false);
    assert.strictEqual(state.segmentstart, 10, 'rejected heartbeat cannot discard the open range');
    const response = deferred();
    const pending = t.saveHeartbeatIfDue(state, 30, () => 40, () => response.promise, 130);
    await pause();
    t.openSegment(state, 5, 131, 1); // Seek/rewatch while the old response is in flight.
    response.resolve({accepted: true});
    await pending;
    assert.strictEqual(state.segmentstart, 5, 'late heartbeat cannot overwrite a new seek/rewatch');
    h.document.hidden = true;
    assert.strictEqual(t.shouldSaveHeartbeat(state, 30, 200), false, 'hidden time cannot be credited');
}
async function tabPauseAndScroll() {
    const h = harness();
    const t = h.load('mod_videotrack/core/tracker');
    const fg = h.load('mod_videotrack/core/player/focus_guard');
    const state = {segmentstart: null, sessionid: 'browser1'};
    let current = 157;
    const saved = [];
    t.openSegment(state, 10, 100, 1);
    t.installLifecycleHandlers({state,
        closeSegment: reason => t.closeAndSaveSegment(state, () => current, (a, b, why) => {
            saved.push([a, b, why]); return {accepted: true};
        }, reason, true)});
    const guard = fg.create({state, config: {cmid: 47, integrityindicatorsenabled: true}, getCurrentTime: () => current});
    guard.setPlaying(true);
    h.emit('scroll');
    await pause();
    assert.strictEqual(state.playing, true);
    assert.strictEqual(saved.length, 0, 'scroll does not close the watched segment');
    assert.strictEqual(h.requests.length, 0, 'scroll records no integrity signal');
    assert.strictEqual(h.observerCount(), false, 'viewport changes have no observer');
    h.document.hidden = true;
    h.emit('visibilitychange');
    current = 0;
    await state._segmentSaveQueue;
    assert.deepStrictEqual(saved, [[10, 157, 'tab']], 'the full pre-hide range is retained');
    assert.strictEqual(state.playing, false);
    guard.destroy();
}
async function randomPause() {
    const h = harness();
    const t = h.load('mod_videotrack/core/tracker');
    const fg = h.load('mod_videotrack/core/player/focus_guard');
    const state = {segmentstart: null, intervaljson: '[[0,30]]'};
    const saved = [];
    t.openSegment(state, 30, 100, 1);
    const guard = fg.create({state, config: {randomfocuspauses: true, randompauseminseconds: 60,
        randompausemaxseconds: 60}, pause: () => t.closeAndSaveSegment(state, () => 90,
        (a, b, why) => { saved.push([a, b, why]); return {accepted: true}; }, 'pause', true)});
    guard.setPlaying(true);
    // Watchdog is the first timer; the random deadline is the last registered timer.
    Array.from(h.timers.values()).at(-1)();
    await state._segmentSaveQueue;
    assert.deepStrictEqual(saved, [[30, 90, 'pause']]);
    assert.strictEqual(state.intervaljson, '[[0,30]]');
    t.openSegment(state, 90, 160, 1);
    assert.strictEqual(state.intervaljson, '[[0,30]]', 'resume preserves the acknowledged earlier intervals');
    guard.destroy();
}
async function durationAndStaleResponses() {
    const h = harness();
    const adapter = h.load('mod_videotrack/core/adapter');
    const progress = h.load('mod_videotrack/core/progress');
    const state = {configuredduration: 1172, duration: 1172, intervaljson: '[[0,96]]'};
    assert.strictEqual(adapter.getDuration(state, () => 2344), 1172, 'configured duration owns the denominator');
    progress.updateProgress({accepted: true, intervaljson: '[[0,32]]'}, state, {}, h.core, {});
    assert.strictEqual(state.intervaljson, '[[0,96]]', 'out-of-order responses cannot shrink known coverage');
    h.nodes.set('videotrack-progress-percent', {});
    state.segmentstart = 96;
    state.playing = true;
    progress.updateLiveProgress(state, 96, {}, h.core, {});
    assert.strictEqual(h.nodes.get('videotrack-progress-percent').textContent, '8.19%');
}
async function youtubeHeartbeat() {
    const h = harness();
    const provider = h.load('mod_videotrack/player');
    let current = 0;
    const fakePlayer = {getCurrentTime: () => current, getDuration: () => 1172,
        getPlaybackRate: () => 1, getPlayerState: () => 1};
    provider.testSetup({cmid: 47, duration: 1172, allowseekforward: false}, fakePlayer);
    provider.testState.sessionid = 'browser1';
    provider.testState.intervaljson = '[]';
    provider.testStart();
    const poll = Array.from(h.timers.values())[0];
    for (let second = 1; second <= 440; second++) {
        current = second;
        h.time(100000 + second * 1000);
        poll();
        await pause();
    }
    provider.testEvent({data: 2});
    await provider.testState._segmentSaveQueue;
    const segments = h.requests.filter(x => x.method === 'mod_videotrack_save_segment').map(x => x.args);
    assert.ok(segments.length >= 14, 'natural no-forward-seek playback still sends heartbeats');
    assert.strictEqual(segments[0].videotimestart, 0);
    assert.strictEqual(segments.at(-1).videotimeend, 440);
    segments.forEach((s, i) => {
        assert.ok(s.videotimeend - s.videotimestart <= 31, 'no starvation-sized segment reaches the strict server ledger');
        if (i) assert.strictEqual(s.videotimestart, segments[i - 1].videotimeend, 'coverage stays contiguous');
    });
}
(async () => {
    for (const test of [boundaryRace, rejectionAndNewSegment, tabPauseAndScroll, randomPause,
        durationAndStaleResponses, youtubeHeartbeat]) {
        if (process.env.VT_TRACKING_TEST && process.env.VT_TRACKING_TEST !== test.name) continue;
        await test();
        console.log('PASS ' + test.name + (built ? ' (build)' : ' (source)'));
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
