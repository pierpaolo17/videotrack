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

assert(/\$plugin->release\s*=\s*'1\.4\.\d+';/.test(version), 'version.php must declare a stable 1.4 release');
assert(/\$plugin->maturity\s*=\s*MATURITY_STABLE;/.test(version), 'post-release packages must remain stable');
assert(exists('docs/funzionalita.md'), 'functional docs must exist');
assert(exists('docs/struttura_tecnica.md'), 'technical docs must exist');
assert(technicalDocs.includes('git archive') || technicalDocs.includes('Moodle'), 'technical docs must distinguish repository/release maintenance scope');
assert(technicalDocs.includes('amd/build'), 'technical docs must document committed AMD build files');
assert(exists('tests/maintenance_static_test.js'), 'maintenance static test must exist');
assert(exists('tests/postrelease_static_test.js'), 'post-release static test must exist');

console.log('postrelease_static_test.js: ok');
