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
assert(/\$plugin->maturity\s*=\s*MATURITY_STABLE;/.test(version), 'maintenance release must remain MATURITY_STABLE');
[
    'docs/funzionalita.md',
    'docs/struttura_tecnica.md',
    'tests/smoke_amd.js',
    'tests/tracker_segment_test.js',
    'tests/adapter_test.js',
    'tests/backup_restore_static_test.js',
    'tests/privacy_static_test.js',
    'tests/maintenance_static_test.js',
    'tests/postrelease_static_test.js',
].forEach((relativePath) => {
    assert(exists(relativePath), `${relativePath} must exist in the repository test package`);
});
assert(technicalDocs.includes('classes/privacy/provider.php'), 'technical docs must describe the privacy provider');
assert(technicalDocs.includes('backup/moodle2'), 'technical docs must describe backup/restore implementation');

const srcCore = path.join(root, 'amd', 'src', 'core');
const buildCore = path.join(root, 'amd', 'build', 'core');
if (fs.existsSync(srcCore)) {
    fs.readdirSync(srcCore)
        .filter((name) => name.endsWith('.js'))
        .forEach((name) => {
            const built = path.join(buildCore, name.replace(/\.js$/, '.min.js'));
            assert(fs.existsSync(built), `missing committed AMD build for core/${name}`);
        });
}

console.log('maintenance_static_test.js: ok');
