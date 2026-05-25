#!/usr/bin/env node
/*
 * AMD smoke checks for mod_videotrack.
 *
 * This script intentionally avoids Moodle, Grunt and third-party dependencies so
 * it can be run from a clean plugin checkout during release preparation:
 *
 *     node tests/smoke_amd.js
 */

const fs = require('fs');
const path = require('path');

const pluginRoot = path.resolve(__dirname, '..');
const srcRoot = path.join(pluginRoot, 'amd', 'src');
const buildRoot = path.join(pluginRoot, 'amd', 'build');

const CORE_PREFIX = 'mod_videotrack/core/';
const LOCAL_MODULE_PREFIX = 'mod_videotrack/';

function fail(message) {
    throw new Error(message);
}

function walk(dir) {
    if (!fs.existsSync(dir)) {
        return [];
    }

    return fs.readdirSync(dir, {withFileTypes: true}).flatMap((entry) => {
        const fullPath = path.join(dir, entry.name);
        if (entry.isDirectory()) {
            return walk(fullPath);
        }
        return entry.isFile() && entry.name.endsWith('.js') ? [fullPath] : [];
    });
}

function toPosix(relativePath) {
    return relativePath.split(path.sep).join('/');
}

function expectedBuildPath(srcFile) {
    const relative = path.relative(srcRoot, srcFile).replace(/\.js$/, '.min.js');
    return path.join(buildRoot, relative);
}

function moduleNameFromDependency(dependency) {
    if (dependency.startsWith(CORE_PREFIX)) {
        return `core/${dependency.substring(CORE_PREFIX.length)}`;
    }
    if (dependency.startsWith(LOCAL_MODULE_PREFIX)) {
        return dependency.substring(LOCAL_MODULE_PREFIX.length);
    }
    return null;
}

function findLocalDependencyPath(dependency) {
    const moduleName = moduleNameFromDependency(dependency);
    if (!moduleName) {
        return null;
    }
    return path.join(srcRoot, `${moduleName}.js`);
}

function extractDependencies(source, filename) {
    const match = source.match(/define\s*\(\s*\[([\s\S]*?)\]/m);
    if (!match) {
        fail(`${filename}: missing AMD dependency array`);
    }

    const dependencies = [];
    const dependencyPattern = /['"]([^'"]+)['"]/g;
    let dependencyMatch;
    while ((dependencyMatch = dependencyPattern.exec(match[1])) !== null) {
        dependencies.push(dependencyMatch[1]);
    }
    return dependencies;
}

function checkSourceFile(srcFile) {
    const relative = toPosix(path.relative(pluginRoot, srcFile));
    const source = fs.readFileSync(srcFile, 'utf8');

    if (!/define\s*\(/.test(source)) {
        fail(`${relative}: missing AMD define() wrapper`);
    }

    const buildFile = expectedBuildPath(srcFile);
    if (!fs.existsSync(buildFile)) {
        fail(`${relative}: missing committed build file ${toPosix(path.relative(pluginRoot, buildFile))}`);
    }

    extractDependencies(source, relative).forEach((dependency) => {
        const localDependency = findLocalDependencyPath(dependency);
        if (localDependency && !fs.existsSync(localDependency)) {
            fail(`${relative}: missing local dependency ${dependency}`);
        }
    });
}

function main() {
    const sourceFiles = walk(srcRoot).sort();
    if (!sourceFiles.length) {
        fail('No AMD source files found');
    }

    sourceFiles.forEach(checkSourceFile);

    const buildFiles = walk(buildRoot).sort();
    const expectedBuildFiles = new Set(sourceFiles.map((srcFile) => path.resolve(expectedBuildPath(srcFile))));
    buildFiles.forEach((buildFile) => {
        if (!expectedBuildFiles.has(path.resolve(buildFile))) {
            fail(`${toPosix(path.relative(pluginRoot, buildFile))}: build file has no matching amd/src file`);
        }
    });

    console.log(`AMD smoke checks passed (${sourceFiles.length} source files, ${buildFiles.length} build files).`);
}

main();
