#!/usr/bin/env node
/*
 * RC3 freeze checks for mod_videotrack.
 *
 * RC3 is the final planned release-candidate checkpoint before final static
 * verification. These checks keep the package functionally frozen and verify
 * that the release documentation, previous RC gates and version metadata are
 * aligned for the rc3 handoff.
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
    assertContains('version.php', /\$plugin->release\s*=\s*'(?:1.3.78-rc3|1.3.79|1.3.8[0-2])'/, 'rc3 release marker');
    assertContains('version.php', /\$plugin->maturity\s*=\s*MATURITY_(?:RC|STABLE)/, 'release-candidate maturity');
    assertContains('version.php', /\$plugin->version\s*=\s*202605250(?:7[89]|8[0-2]);/, 'incremented plugin version');

    [
        'docs/RELEASE-CANDIDATE-1.3.md',
        'docs/RELEASE-NOTES-1.3.md',
        'docs/UPGRADE-1.3.md',
        'tests/release_candidate_static_test.js',
        'tests/rc_freeze_static_test.js',
        'tests/rc2_freeze_static_test.js'
    ].forEach(assertFile);

    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /1\.3\.76-rc1/, 'rc1 checkpoint history');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /1\.3\.77-rc2/, 'rc2 checkpoint history');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /1\.3\.78-rc3/, 'rc3 checkpoint history');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /final planned release-candidate checkpoint/, 'rc3 freeze scope');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /Manual runtime checks still required/, 'manual runtime caveat');

    console.log('RC3 freeze static checks passed.');
}

main();
