#!/usr/bin/env node
/* eslint-env node */

const fs = require('fs');
const path = require('path');
const assert = require('assert');

const root = path.resolve(__dirname, '..');

function read(relativePath) {
    return fs.readFileSync(path.join(root, relativePath), 'utf8');
}

function exists(relativePath) {
    return fs.existsSync(path.join(root, relativePath));
}

const version = read('version.php');
const functionalDocs = read('docs/funzionalita.md');
const technicalDocs = read('docs/struttura_tecnica.md');

assert(/\$plugin->release\s*=\s*'1\.4\.\d+';/.test(version), 'release marker must stay on the 1.4 line');
assert(/\$plugin->maturity\s*=\s*MATURITY_(?:RC|STABLE);/.test(version), 'release maturity must be explicit');
assert(/\$plugin->version\s*=\s*20\d{8,9};/.test(version), 'plugin version must be Moodle calendar-style numeric metadata');
[
    'docs/funzionalita.md',
    'docs/struttura_tecnica.md',
    'tests/smoke_amd.js',
    'tests/tracker_segment_test.js',
    'tests/adapter_test.js',
    'tests/backup_restore_static_test.js',
    'tests/privacy_static_test.js',
    'tests/review_fixes_static_test.js'
].forEach((relativePath) => {
    assert(exists(relativePath), `${relativePath} must exist`);
});
assert(functionalDocs.includes('tracciare con precisione'), 'functional docs must preserve the plugin learning analytics purpose');
assert(functionalDocs.includes('attenzione dichiarata dal comportamento'), 'functional docs must preserve the attention caveat');
assert(technicalDocs.includes('backup/moodle2'), 'technical docs must document backup/restore');
assert(technicalDocs.includes('Privacy API'), 'technical docs must document privacy API');
assert(technicalDocs.includes('amd/src'), 'technical docs must document AMD sources');

console.log('final_static_test.js: ok');
