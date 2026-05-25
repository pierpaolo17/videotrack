#!/usr/bin/env node
/*
 * RC1 freeze checks for mod_videotrack.
 *
 * These checks are intentionally Moodle-independent. They verify that the rc1
 * package advertises release-candidate maturity and that the static release
 * gate remains documented and executable.
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
    assertContains('version.php', /\$plugin->release\s*=\s*'(?:1.3.7[678]-rc[123]|1.3.79|1.3.8[01])'/, 'release-candidate release marker');
    assertContains('version.php', /\$plugin->maturity\s*=\s*MATURITY_(?:RC|STABLE)/, 'release-candidate maturity');
    assertContains('version.php', /\$plugin->version\s*=\s*202605250(?:7[6789]|8[01]);/, 'incremented plugin version');

    [
        'docs/RELEASE-CANDIDATE-1.3.md',
        'docs/RELEASE-NOTES-1.3.md',
        'docs/UPGRADE-1.3.md',
        'tests/release_candidate_static_test.js'
    ].forEach(assertFile);

    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /Manual runtime checks still required/, 'manual runtime caveat');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /Do not introduce database changes/, 'database freeze reminder');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /privacy provider contracts/, 'privacy freeze reminder');

    console.log('RC freeze static checks passed.');
}

main();
