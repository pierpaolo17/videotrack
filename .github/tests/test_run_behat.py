"""Failure-injection checks for the CI runner; no Moodle, PHP or Docker needed."""

import json
import os
from pathlib import Path
import signal
import socket
import subprocess
import sys
import tempfile
import time
import unittest


RUNNER = Path(__file__).resolve().parents[1] / "run_behat.py"
FAKE = r'''#!/usr/bin/env python3
import json, os, signal, socket, subprocess, sys, threading, time
from http.server import BaseHTTPRequestHandler, HTTPServer
from pathlib import Path
workspace = Path(os.environ["GITHUB_WORKSPACE"])
mode = os.environ["VT_RUNNER_TEST_MODE"]
def serve(port):
    class Handler(BaseHTTPRequestHandler):
        def do_GET(self):
            self.send_response(200)
            self.end_headers()
            self.wfile.write(b'{"value":{"ready":true}}')
        def log_message(self, *args):
            pass
    HTTPServer(("localhost", port), Handler).serve_forever()
if len(sys.argv) > 1 and sys.argv[1] == "serve":
    serve(4444)
elif Path(sys.argv[0]).name == "docker":
    if sys.argv[1] == "run":
        process = subprocess.Popen([sys.executable, __file__, "serve"],
                                   stdin=subprocess.DEVNULL, stdout=subprocess.DEVNULL,
                                   stderr=subprocess.DEVNULL, start_new_session=True)
        (workspace / "fake-selenium.pid").write_text(str(process.pid))
        print("fake-container")
    elif sys.argv[1] == "logs":
        print("Fake Selenium retained log")
    elif sys.argv[1] == "rm":
        pid = int((workspace / "fake-selenium.pid").read_text())
        try:
            os.kill(pid, signal.SIGTERM)
        except ProcessLookupError:
            pass
        for _ in range(100):
            try:
                socket.create_connection(("localhost",4444), timeout=.1).close()
            except OSError:
                break
            time.sleep(.01)
        (workspace / "fake-selenium-removed").touch()
elif Path(sys.argv[0]).name == "php":
    assert sys.argv[sys.argv.index("-S") + 1] == "localhost:8000"
    print("Fake PHP server log is retained", flush=True)
    if mode == "startup-exit":
        sys.exit(23)
    if mode in ["signal-exit", "zero-exit"]:
        def fail_after_tests_start():
            while not (workspace / "tests-started").exists():
                time.sleep(.01)
            time.sleep(.1)
            if mode == "zero-exit":
                os._exit(0)
            os.kill(os.getpid(), signal.SIGTERM)
        threading.Thread(target=fail_after_tests_start, daemon=True).start()
    serve(8000)
elif Path(sys.argv[0]).name == "moodle-plugin-ci":
    assert sys.argv[1:] == ["behat", "--profile", "chrome", "--scss-deprecations"]
    assert os.environ["MOODLE_START_BEHAT_SERVERS"] == "NO"
    (workspace / "tests-started").touch()
    time.sleep(.2 if mode in ["success", "behat-failure"] else 20)
    print("Fake Behat executed with unchanged arguments", flush=True)
    sys.exit(7 if mode == "behat-failure" else 0)
'''


