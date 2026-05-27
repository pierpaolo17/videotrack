#!/usr/bin/env node
/*
 * Pure adapter tests for mod_videotrack.
 *
 * The tests run without Moodle or browser APIs. They exercise the defensive
 * adapter helpers that player modules depend on when normalising provider
 * capabilities, media times and provider command failures.
 */

const fs = require('fs');
const path = require('path');
const vm = require('vm');
const assert = require('assert');

const pluginRoot = path.resolve(__dirname, '..');
const adapterPath = path.join(pluginRoot, 'amd', 'src', 'core', 'adapter.js');

function loadAdapter() {
    let exported = null;
    const context = {
        define: function(dependencies, factory) {
            assert.deepStrictEqual(Array.from(dependencies), [], 'adapter should not require Moodle/browser dependencies');
            exported = factory();
        },
        console: console
    };

    vm.createContext(context);
    vm.runInContext(fs.readFileSync(adapterPath, 'utf8'), context, {filename: adapterPath});

    assert(exported, 'adapter module was not exported by AMD define()');
    return exported;
}

function createLogger() {
    const messages = [];
    return {
        messages,
        debug: (message) => messages.push(message)
    };
}

function testProviderCapabilities(adapter) {
    assert.strictEqual(adapter.normaliseProviderType('YouTube'), 'youtube');
    assert.strictEqual(adapter.normaliseProviderType(' youtube '), 'youtube');
    assert.strictEqual(adapter.normaliseProviderType('HTML5'), 'html5');
    assert.strictEqual(adapter.normaliseProviderType('unknown'), '');
    assert.strictEqual(adapter.isKnownProviderType('vimeo'), true);
    assert.strictEqual(adapter.isKnownProviderType('dailymotion'), false);

    const youtube = {
        getCurrentTime: () => 12,
        getDuration: () => 90,
        playVideo: () => true,
        pauseVideo: () => true,
        seekTo: () => true,
        getPlaybackRate: () => 1,
        setPlaybackRate: () => true,
        getPlayerState: () => 0
    };

    assert.strictEqual(adapter.canCurrentTime(youtube, 'youtube'), true);
    assert.strictEqual(adapter.canDuration(youtube, 'youtube'), true);
    assert.strictEqual(adapter.canPlay(youtube, 'youtube'), true);
    assert.strictEqual(adapter.canPause(youtube, 'youtube'), true);
    assert.strictEqual(adapter.canSeek(youtube, 'youtube'), true);
    assert.strictEqual(adapter.canPlaybackRate(youtube, 'youtube'), true);
    assert.strictEqual(adapter.canEnded(youtube, 'youtube'), true);

    const partialYoutube = {getCurrentTime: () => 12};
    assert.strictEqual(adapter.canCurrentTime(partialYoutube, 'youtube'), true);
    assert.strictEqual(adapter.canDuration(partialYoutube, 'youtube'), false);
    assert.strictEqual(adapter.canSeek(partialYoutube, 'youtube'), false);

    const html5 = {
        currentTime: 0,
        duration: 120,
        play: () => true,
        pause: () => true,
        playbackRate: 1,
        volume: 1,
        muted: false,
        paused: true,
        ended: false
    };
    assert.strictEqual(adapter.canCurrentTime(html5, 'html5'), true);
    assert.strictEqual(adapter.canDuration(html5, 'html5'), true);
    assert.strictEqual(adapter.canPlay(html5, 'html5'), true);
    assert.strictEqual(adapter.canPause(html5, 'html5'), true);
    assert.strictEqual(adapter.canSeek(html5, 'html5'), true);
    assert.strictEqual(adapter.canPaused(html5, 'html5'), true);
    assert.strictEqual(adapter.canEnded(html5, 'html5'), true);
    assert.strictEqual(adapter.can({}, 'html5', 'notARealCapability'), false);
    assert.strictEqual(adapter.can({}, 'html5', 'currentTime'), false);

    const caps = adapter.getCapabilities('youtube');
    caps.play.push('mutated');
    assert.deepStrictEqual(Array.from(adapter.getCapabilityMethods('youtube', 'play')), ['playVideo']);
}

