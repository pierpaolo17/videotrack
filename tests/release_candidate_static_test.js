#!/usr/bin/env node
/*
 * Release-candidate preparation checks for mod_videotrack.
 *
 * This script keeps the final pre-rc gate Moodle-independent. It verifies that
 * the accumulated 1.3 release documentation and static test harnesses are
 * present before the branch can move toward rc1.
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

function assertFile(relativePath) {
    if (!fs.existsSync(path.join(pluginRoot, relativePath))) {
        fail(`${relativePath}: missing file`);
    }
}

function assertContains(relativePath, pattern, description) {
    if (!pattern.test(read(relativePath))) {
        fail(`${relativePath}: missing ${description}`);
    }
}

function main() {
    [
        'docs/UPGRADE-1.3.md',
        'docs/RELEASE-NOTES-1.3.md',
        'docs/DEPRECATIONS-1.3.md',
        'docs/PERFORMANCE-1.3.md',
        'docs/RELEASE-CANDIDATE-1.3.md',
        'tests/smoke_amd.js',
        'tests/tracker_segment_test.js',
        'tests/adapter_test.js',
        'tests/backup_restore_static_test.js',
        'tests/privacy_static_test.js',
        'tests/deprecation_static_test.js',
        'tests/performance_static_test.js',
        'tests/rc_freeze_static_test.js',
        'tests/rc2_freeze_static_test.js',
        'tests/rc3_freeze_static_test.js'
    ].forEach(assertFile);

    assertContains('version.php', /\$plugin->release\s*=\s*'(?:1.3.7[678]-rc[123]|1.3.79|1.3.8[0-7])'/, 'release-candidate release marker');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /Manual runtime checks still required/, 'manual runtime caveat');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /1\.3\.76-rc1/, 'rc1 target');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /1\.3\.77-rc2/, 'rc2 target');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /1\.3\.78-rc3/, 'rc3 target');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /node tests\/smoke_amd\.js/, 'AMD smoke command');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /privacy export\/delete flows/, 'privacy runtime reminder');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /backup and restore/, 'backup restore runtime reminder');

    console.log('Release candidate static checks passed.');
}

main();
