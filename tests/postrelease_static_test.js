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
assert(/\$plugin->release\s*=\s*'(?:1\.3\.\d+|1\.4\.\d+)';/.test(version), 'version.php must declare a stable 1.3/1.4 release');
assert(/\$plugin->maturity\s*=\s*MATURITY_STABLE;/.test(version), 'post-release packages must remain stable');

[
    'docs/STABLE-RELEASE-1.3.md',
    'docs/MAINTENANCE-1.3.md',
    'docs/POST-RELEASE-1.3.md',
    'tests/stable_release_static_test.js',
    'tests/maintenance_static_test.js'
].forEach((relativePath) => {
    assert(exists(relativePath), `${relativePath} must exist in the post-release package`);
});

const notes = read('docs/POST-RELEASE-1.3.md');
assert(notes.includes('MATURITY_STABLE'), 'post-release notes must document stable maturity');
assert(notes.includes('node tests/postrelease_static_test.js'), 'post-release notes must document this test');
assert(notes.includes('Moodle runtime'), 'post-release notes must distinguish static checks from runtime Moodle checks');

console.log('postrelease_static_test.js: ok');
