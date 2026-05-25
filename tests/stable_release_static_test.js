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
assert(/\$plugin->maturity\s*=\s*MATURITY_STABLE;/.test(version), 'stable package must use MATURITY_STABLE');
assert(/\$plugin->release\s*=\s*'1\.3\.(?:8\d|9\d)';/.test(version), 'stable release marker must be in the 1.3.80+ stable line');
assert(exists('docs/STABLE-RELEASE-1.3.md'), 'stable release documentation must exist');
assert(read('docs/STABLE-RELEASE-1.3.md').includes('MATURITY_STABLE'), 'stable documentation must mention MATURITY_STABLE');
assert(exists('docs/FINAL-CHECKS-1.3.md'), 'final checks documentation must exist');
assert(exists('tests/final_static_test.js'), 'final static test must exist');

console.log('stable_release_static_test.js: ok');
