#!/usr/bin/env node
/* Real form AMD with deterministic DOM, SDK, modal and clock doubles.
 * Run: node tests/form_duration_test.js [--build]
 * No live provider, Moodle database or browser acceptance is claimed here.
 */
const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');
const root = process.env.VT_DURATION_PLUGIN_ROOT || path.resolve(__dirname, '..');
const built = process.argv.includes('--build');
const flush = async() => { for (let i = 0; i < 6; i++) await new Promise(setImmediate); };

function harness(provider = 'youtube', manual = '180', seconds = 120) {
    const nodes = new Map();
    const timers = new Map();
    const intervals = new Map();
    let sequence = 0;
    const h = {seconds, ready: true, failure: false, requests: [], notices: [], saved: [], validations: 0,
        rejectMoodle: false, destroys: 0, cancelled: 0};
    class Evt {
        constructor(type, data = {}) { Object.assign(this, data); this.type = type; this.defaultPrevented = false; }
        preventDefault() { this.defaultPrevented = true; }
        stopImmediatePropagation() { this.stopped = true; }
    }
    class Element {
        constructor(tag = 'div') {
            this.tag = tag; this.value = ''; this.style = {}; this.attrs = {}; this.children = [];
            this.listeners = {}; this.classList = {toggle() {}}; this.textContent = '';
        }
        set id(value) { this._id = value; nodes.set(value, this); }
        get id() { return this._id; }
        setAttribute(name, value) { this.attrs[name] = value; }
        removeAttribute(name) { delete this.attrs[name]; if (name === 'src') this.src = ''; }
        addEventListener(type, fn, options) {
            (this.listeners[type] ||= []).push({fn, capture: options === true, once: options && options.once});
        }
        dispatchEvent(event) {
            event.target = this;
            const handlers = (this.listeners[event.type] || []).slice().sort((a, b) => Number(b.capture) - Number(a.capture));
            for (const handler of handlers) {
                if (event.stopped) break;
                handler.fn(event);
                if (handler.once) this.listeners[event.type] = this.listeners[event.type].filter(x => x !== handler);
            }
            return !event.defaultPrevented;
        }
        appendChild(child) { this.children.push(child); child.parentNode = this; }
        removeChild(child) { this.children = this.children.filter(x => x !== child); child.parentNode = null; }
        querySelector() { return this.link || null; }
        focus() { h.focused = this; }
        load() {
            if (this.src) {
                h.requests.push({provider: 'upload', input: this.src});
                Promise.resolve().then(() => {
                    this.duration = h.ready ? h.seconds : 0;
                    this.dispatchEvent(new Evt(h.failure ? 'error' : 'loadedmetadata'));
                });
            }
        }
    }
    const document = {getElementById: id => nodes.get(id) || null, querySelector: () => null,
        createElement: tag => new Element(tag), body: new Element('body'), head: new Element('head')};
    const window = {location: {href: 'https://moodle.invalid/course/modedit.php?update=47', origin: 'https://moodle.invalid'},
        setTimeout: (fn, delay) => { timers.set(++sequence, {fn, delay}); return sequence; },
        clearTimeout: id => timers.delete(id),
        setInterval: fn => { intervals.set(++sequence, fn); return sequence; },
        clearInterval: id => intervals.delete(id)};
    window.YT = {Player: function(id, options) {
        h.requests.push({provider: 'youtube', input: options.videoId});
        this.getDuration = () => h.ready ? h.seconds : 0;
        this.destroy = () => { h.destroys++; };
        this.mute = this.playVideo = () => {};
        Promise.resolve().then(() => options.events[h.failure ? 'onError' : 'onReady']({target: this}));
    }};
    window.Vimeo = {Player: function(iframe) {
        h.requests.push({provider: 'vimeo', input: iframe.src});
        this.getDuration = () => h.failure ? Promise.reject(new Error('unavailable')) : Promise.resolve(h.seconds);
        this.destroy = () => { h.destroys++; return Promise.resolve(); };
    }};
    const form = new Element('form');
    const fields = {};
    for (const [key, value] of Object.entries({source: provider, youtube: 'https://www.youtube.com/watch?v=VTForm00001',
        vimeo: 'https://vimeo.com/123456789/abcdef123', file: '137', duration: manual, probe: '', note: '', human: ''})) {
        const element = fields[key] = new Element('input');
        element.id = 'id_' + key; element.value = value; element.form = form;
    }
    const fieldset = new Element(); fieldset.id = fields.file.id + '_fieldset';
    fieldset.link = {href: 'https://moodle.invalid/draftfile.php/5/user/draft/137/video.mp4'};
    const save = {name: 'submitbutton', value: 'Save and display'};
    form.requestSubmit = submitter => {
        const event = new Evt('submit', {submitter});
        if (form.dispatchEvent(event)) {
            if (submitter && submitter.name === 'cancel') h.cancelled++;
            else h.saved.push({button: submitter, duration: fields.duration.value,
                probe: fields.probe.value, source: fields.source.value});
        }
        return event;
    };
    // This represents Moodle's ordinary bubble-phase validation. The guard must preserve it.
    form.addEventListener('submit', event => {
        if (event.submitter && event.submitter.name === 'cancel') return;
        h.validations++; if (h.rejectMoodle) event.preventDefault();
    });
    const modal = () => {
        const handlers = [];
        return {getRoot: () => ({one: (event, fn) => handlers.push(fn)}), hidden: () => handlers.forEach(fn => fn())};
    };
    const notification = {
        alert: (title, message) => {
            const view = modal(); h.notices.push({kind: 'error', title, message, view}); return Promise.resolve(view);
        },
        confirm: (title, message, yes, no, accept, cancel) => {
            const view = modal(); h.notices.push({kind: 'manual', title, message, accept, cancel, view});
            return Promise.resolve(view);
        }
    };
    let module;
    const context = vm.createContext({window, document, URL, Event: Evt, console, Promise,
        define: (...args) => {
            if (typeof args[0] === 'string') args.shift();
            const stubs = {'core/log': {debug() {}}, 'core/notification': notification, 'core/modal_events': {hidden: 'hidden'}};
            module = args[1](...args[0].map(key => { assert(stubs[key], 'unknown dependency ' + key); return stubs[key]; }));
        }});
    const filename = path.join(root, 'amd', built ? 'build' : 'src', 'form', built ? 'duration.min.js' : 'duration.js');
    vm.runInContext(fs.readFileSync(filename, 'utf8'), context, {filename});
    const config = Object.fromEntries(Object.entries(fields).map(([key, value]) => [key + 'id', value.id]));
    config.maximum = 86400;
    config.messages = {idle: 'idle', detecting: 'checking', manual: 'detected __VIDEOTRACK_DURATION__',
        success: 'detected __VIDEOTRACK_DURATION__', unavailable: 'verify manually before saving',
        exceeds: 'configured __VIDEOTRACK_CONFIGURED_DURATION__ exceeds __VIDEOTRACK_DURATION__',
        human: '__VIDEOTRACK_DURATION_HUMAN__', title: 'Duration', close: 'Close', save: 'Save changes',
        cancelnotice: 'Changes will be discarded without saving.'};
    module.init(config);
    return Object.assign(h, {fields, module, form, window, timers, intervals, save,
        tick: delay => {
            for (const [id, timer] of Array.from(timers)) if (timer.delay === delay) { timers.delete(id); timer.fn(); }
        },
        poll: () => Array.from(intervals.values()).forEach(fn => fn()),
        edit: (key, value) => { fields[key].value = value; fields[key].dispatchEvent(new Evt(key === 'source' ? 'change' : 'input')); },
        submit: (submitter = save) => form.requestSubmit(submitter),
        dismiss: (notice, action = 'cancel') => {
            if (notice.kind === 'manual') notice[action]();
            notice.view.hidden();
        }});
}

