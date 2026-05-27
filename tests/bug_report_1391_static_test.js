#!/usr/bin/env node
/* eslint-env node */

const fs = require('fs');
const path = require('path');
const assert = require('assert');

const root = path.resolve(__dirname, '..');
const read = (relativePath) => fs.readFileSync(path.join(root, relativePath), 'utf8');

const version = read('version.php');
assert(/\$plugin->release\s*=\s*'(?:1\.3\.\d+|1\.4\.\d+)';/.test(version), 'release marker must stay on the stable 1.3/1.4 line');
assert(!/1\.3\.(?:8|9)\d/.test(read('tests/stable_release_static_test.js')), 'stable release test must not hardcode 1.3.8x/9x only');
assert(!/1\.3\.(?:8|9)\d/.test(read('tests/review_fixes_static_test.js')), 'review-fixes test must not hardcode 1.3.8x/9x only');

const status = read('amd/src/core/status.js');
assert(status.includes('new WeakMap()'), 'status timers must remain scoped per container');
assert(!/videotrack-status-live-region['"]/.test(status), 'status live region must not use duplicated fixed IDs');

const session = read('amd/src/core/session.js');
assert(!session.includes('Math.random'), 'session fallback must not use Math.random');

console.log('bug_report_1391_static_test.js: ok');
