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

assert(exists('tests/review_fixes_static_test.js'), 'review fixes static test must exist');

const version = read('version.php');
assert(/\$plugin->release\s*=\s*'(?:1\.3\.\d+|1\.4\.\d+)'/.test(version), 'review fix line must remain in the stable 1.3/1.4 line');

const report = read('report.php');
assert(/X-Content-Type-Options:\s*nosniff/.test(report), 'CSV exports must send nosniff header');
assert(/report:exportnotes_csv_personaldata/.test(report), 'notes export submit must have a personal-data aria label');

const presets = read('presets.php');
assert(/presets:deletearia/.test(presets), 'preset delete action must have contextual aria-label');
assert(/presets:reactionlabelaria/.test(presets), 'preset reaction fields must have contextual aria-labels');

const lang = read('lang/en/videotrack.php');
[
    'presets:deletearia',
    'presets:reactionlabelaria',
    'presets:reactiondescriptionaria',
    'presets:reactionicontypearia',
    'presets:reactioniconvaluearia',
    'presets:reactionrequiredaria'
].forEach((key) => {
    assert(lang.includes(`$string['${key}']`), `${key} language string must exist`);
});

const finalTest = read('tests/final_static_test.js');
assert(!/202605250\(\?:79\|80/.test(finalTest), 'final static test must not hard-code each micro-release');
assert(/tests\/review_fixes_static_test\.js/.test(finalTest), 'final gate must include review fixes test');

console.log('review_fixes_static_test.js: ok');
