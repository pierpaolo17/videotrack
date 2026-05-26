#!/usr/bin/env node
/* eslint-env node */

const fs = require('fs');
const path = require('path');
const assert = require('assert');

const root = path.resolve(__dirname, '..');
const read = (relativePath) => fs.readFileSync(path.join(root, relativePath), 'utf8');

const amdFiles = [
    'amd/src/core/adapter.js',
    'amd/src/core/api.js',
    'amd/src/core/events.js',
    'amd/src/core/status.js',
    'amd/src/core/session.js',
    'amd/src/core/state.js',
    'amd/src/core/tracker.js',
    'amd/src/core/player.js',
    'amd/src/core/progress.js',
    'amd/src/core/utils.js'
];

amdFiles.forEach((file) => {
    const source = read(file);
    assert(!/var\s+\w+\s*=\s*;/.test(source), `${file}: must not contain empty variable initialisers`);
    assert(!/function\s+\w+\s*\([^)]*\)\s*;/.test(source), `${file}: must not contain unimplemented function declarations`);
    assert(!/\|\|\s*\|\|/.test(source), `${file}: must not contain broken logical OR expressions`);
});

const api = read('amd/src/core/api.js');
assert(!api.includes('Math.random'), 'retry jitter must not use Math.random');
assert(api.includes('getRetryJitter'), 'retry jitter must use the dedicated helper');

const provider = read('classes/privacy/provider.php');
assert(provider.includes('format_interval_second'), 'privacy interval formatting must be centralised');

console.log('bug_report_1393_static_test.js: ok');
