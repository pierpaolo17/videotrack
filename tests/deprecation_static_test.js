#!/usr/bin/env node
/*
 * Static deprecation/compatibility checks for mod_videotrack.
 *
 * The goal is intentionally conservative: keep historical entry points stable
 * while preventing new shared logic from drifting back into provider modules.
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
    const content = read(relativePath);
    if (!pattern.test(content)) {
        fail(`${relativePath}: missing ${description}`);
    }
}

function assertNotContains(relativePath, pattern, description) {
    const content = read(relativePath);
    if (pattern.test(content)) {
        fail(`${relativePath}: unexpected ${description}`);
    }
}

function main() {
    assertFile('docs/DEPRECATIONS-1.3.md');
    assertContains('docs/DEPRECATIONS-1.3.md', /mod_videotrack\/player/, 'historical player entry point note');
    assertContains('docs/DEPRECATIONS-1.3.md', /core\/adapter/, 'adapter migration note');
    assertContains('docs/DEPRECATIONS-1.3.md', /core\/api/, 'API migration note');

    assertContains('amd/src/core/player.js', /Backwards-compatible facade/, 'facade compatibility documentation');
    assertContains('amd/src/core/adapter.js', /legacy method list/, 'legacy adapter capability documentation');

    [
        'amd/src/player.js',
        'amd/src/html5_player.js',
        'amd/src/vimeo_player.js'
    ].forEach((relativePath) => {
        assertContains(relativePath, /mod_videotrack\/core\//, 'core module dependency');
        assertNotContains(relativePath, /XMLHttpRequest|fetch\s*\(/, 'direct network call outside core/api');
    });

    console.log('Deprecation static checks passed.');
}

main();
