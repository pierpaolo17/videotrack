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

assert(
    /\$plugin->release\s*=\s*'1\.3\.(?:85|86|87)';/.test(version),
    'version.php must declare release 1.3.85, 1.3.86 or 1.3.87'
);

assert(
    /\$plugin->maturity\s*=\s*MATURITY_STABLE;/.test(version),
    'maintenance release must remain MATURITY_STABLE'
);

[
    'docs/UPGRADE-1.3.md',
    'docs/RELEASE-NOTES-1.3.md',
    'docs/FINAL-CHECKS-1.3.md',
    'docs/MAINTENANCE-1.3.md',
    'docs/POST-RELEASE-1.3.md',
    'tests/smoke_amd.js',
    'tests/tracker_segment_test.js',
    'tests/adapter_test.js',
    'tests/backup_restore_static_test.js',
    'tests/privacy_static_test.js',
    'tests/maintenance_static_test.js',
    'tests/postrelease_static_test.js',
    'tests/postrelease_static_test.js'
].forEach((relativePath) => {
    assert(exists(relativePath), `${relativePath} must exist in the maintenance package`);
});

const maintenance = read('docs/MAINTENANCE-1.3.md');
assert(
    maintenance.includes('MATURITY_STABLE') && maintenance.includes('node tests/maintenance_static_test.js') && maintenance.includes('node tests/postrelease_static_test.js'),
    'maintenance notes must document stable maturity and the maintenance/post-release tests'
);

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
