#!/usr/bin/env python3
"""Preserve real Moodle Grunt output on the disposable GitHub Actions site only."""
# Copyright 2026 VideoTrack contributors.
# SPDX-License-Identifier: GPL-3.0-or-later
import hashlib
import json
import os
from pathlib import Path
import re
import subprocess
import sys
import zipfile


def digest(path):
    return hashlib.sha256(path.read_bytes()).hexdigest()


def collect(plugin, checkout, output, commit):
    """Validate provenance and capture only this plugin's known AMD builds/maps."""
    sources = {}
    builds = {}
    for source in sorted((plugin / "amd/src").rglob("*.js")):
        relative = source.relative_to(plugin)
        submitted = checkout / relative
        if source.is_symlink() or submitted.is_symlink():
            raise ValueError("Refusing a symlinked AMD source")
        if not source.resolve().is_relative_to(plugin) or not submitted.resolve().is_relative_to(checkout):
            raise ValueError("Refusing an AMD source outside the selected trees")
        if not submitted.is_file() or source.read_bytes() != submitted.read_bytes():
            raise ValueError("Installed and submitted AMD source differ: " + str(relative))
        sources[relative.as_posix()] = digest(source)
        build = plugin / "amd/build" / source.relative_to(plugin / "amd/src").with_suffix(".min.js")
        mapfile = Path(str(build) + ".map")
        if not build.is_file() or not mapfile.is_file() or not build.stat().st_size:
            raise ValueError("Incomplete native AMD build: " + str(relative))
        if any(path.is_symlink() or not path.resolve().is_relative_to(plugin) for path in [build, mapfile]):
            raise ValueError("Refusing a build outside the selected plugin")
        data = json.loads(mapfile.read_text())
        if not data.get("mappings") or data.get("sourcesContent") != [source.read_text()]:
            raise ValueError("Native map does not contain the exact source: " + str(relative))
        if len(data.get("sources", [])) != 1 or (mapfile.parent / data["sources"][0]).resolve() != source.resolve():
            raise ValueError("Native map source path is not canonical: " + str(relative))
        for path in [build, mapfile]:
            builds[path.relative_to(plugin).as_posix()] = digest(path)
    submitted_sources = {path.relative_to(checkout).as_posix() for path in (checkout / "amd/src").rglob("*.js")}
    if not sources or set(sources) != submitted_sources or len(builds) != 2 * len(sources):
        raise ValueError("Native build set does not match the submitted AMD sources")
    report = {
        "commit": commit,
        "command": ["npx", "--no-install", "grunt", "amd"],
        "native_grunt_exit": 0,
        "sources": sources,
        "builds": builds,
    }
    # Only expected relative build paths and a redacted provenance manifest enter the ZIP.
    with zipfile.ZipFile(output, "w", zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for relative in builds:
            archive.write(plugin / relative, relative)
        archive.writestr("native-amd-manifest.json", json.dumps(report, indent=2) + "\n")
    return report


def main():
    if os.environ.get("GITHUB_ACTIONS") != "true":
        raise ValueError("Native capture requires the disposable GitHub Actions site")
    workspace = Path(os.environ["GITHUB_WORKSPACE"]).resolve()
    moodle = Path(os.environ["MOODLE_ROOT"]).resolve()
    plugin = Path(os.environ["VIDEOTRACK_ROOT"]).resolve()
    if moodle != workspace / "moodle" or plugin not in [moodle / "mod/videotrack", moodle / "public/mod/videotrack"]:
        raise ValueError("Refusing an unexpected Moodle/plugin path")
    commit = os.environ["GITHUB_SHA"]
    if not re.fullmatch(r"[a-f0-9]{40}", commit):
        raise ValueError("Expected a concrete GitHub source commit")
    output = workspace / "ci-reports/native-amd.zip"
    if not output.parent.is_dir():
        raise ValueError("CI report directory is missing")
    # Separate from the earlier, unchanged plugin-CI freshness check, which restores its inputs.
    subprocess.run(["npx", "--no-install", "grunt", "amd"], cwd=plugin / "amd", check=True)
    report = collect(plugin, workspace / "plugin", output, commit)
    print("Native AMD capture: %d sources, %d builds/maps; SHA256 %s" %
          (len(report["sources"]), len(report["builds"]), digest(output)))


if __name__ == "__main__":
    try:
        main()
    except (ValueError, KeyError, OSError, subprocess.CalledProcessError) as error:
        print("Native AMD capture failed: " + str(error), file=sys.stderr)
        sys.exit(1)
