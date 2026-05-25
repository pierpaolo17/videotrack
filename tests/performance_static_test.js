#!/usr/bin/env node
/*
 * Static performance review checks for mod_videotrack.
 *
 * These checks are intentionally conservative and Moodle-independent. They
 * protect the 1.3 refactor goal of keeping request/retry/heartbeat logic in
 * shared core modules rather than drifting back into provider entry points.
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

function countMatches(content, pattern) {
    const matches = content.match(pattern);
    return matches ? matches.length : 0;
}

function assertAtMost(relativePath, pattern, maximum, description) {
    const total = countMatches(read(relativePath), pattern);
    if (total > maximum) {
        fail(`${relativePath}: ${description} count ${total} exceeds ${maximum}`);
    }
}

function main() {
    assertFile('docs/PERFORMANCE-1.3.md');
    assertContains('docs/PERFORMANCE-1.3.md', /core\/api\.js/, 'API performance scope note');
    assertContains('docs/PERFORMANCE-1.3.md', /core\/tracker\.js/, 'tracker performance scope note');
    assertContains('docs/PERFORMANCE-1.3.md', /Manual checks still required/, 'manual runtime caveat');

    assertContains('amd/src/core/api.js', /AJAX_MAX_RETRIES\s*=\s*2/, 'bounded AJAX retry count');
    assertContains('amd/src/core/api.js', /retryDelay/, 'central retry delay helper');
    assertContains('amd/src/core/api.js', /createRequestScope/, 'request scope cleanup helper');
    assertContains('amd/src/core/tracker.js', /startHeartbeat|scheduleHeartbeat|heartbeat/i, 'central heartbeat handling');
    assertContains('amd/src/core/tracker.js', /isTransitionCurrent/, 'async transition guard');

    [
        'amd/src/player.js',
        'amd/src/html5_player.js',
        'amd/src/vimeo_player.js'
    ].forEach((relativePath) => {
        assertContains(relativePath, /mod_videotrack\/core\//, 'shared core dependency');
        assertNotContains(relativePath, /XMLHttpRequest|fetch\s*\(/, 'direct network call outside core/api');
        assertAtMost(relativePath, /setInterval\s*\(/g, 1, 'direct interval usage');
    });

    console.log('Performance static checks passed.');
}

main();
