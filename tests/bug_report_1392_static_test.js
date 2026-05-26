#!/usr/bin/env node
/* eslint-env node */

const fs = require('fs');
const path = require('path');
const assert = require('assert');

const root = path.resolve(__dirname, '..');
const read = (relativePath) => fs.readFileSync(path.join(root, relativePath), 'utf8');

const provider = read('classes/privacy/provider.php');
assert(provider.includes('privacy:intervals_unavailable'), 'privacy provider must keep invalid interval fallback');
assert(provider.includes('privacy:intervals_none'), 'privacy provider must distinguish empty interval lists from invalid JSON');
assert(!provider.includes('G1 fix'), 'privacy provider comments must be neutral for stable code');

const deleteReaction = read('classes/external/delete_reaction.php');
assert(/refresh_completion\(\$videotrack, \$cm, \(int\)\$USER->id, \$summary, \$requiredreactionids\)/.test(deleteReaction), 'delete reaction must pass prefetched required reaction IDs');

const maintenance = read('tests/maintenance_static_test.js');
const packageList = maintenance.match(/\[([\s\S]*?)\]\.forEach/) || ['', ''];
const occurrences = (packageList[1].match(/tests\/postrelease_static_test\.js/g) || []).length;
assert.strictEqual(occurrences, 1, 'maintenance package list should include postrelease_static_test.js exactly once');

console.log('bug_report_1392_static_test.js: ok');
