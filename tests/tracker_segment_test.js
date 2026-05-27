#!/usr/bin/env node
/*
 * Pure segment/tracker checks for mod_videotrack.
 *
 * These tests intentionally avoid Moodle, browsers and third-party packages so
 * they can run in a clean plugin checkout during release preparation:
 *
 *     node tests/tracker_segment_test.js
 */

const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert');

const pluginRoot = path.resolve(__dirname, '..');
const srcRoot = path.join(pluginRoot, 'amd', 'src');
const registry = new Map();

function assertJsonEqual(actual, expected) {
    assert.strictEqual(JSON.stringify(actual), JSON.stringify(expected));
}

function modulePath(moduleName) {
    if (!moduleName.startsWith('mod_videotrack/')) {
        throw new Error(`Unsupported dependency ${moduleName}`);
    }
    return path.join(srcRoot, `${moduleName.substring('mod_videotrack/'.length)}.js`);
}

function loadAmd(moduleName) {
    if (registry.has(moduleName)) {
        return registry.get(moduleName);
    }

    const filename = modulePath(moduleName);
    const source = fs.readFileSync(filename, 'utf8');
    const sandbox = {
        console,
        Promise,
        Number,
        Date,
        setTimeout,
        clearTimeout,
        setInterval,
        clearInterval,
        window: {
            setTimeout,
            clearTimeout,
            setInterval,
            clearInterval
        },
        define(dependencies, factory) {
            const resolved = dependencies.map(loadAmd);
            registry.set(moduleName, factory.apply(null, resolved));
        }
    };

    vm.runInNewContext(source, sandbox, {filename});

    if (!registry.has(moduleName)) {
        throw new Error(`${moduleName} did not register through define()`);
    }
    return registry.get(moduleName);
}

async function testSegmentHelpers() {
    const Segment = loadAmd('mod_videotrack/core/segment');

    assert.strictEqual(Segment.finiteSeconds(-3), 0);
    assert.strictEqual(Segment.finiteSeconds('4.5'), 4.5);
    assert.strictEqual(Segment.normaliseSaveReason('seek'), 'seek');
    assert.strictEqual(Segment.normaliseSaveReason('unexpected'), 'interaction');
    assertJsonEqual(Segment.clampSegmentTimes(-5, 123.4567, 60), {start: 0, end: 60});
    assertJsonEqual(Segment.clampSegmentTimes(7.12349, 6, 0), {start: 7.123, end: 7.123});
    assert.strictEqual(Segment.calculateInteractionEnd(10, 10, 20, 'reaction'), 10.25);
    assert.strictEqual(Segment.calculateInteractionEnd(19.9, 19.9, 20, 'note'), 20);
    assert.strictEqual(Segment.calculateInteractionEnd(10, 10, 20, 'pause'), 10);
}

async function testTrackerStateMachine() {
    const Tracker = loadAmd('mod_videotrack/core/tracker');
    const state = {segmentstart: null};
    const transitions = [];

    Tracker.on(state, 'state:change', (payload) => transitions.push(`${payload.from}->${payload.to}`));

    assert.strictEqual(Tracker.getTrackerState(state), Tracker.STATES.IDLE);
    assert.strictEqual(Tracker.markPlaying(state, {reason: 'test'}), true);
    assert.strictEqual(state.playing, true);
    assert.strictEqual(Tracker.markSeeking(state), true);
    assert.strictEqual(state.isSeeking, true);
    assert.strictEqual(Tracker.markPaused(state), true);
    assert.strictEqual(state.playing, false);
    assert.strictEqual(state.isSeeking, false);
    assert.strictEqual(Tracker.markIdle(state), false, 'paused -> idle is intentionally blocked');
    assert.strictEqual(Tracker.markEnded(state), true);
    assert.strictEqual(state.ended, true);
    assert.strictEqual(Tracker.markDestroyed(state), true);
    assert.strictEqual(Tracker.markPlaying(state), false, 'destroyed state must not restart');
    assert.ok(transitions.includes('idle->playing'));
    assert.ok(transitions.includes('playing->seeking'));
}

