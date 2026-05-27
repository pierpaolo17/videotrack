#!/usr/bin/env node
/*
 * RC2 freeze checks for mod_videotrack.
 *
 * These checks are Moodle-independent and keep rc2 and later RC checkpoints
 * focused on release-gate hardening. They verify that the package remains in
 * RC maturity, documents the rc2 checkpoint and keeps the static test gate
 * available.
 */

const fs = require('fs');
const path = require('path');

const pluginRoot = path.resolve(__dirname, '..');

function fail(message) {
    throw new Error(message);
}

function read(relativePath) {
    return fs.readFileSync(path.join(pluginRoot, relativePath), 'utf8');
}

function assertContains(relativePath, pattern, description) {
    if (!pattern.test(read(relativePath))) {
        fail(`${relativePath}: missing ${description}`);
    }
}

function assertFile(relativePath) {
    if (!fs.existsSync(path.join(pluginRoot, relativePath))) {
        fail(`${relativePath}: missing file`);
    }
}

function main() {
    assertContains('version.php', /\$plugin->release\s*=\s*'(?:1.3.7[78]-rc[23]|1\.3\.79|1\.3\.\d+|1\.4\.0)'/, 'rc2-or-later release marker');
    assertContains('version.php', /\$plugin->maturity\s*=\s*MATURITY_(?:RC|STABLE)/, 'release-candidate maturity');
    assertContains('version.php', /\$plugin->version\s*=\s*20\d{8,9};/, 'incremented rc2-or-later plugin version');

    [
        'docs/RELEASE-CANDIDATE-1.3.md',
        'docs/RELEASE-NOTES-1.3.md',
        'docs/UPGRADE-1.3.md',
        'tests/release_candidate_static_test.js',
        'tests/rc_freeze_static_test.js'
    ].forEach(assertFile);

    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /1\.3\.76-rc1/, 'rc1 checkpoint history');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /1\.3\.77-rc2/, 'rc2 checkpoint history');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /functional freeze/, 'functional freeze reminder');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /Manual runtime checks still required/, 'manual runtime caveat');

    console.log('RC2 freeze static checks passed.');
}

main();