function testMediaNormalisation(adapter) {
    assert.strictEqual(adapter.normaliseTime(10, 0), 10);
    assert.strictEqual(adapter.normaliseTime('-1', 7), 7);
    assert.strictEqual(adapter.normaliseTime('bad', 4), 4);
    assert.strictEqual(adapter.normaliseTime('bad', 'fallback-bad'), 0);

    assert.strictEqual(adapter.normaliseVolume(2, 0.5), 1);
    assert.strictEqual(adapter.normaliseVolume(-1, 0.5), 0);
    assert.strictEqual(adapter.normaliseVolume('bad', 0.25), 0.25);

    assert.strictEqual(adapter.resolveSkipTarget(10, 5, 30), 15);
    assert.strictEqual(adapter.resolveSkipTarget(10, -20, 30), 0);
    assert.strictEqual(adapter.resolveSkipTarget(28, 10, 30), 30);
}

function testStatefulReaders(adapter) {
    const log = createLogger();
    const state = {lasttime: 3, duration: 20, volume: 0.4, muted: false, playbackrate: 1.25, playing: true, ended: false};

    assert.strictEqual(adapter.getCurrentTime(state, () => 12, log, 'unit'), 12);
    assert.strictEqual(state.lasttime, 12);
    assert.strictEqual(adapter.getCurrentTime(state, () => { throw new Error('boom'); }, log, 'unit'), 12);

    assert.strictEqual(adapter.getDuration(state, () => 60, log, 'unit'), 60);
    assert.strictEqual(state.duration, 60);
    assert.strictEqual(adapter.getDuration(state, () => { throw new Error('boom'); }, log, 'unit'), 60);

    assert.strictEqual(adapter.getVolume(state, () => 0.75, log, 'unit'), 0.75);
    assert.strictEqual(state.volume, 0.75);
    adapter.setVolume(0, (value) => value, state, log, 'unit');
    assert.strictEqual(state.volume, 0);
    assert.strictEqual(state.muted, true);

    assert.strictEqual(adapter.isMuted(state, () => false, log, 'unit'), false);
    assert.strictEqual(state.muted, false);
    adapter.setMuted(true, (value) => value, state, log, 'unit');
    assert.strictEqual(state.muted, true);

    assert.strictEqual(adapter.getPlaybackRate(state, () => 1.5, log, 'unit'), 1.5);
    assert.strictEqual(state.playbackrate, 1.5);
    adapter.setPlaybackRate('bad', (value) => value, state, log, 'unit');
    assert.strictEqual(state.playbackrate, 1);

    assert.strictEqual(adapter.isPaused(state, () => false, log, 'unit'), false);
    assert.strictEqual(state.playing, true);
    assert.strictEqual(adapter.isPaused({playing: false}, null, log, 'unit'), true);

    assert.strictEqual(adapter.isEnded(state, () => true, log, 'unit'), true);
    assert.strictEqual(state.ended, true);
    assert.strictEqual(adapter.isEnded(state, () => 0, log, 'youtube'), true);
    assert.strictEqual(adapter.isEnded(state, () => 1, log, 'youtube'), false);
    assert.strictEqual(adapter.isEnded(state, () => { throw new Error('boom'); }, log, 'unit'), false);

    assert(log.messages.length >= 3, 'expected reader failures to be logged');
}

function testCommands(adapter) {
    let played = false;
    let paused = false;
    let seekTarget = null;
    const log = createLogger();

    assert.strictEqual(adapter.play(() => { played = true; return 'play-result'; }, log), 'play-result');
    assert.strictEqual(played, true);

    assert.strictEqual(adapter.pause(() => { paused = true; return 'pause-result'; }, log), 'pause-result');
    assert.strictEqual(paused, true);

    assert.strictEqual(adapter.seek('42', (target) => { seekTarget = target; return target; }, log), 42);
    assert.strictEqual(seekTarget, 42);

    assert.strictEqual(adapter.run(() => { throw new Error('provider failed'); }, log, 'failing command'), null);
    assert(log.messages.some((message) => message.includes('failing command')), 'expected command failure to be logged');
}

function main() {
    const adapter = loadAdapter();
    testProviderCapabilities(adapter);
    testMediaNormalisation(adapter);
    testStatefulReaders(adapter);
    testCommands(adapter);
    console.log('Adapter tests passed.');
}

main();
