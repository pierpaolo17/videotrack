#!/usr/bin/env python3
"""Run the unchanged Behat gate with observable, CI-owned server processes."""

import json
import os
from pathlib import Path
import signal
import socket
import subprocess
import sys
import time
import urllib.error
import urllib.request


CONTAINER = "videotrack-behat-selenium"


def listening(port):
    """Test a local listener without sending a Moodle request."""
    try:
        with socket.create_connection(("localhost", port), timeout=0.5):
            return True
    except OSError:
        return False


def stop_process(process):
    """Stop only a process group started by this runner and reap its leader."""
    if process is None:
        return None
    if process.poll() is None:
        try:
            os.killpg(process.pid, signal.SIGTERM)
        except ProcessLookupError:
            pass
        try:
            process.wait(timeout=10)
        except subprocess.TimeoutExpired:
            try:
                os.killpg(process.pid, signal.SIGKILL)
            except ProcessLookupError:
                pass
    return process.wait(timeout=10)


def wait_for_servers(webserver, timeout=60):
    """Require the PHP listener and Selenium's ready flag before starting tests."""
    deadline = time.monotonic() + timeout
    while time.monotonic() < deadline:
        if webserver.poll() is not None:
            raise RuntimeError("PHP server exited during startup")
        try:
            with urllib.request.urlopen("http://localhost:4444/status", timeout=2) as response:
                ready = json.load(response).get("value", {}).get("ready") is True
            if ready and listening(8000):
                return
        except (OSError, ValueError, urllib.error.URLError):
            pass
        time.sleep(0.25)
    raise RuntimeError("PHP/Selenium startup did not complete within 60 seconds")


def run():
    """Preserve Behat's exit status and also fail on an unexpected PHP exit."""
    if os.environ.get("GITHUB_ACTIONS") != "true":
        raise ValueError("Behat server runner requires the disposable GitHub Actions site")
    workspace = Path(os.environ["GITHUB_WORKSPACE"]).resolve()
    moodle = Path(os.environ["MOODLE_ROOT"]).resolve()
    plugin = Path(os.environ["VIDEOTRACK_ROOT"]).resolve()
    if moodle != workspace / "moodle" or plugin not in [
        moodle / "mod/videotrack", moodle / "public/mod/videotrack"
    ]:
        raise ValueError("Refusing an unexpected Moodle/plugin path")
    public = plugin.parents[1]
    if not (public / "admin/tool/behat/cli/run.php").is_file():
        raise ValueError("Moodle Behat entry point is missing")
    reports = workspace / "ci-reports"
    if not reports.is_dir():
        raise ValueError("CI report directory is missing")
    if listening(8000) or listening(4444):
        raise ValueError("Refusing to replace an existing HTTP or Selenium listener")

    state = {"phase": "starting", "result": 1, "webserver_exit_before_cleanup": None}
    webserver = tests = None
    container_started = False
    log = (reports / "behat-webserver.log").open("w")
    try:
        image = os.environ.get("MOODLE_BEHAT_SELENIUM_IMAGE") or "selenium/standalone-chrome:4"
        subprocess.run([
            "docker", "run", "-d", "--rm", "--name=" + CONTAINER,
            "--network=host", "--shm-size=2g", "-v", str(moodle) + ":" + str(moodle), image,
        ], check=True, timeout=120)
        container_started = True
        # Keep the existing PHP configuration. Direct diagnostics to the retained log.
        webserver = subprocess.Popen([
            "php", "-d", "log_errors=1", "-d", "error_log=/dev/stderr",
            "-S", "localhost:8000", "-t", str(public),
        ], cwd=moodle, stdout=log, stderr=subprocess.STDOUT, start_new_session=True)
        state["webserver_pid"] = webserver.pid
        wait_for_servers(webserver)
        state["phase"] = "testing"
        state["started_utc"] = time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime())
        env = dict(os.environ, MOODLE_START_BEHAT_SERVERS="NO")
        tests = subprocess.Popen([
            "moodle-plugin-ci", "behat", "--profile", "chrome", "--scss-deprecations",
        ], cwd=workspace, env=env, start_new_session=True)
        while tests.poll() is None:
            if webserver.poll() is not None:
                state["unexpected_webserver_exit"] = True
                state["webserver_exit_utc"] = time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime())
                print("Behat PHP server exited unexpectedly; stopping the test run. See retained server log.", flush=True)
                stop_process(tests)
                break
            time.sleep(0.25)
        state["behat_exit"] = tests.returncode
        state["webserver_exit_before_cleanup"] = webserver.poll()
        if tests.returncode == 0 and state["webserver_exit_before_cleanup"] is None:
            state["result"] = 0
        elif tests.returncode is not None and tests.returncode > 0:
            state["result"] = tests.returncode
        state["phase"] = "finished"
    except SystemExit as error:
        state["phase"] = "interrupted"
        state["result"] = int(error.code)
        raise
    except (OSError, RuntimeError, subprocess.SubprocessError) as error:
        state["error"] = str(error)
        print("Behat server runner failed: " + str(error), file=sys.stderr, flush=True)
    finally:
        state["webserver_exit_before_cleanup"] = None if webserver is None else webserver.poll()
        state["cleanup_started_utc"] = time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime())
        stop_process(tests)
        state["webserver_exit_after_cleanup"] = stop_process(webserver)
        log.close()
        if container_started:
            with (reports / "behat-selenium.log").open("w") as output:
                try:
                    subprocess.run(["docker", "logs", "--tail", "200", CONTAINER],
                                   stdout=output, stderr=subprocess.STDOUT, timeout=15, check=False)
                    cleanup = subprocess.run(["docker", "rm", "-f", CONTAINER],
                                             stdout=subprocess.DEVNULL, stderr=output, timeout=30, check=False)
                    state["selenium_cleanup_exit"] = cleanup.returncode
                    if cleanup.returncode:
                        state["result"] = 1
                except (OSError, subprocess.SubprocessError) as error:
                    state["selenium_cleanup_error"] = str(error)
                    state["result"] = 1
        (reports / "behat-server-state.json").write_text(json.dumps(state, indent=2) + "\n")
    return state["result"]


def interrupted(signum, _frame):
    """Execute the finally cleanup on an interrupted CI step."""
    raise SystemExit(128 + signum)


if __name__ == "__main__":
    signal.signal(signal.SIGINT, interrupted)
    signal.signal(signal.SIGTERM, interrupted)
    try:
        sys.exit(run())
    except (ValueError, KeyError, OSError) as error:
        print("Behat server runner refused: " + str(error), file=sys.stderr)
        sys.exit(1)