async function existingPositiveChecked() {
    const h = harness(); h.tick(400); await flush();
    assert.strictEqual(h.requests.length, 1, 'existing positive settings must still query the provider');
    assert.strictEqual(h.fields.duration.value, '180', 'a copied manual duration must never be silently truncated');
    assert.strictEqual(h.fields.note.textContent, 'configured 180 exceeds 120');
    assert.strictEqual(JSON.parse(h.fields.probe.value).seconds, 120);
    h.submit(); await flush();
    assert.strictEqual(h.requests.length, 2, 'saving must re-query instead of accepting the earlier result');
    assert.strictEqual(h.saved.length, 0); assert.strictEqual(h.notices[0].kind, 'error');
    h.dismiss(h.notices[0]); assert.strictEqual(h.focused, h.fields.duration);
}
async function everySaveAndMoodleValidation() {
    const h = harness('youtube', '60', 120); h.rejectMoodle = true;
    h.submit(); await flush();
    assert.strictEqual(h.validations, 1, 'ordinary Moodle validation must run after the asynchronous check');
    assert.strictEqual(h.saved.length, 0, 'requestSubmit must preserve a Moodle validation failure');
    h.seconds = 40; h.rejectMoodle = false;
    // Represents a later save after changing only a reaction/bookmark option, with the same source and duration.
    h.submit({name: 'submitbutton2', value: 'Save and return to course'}); await flush();
    assert.strictEqual(h.requests.length, 2); assert.strictEqual(h.saved.length, 0);
    assert.strictEqual(h.fields.duration.value, '60');
    assert.strictEqual(h.notices[0].message, 'configured 60 exceeds 40');
}
async function duplicatedActivityAndShorterManual() {
    const h = harness('youtube', '180', 60);
    h.edit('youtube', 'https://youtu.be/VTForm00002');
    h.submit(); await flush();
    assert.strictEqual(h.requests[0].input, 'VTForm00002'); assert.strictEqual(h.saved.length, 0);
    h.dismiss(h.notices[0]); h.edit('duration', '50');
    h.submit(); await flush();
    assert.strictEqual(h.saved.length, 1); assert.strictEqual(h.saved[0].duration, '50');
    assert.strictEqual(h.saved[0].button, h.save); assert.strictEqual(h.requests.length, 2);
    assert.strictEqual(h.validations, 1); assert.strictEqual(h.form.attrs['aria-busy'], undefined);
}
async function allProvidersAndEquality() {
    for (const provider of ['youtube', 'vimeo', 'upload']) {
        const h = harness(provider, '90.001', 90);
        h.submit(); await flush(); assert.strictEqual(h.saved.length, 0, provider);
        assert.strictEqual(h.notices[0].kind, 'error', provider); h.dismiss(h.notices[0]);
        h.edit('duration', '90'); h.submit(); await flush();
        assert.strictEqual(h.saved.length, 1, provider); assert.strictEqual(h.saved[0].duration, '90');
        const probe = JSON.parse(h.saved[0].probe);
        assert.strictEqual(probe.source, provider); assert.strictEqual(probe.seconds, 90);
        if (provider === 'vimeo') assert(h.requests[0].input.includes('h=abcdef123'));
        if (provider === 'upload') assert.strictEqual(probe.input, '137');
    }
}
async function unavailableManualAndRetry() {
    for (const provider of ['youtube', 'vimeo', 'upload']) {
        const h = harness(provider, '180', 120); h.failure = true;
        h.submit(); await flush();
        assert.strictEqual(h.notices[0].kind, 'manual', provider);
        assert.strictEqual(h.fields.duration.value, '180'); assert.strictEqual(h.fields.probe.value, '');
        assert.strictEqual(h.saved.length, 0); h.dismiss(h.notices[0], 'accept'); await flush();
        assert.strictEqual(h.saved.length, 1); assert.strictEqual(h.saved[0].duration, '180');
        assert.strictEqual(h.saved[0].probe, '');
        h.failure = false; h.submit(); await flush();
        assert.strictEqual(h.requests.length, 2); assert.strictEqual(h.saved.length, 1);
        assert.strictEqual(h.notices[1].kind, 'error', 'recovery must not reuse an outage result');
    }
}
async function pendingDoubleSaveAndStaleSource() {
    const h = harness(); h.ready = false;
    h.submit(); h.submit(); await flush();
    assert.strictEqual(h.requests.length, 1, 'double clicking must not create duplicate requests or submissions');
    h.edit('youtube', 'https://youtu.be/VTForm00002'); h.ready = true; h.poll(); await flush();
    assert.strictEqual(h.saved.length, 0); assert.strictEqual(h.notices.length, 0);
    assert.strictEqual(h.fields.probe.value, '', 'old source responses must never become form metadata');
    h.edit('duration', '100'); h.submit(); await flush();
    assert.strictEqual(h.saved.length, 1); assert.strictEqual(h.requests[1].input, 'VTForm00002');
}
async function pendingManualEditAndTimeout() {
    const h = harness('youtube', '180', 60); h.ready = false;
    h.submit(); await flush(); h.edit('duration', '50'); h.ready = true; h.poll(); await flush();
    assert.strictEqual(h.saved.length, 1); assert.strictEqual(h.saved[0].duration, '50');
    const timeout = harness(); timeout.ready = false;
    timeout.submit(); await flush(); timeout.tick(20000); await flush();
    assert.strictEqual(timeout.notices[0].kind, 'manual');
    assert.strictEqual(timeout.intervals.size, 0, 'timed-out player polling must be cleaned up');
    assert.strictEqual(timeout.destroys, 1); assert.strictEqual(timeout.fields.duration.value, '180');
    timeout.dismiss(timeout.notices[0]); assert.strictEqual(timeout.saved.length, 0);
}
async function cancelChecksAndRepeatEnterZero() {
    const h = harness('youtube', '0', 60);
    h.submit({name: 'reaction_add_fields'}); await flush();
    assert.strictEqual(h.requests.length, 0, 'repeat-field actions must remain immediate');
    h.saved.length = 0; h.validations = 0;
    h.submit(null); await flush();
    assert.strictEqual(h.saved.length, 1, 'Enter submission must pass through normal validation');
    assert.strictEqual(h.saved[0].duration, '0', 'explicit zero on save must not be overwritten');
    assert.strictEqual(h.validations, 1);
    for (const kind of ['valid', 'excessive', 'unavailable']) {
        const c = harness('youtube', kind === 'valid' ? '50' : '180', 60);
        c.failure = kind === 'unavailable'; c.rejectMoodle = true;
        c.submit({name: 'cancel'}); await flush();
        assert.strictEqual(c.requests.length, 1, 'Cancel must also perform a current duration check');
        assert.strictEqual(c.saved.length, 0, 'Cancel must never save activity settings');
        if (kind !== 'valid') {
            assert.strictEqual(c.cancelled, 0);
            assert(c.notices[0].message.includes('Changes will be discarded'));
            c.dismiss(c.notices[0]); await flush();
        }
        assert.strictEqual(c.cancelled, 1, 'Cancel must remain available after any duration warning');
        assert.strictEqual(c.saved.length, 0);
    }
}
async function unavailableDialogCannotApproveChangedForm() {
    const h = harness(); h.failure = true; h.submit(); await flush();
    h.edit('duration', '300'); h.dismiss(h.notices[0], 'accept'); await flush();
    assert.strictEqual(h.saved.length, 0, 'a manual-save decision must not apply to changed inputs');
    h.failure = false; h.submit(); await flush(); assert.strictEqual(h.notices[1].kind, 'error');
}
async function precisionAndInitialSuggestion() {
    const h = harness('youtube', '0', 60.125); h.tick(400); await flush();
    assert.strictEqual(h.fields.duration.value, '60.125');
    h.edit('duration', '40'); h.edit('youtube', 'https://youtu.be/VTForm00002'); h.tick(700); await flush();
    assert.strictEqual(h.fields.duration.value, '40', 'a teacher edit must survive later automatic suggestions');
    for (const [configured, detected, expected] of [[60, 60, false], [59, 60, false], [60.001, 60, true],
        [60.0001, 60.0002, false], [60, 0, false], [0, 60, false], [60, Infinity, false]]) {
        assert.strictEqual(h.module.exceedsDuration(configured, detected, 86400), expected);
    }
}

(async() => {
    let failures = 0;
    for (const test of [existingPositiveChecked, everySaveAndMoodleValidation, duplicatedActivityAndShorterManual,
        allProvidersAndEquality, unavailableManualAndRetry, pendingDoubleSaveAndStaleSource,
        pendingManualEditAndTimeout, cancelChecksAndRepeatEnterZero, unavailableDialogCannotApproveChangedForm,
        precisionAndInitialSuggestion]) {
        try { await test(); console.log('PASS ' + test.name + (built ? ' (build)' : ' (source)')); }
        catch (error) { failures++; console.error('FAIL ' + test.name + ': ' + error.stack); }
    }
    process.exitCode = failures ? 1 : 0;
})();