async function testTrackerSegmentsAndHeartbeat() {
    const Tracker = loadAmd('mod_videotrack/core/tracker');
    const state = {segmentstart: null};
    const saved = [];

    Tracker.openSegment(state, 5, 100, 1.25);
    assert.strictEqual(state.segmentstart, 5);
    assert.strictEqual(state.playbackrate, 1.25);
    assert.strictEqual(Tracker.shouldSaveHeartbeat(state, 30, 129), false);
    assert.strictEqual(Tracker.shouldSaveHeartbeat(state, 30, 130), true);

    const heartbeatSaved = await Tracker.saveHeartbeatIfDue(
        state,
        30,
        () => Promise.resolve(42),
        (start, end, reason) => {
            saved.push({start, end, reason});
            return Promise.resolve();
        },
        130
    );

    assert.strictEqual(heartbeatSaved, true);
    assertJsonEqual(saved, [{start: 5, end: 42, reason: 'heartbeat'}]);
    assert.strictEqual(state.segmentstart, 42);
    assert.strictEqual(state.heartbeatPending, false);

    const closed = Tracker.closeSegment(state, 50);
    assertJsonEqual(closed, {start: 42, end: 50});
    assert.strictEqual(state.segmentstart, null);
    assert.strictEqual(state.playing, false);
}

async function testStaleHeartbeatGuard() {
    const Tracker = loadAmd('mod_videotrack/core/tracker');
    const state = {segmentstart: null};
    let resolver;
    let saveCalls = 0;

    Tracker.openSegment(state, 0, 0);
    const pending = Tracker.saveHeartbeatIfDue(
        state,
        1,
        () => new Promise((resolve) => { resolver = resolve; }),
        () => {
            saveCalls += 1;
            return Promise.resolve();
        },
        2
    );

    await Promise.resolve();
    Tracker.markPaused(state, {reason: 'race-test'});
    resolver(10);

    assert.strictEqual(await pending, false);
    assert.strictEqual(saveCalls, 0);
    assert.strictEqual(state.heartbeatPending, false);
}

async function testTrackerRegressionFixes() {
    const Tracker = loadAmd('mod_videotrack/core/tracker');

    const unchanged = {trackerstate: Tracker.STATES.PLAYING, playing: false, ended: true, isSeeking: true};
    assert.strictEqual(Tracker.setTrackerState(unchanged, Tracker.STATES.PLAYING), true);
    assert.strictEqual(unchanged.playing, true, 'same-state transition must resynchronise playing flag');
    assert.strictEqual(unchanged.ended, false);
    assert.strictEqual(unchanged.isSeeking, false);

    const seekState = {segmentstart: 1, playing: true};
    Tracker.markProgrammaticSeek(seekState);
    seekState.playing = false;
    assert.strictEqual(Tracker.consumeProgrammaticSeek(seekState, 5), true);
    assert.strictEqual(seekState.playing, true, 'programmatic seek should restore pre-seek playback state');

    const stoppedReplay = {playing: true, currentReplayEnd: 10};
    assert.strictEqual(Tracker.shouldStopReplay(stoppedReplay, 10), true);
    assert.strictEqual(Tracker.getTrackerState(stoppedReplay), Tracker.STATES.PAUSED);
    assert.strictEqual(stoppedReplay.ended, false, 'partial replay limit must not mark the whole video ended');

    const closing = {segmentstart: 2, playing: false};
    assertJsonEqual(Tracker.closeSegment(closing, 6), {start: 2, end: 6});
    assert.strictEqual(closing.segmentstart, null);

    const invalidClose = {segmentstart: 6, playing: true};
    assert.strictEqual(Tracker.closeSegment(invalidClose, 6), null);
    assert.strictEqual(invalidClose.segmentstart, 6, 'zero-duration close should not discard the open segment');

    assert.strictEqual(Tracker.sendUnloadBeacon({
        state: {segmentstart: 5, lasttime: 5, playing: true},
        sendSegment: () => true
    }), false, 'zero-duration unload beacon should be skipped');

    assert.strictEqual(Tracker.isPlayerAvailable(() => { throw new Error('gone'); }), false);

    const heartbeatState = {segmentstart: 1, playing: true};
    const heartbeatResult = await Tracker.runHeartbeat({
        state: heartbeatState,
        heartbeatInterval: 1,
        hasPlayer: () => { throw new Error('provider gone'); },
        getCurrentTime: () => 5,
        saveSegment: () => Promise.resolve()
    });
    assert.strictEqual(heartbeatResult, false, 'callback exceptions should not break heartbeat');
}

async function main() {
    await testSegmentHelpers();
    await testTrackerStateMachine();
    await testTrackerSegmentsAndHeartbeat();
    await testStaleHeartbeatGuard();
    await testTrackerRegressionFixes();
    console.log('Tracker/segment checks passed.');
}

main().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