class RunnerTest(unittest.TestCase):
    def setUp(self):
        self.directory = tempfile.TemporaryDirectory(prefix="vt-behat-runner-")
        self.addCleanup(self.directory.cleanup)
        self.workspace = Path(self.directory.name)
        self.public = self.workspace / "moodle/public"
        entry = self.public / "admin/tool/behat/cli/run.php"
        entry.parent.mkdir(parents=True)
        entry.touch()
        (self.public / "mod/videotrack").mkdir(parents=True)
        (self.workspace / "ci-reports").mkdir()
        commands = self.workspace / "bin"
        commands.mkdir()
        for name in ["docker", "php", "moodle-plugin-ci"]:
            path = commands / name
            path.write_text(FAKE)
            path.chmod(0o700)
        self.env = dict(os.environ, GITHUB_ACTIONS="true", GITHUB_WORKSPACE=str(self.workspace),
                        MOODLE_ROOT=str(self.workspace / "moodle"),
                        VIDEOTRACK_ROOT=str(self.public / "mod/videotrack"),
                        VT_RUNNER_TEST_MODE="success", PATH=str(commands) + os.pathsep + os.environ["PATH"])

    def execute(self, mode="success"):
        self.env["VT_RUNNER_TEST_MODE"] = mode
        result = subprocess.run([sys.executable, str(RUNNER)], env=self.env,
                                capture_output=True, text=True, timeout=15)
        state = json.loads((self.workspace / "ci-reports/behat-server-state.json").read_text())
        self.assertTrue((self.workspace / "fake-selenium-removed").exists(), result.stderr)
        self.assertIn("Fake PHP server log is retained",
                      (self.workspace / "ci-reports/behat-webserver.log").read_text())
        self.assertIn("Fake Selenium retained log",
                      (self.workspace / "ci-reports/behat-selenium.log").read_text())
        for port in [8000, 4444]:
            with socket.socket() as connection:
                self.assertNotEqual(0, connection.connect_ex(("localhost", port)))
        return result, state

    def test_success_cleans_up_without_calling_intentional_stop_a_crash(self):
        result, state = self.execute()
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertIsNone(state["webserver_exit_before_cleanup"])
        self.assertEqual(0, state["behat_exit"])
        self.assertEqual(-signal.SIGTERM, state["webserver_exit_after_cleanup"])

    def test_classic_moodle_layout_uses_the_same_gate(self):
        for directory in ["admin", "mod"]:
            (self.public / directory).rename(self.workspace / "moodle" / directory)
        self.env["VIDEOTRACK_ROOT"] = str(self.workspace / "moodle/mod/videotrack")
        result, state = self.execute()
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertEqual(0, state["behat_exit"])

    def test_behat_failure_keeps_its_nonzero_exit_status(self):
        result, state = self.execute("behat-failure")
        self.assertEqual(7, result.returncode, result.stderr)
        self.assertEqual(7, state["behat_exit"])
        self.assertIsNone(state["webserver_exit_before_cleanup"])

    def test_php_signal_exit_fails_and_records_signal_before_cleanup(self):
        result, state = self.execute("signal-exit")
        self.assertNotEqual(0, result.returncode)
        self.assertTrue(state["unexpected_webserver_exit"])
        self.assertEqual(-signal.SIGTERM, state["webserver_exit_before_cleanup"])

    def test_unexpected_zero_exit_is_also_a_failure(self):
        result, state = self.execute("zero-exit")
        self.assertNotEqual(0, result.returncode)
        self.assertTrue(state["unexpected_webserver_exit"])
        self.assertEqual(0, state["webserver_exit_before_cleanup"])

    def test_startup_failure_keeps_server_status_and_skips_behat(self):
        result, state = self.execute("startup-exit")
        self.assertNotEqual(0, result.returncode)
        self.assertEqual(23, state["webserver_exit_before_cleanup"])
        self.assertFalse((self.workspace / "tests-started").exists())

    def test_refuses_outside_actions_before_starting_services(self):
        self.env.pop("GITHUB_ACTIONS")
        result = subprocess.run([sys.executable, str(RUNNER)], env=self.env,
                                capture_output=True, text=True, timeout=5)
        self.assertNotEqual(0, result.returncode)
        self.assertIn("disposable GitHub Actions site", result.stderr)
        self.assertFalse((self.workspace / "fake-selenium.pid").exists())

    def test_refuses_an_existing_listener_without_stopping_it(self):
        with socket.socket() as listener:
            listener.bind(("localhost", 8000))
            listener.listen()
            result = subprocess.run([sys.executable, str(RUNNER)], env=self.env,
                                    capture_output=True, text=True, timeout=5)
            self.assertNotEqual(0, result.returncode)
            self.assertIn("existing HTTP or Selenium listener", result.stderr)
            self.assertFalse((self.workspace / "fake-selenium.pid").exists())

    def test_interruption_still_stops_owned_servers_and_records_cleanup(self):
        self.env["VT_RUNNER_TEST_MODE"] = "interrupt"
        process = subprocess.Popen([sys.executable, str(RUNNER)], env=self.env,
                                   stdout=subprocess.PIPE, stderr=subprocess.PIPE, text=True)
        try:
            deadline = time.monotonic() + 5
            while not (self.workspace / "tests-started").exists():
                self.assertIsNone(process.poll())
                self.assertLess(time.monotonic(), deadline)
                time.sleep(.01)
            process.send_signal(signal.SIGTERM)
            _, error = process.communicate(timeout=10)
            self.assertEqual(128 + signal.SIGTERM, process.returncode, error)
            state = json.loads((self.workspace / "ci-reports/behat-server-state.json").read_text())
            self.assertEqual("interrupted", state["phase"])
            self.assertEqual(128 + signal.SIGTERM, state["result"])
            self.assertIsNone(state["webserver_exit_before_cleanup"])
            self.assertEqual(-signal.SIGTERM, state["webserver_exit_after_cleanup"])
            self.assertTrue((self.workspace / "fake-selenium-removed").exists())
        finally:
            if process.poll() is None:
                process.kill()
                process.communicate(timeout=10)


if __name__ == "__main__":
    unittest.main()
