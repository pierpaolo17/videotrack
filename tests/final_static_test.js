#!/usr/bin/env node
/*
 * Final static checks for mod_videotrack 1.3.
 *
 * This is the final Moodle-independent gate before the stable release decision.
 * It verifies that rc1/rc2/rc3 evidence, documentation and test harnesses are
 * present and that version metadata has moved to the final-check checkpoint.
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
        'docs/FINAL-CHECKS-1.3.md',
        'docs/STABLE-RELEASE-1.3.md',
        'docs/MAINTENANCE-1.3.md',
        'docs/POST-RELEASE-1.3.md',
        'tests/smoke_amd.js',
        'tests/tracker_segment_test.js',
        'tests/adapter_test.js',
        'tests/backup_restore_static_test.js',
        'tests/privacy_static_test.js',
        'tests/deprecation_static_test.js',
        'tests/performance_static_test.js',
        'tests/release_candidate_static_test.js',
        'tests/rc_freeze_static_test.js',
        'tests/rc2_freeze_static_test.js',
        'tests/rc3_freeze_static_test.js',
        'tests/stable_release_static_test.js',
        'tests/maintenance_static_test.js',
        'tests/postrelease_static_test.js'
    ].forEach(assertFile);

    assertContains('version.php', /\$plugin->version\s*=\s*202605250(?:79|80|81|82|83|84|85);/, 'final-check plugin version');
    assertContains('version.php', /\$plugin->release\s*=\s*'(?:1\.3\.79|1\.3\.80|1\.3\.81|1\.3\.82|1\.3\.83|1\.3\.84|1\.3\.85)'/, 'final-check release marker');
    assertContains('version.php', /\$plugin->maturity\s*=\s*MATURITY_(?:RC|STABLE)/, 'release-candidate maturity before stable tag');

    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /1\.3\.76-rc1/, 'rc1 checkpoint history');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /1\.3\.77-rc2/, 'rc2 checkpoint history');
    assertContains('docs/RELEASE-CANDIDATE-1.3.md', /1\.3\.78-rc3/, 'rc3 checkpoint history');
    assertContains('docs/FINAL-CHECKS-1.3.md', /Manual runtime checks still required/, 'manual runtime caveat');
    assertContains('docs/FINAL-CHECKS-1.3.md', /1\.3\.80/, 'stable checkpoint');
    assertContains('docs/STABLE-RELEASE-1.3.md', /MATURITY_STABLE/, 'stable maturity marker');
    assertContains('docs/MAINTENANCE-1.3.md', /MATURITY_STABLE/, 'maintenance maturity marker');
    assertContains('docs/POST-RELEASE-1.3.md', /MATURITY_STABLE/, 'post-release maturity marker');

    console.log('Final static checks passed.');
}

main();
