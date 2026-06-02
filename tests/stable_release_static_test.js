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

assert(/\$plugin->maturity\s*=\s*MATURITY_STABLE;/.test(version), 'stable package must use MATURITY_STABLE');
assert(/\$plugin->release\s*=\s*'1\.4\.\d+';/.test(version), 'stable release marker must remain on the 1.4 line');
assert(exists('docs/funzionalita.md'), 'functional documentation must exist');
assert(exists('docs/struttura_tecnica.md'), 'technical documentation must exist');
assert(functionalDocs.includes('mod_videotrack'), 'functional documentation must describe mod_videotrack');
assert(functionalDocs.includes('Privacy API'), 'functional documentation must document privacy scope');
assert(technicalDocs.includes('AMD'), 'technical documentation must describe AMD modules');
assert(exists('tests/final_static_test.js'), 'final static test must exist');

console.log('stable_release_static_test.js: ok');
