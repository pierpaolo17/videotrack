#!/usr/bin/env bash
# Repository-only destructive lifecycle gate for the disposable GitHub Actions site.
set -euo pipefail

if [[ "${GITHUB_ACTIONS:-}" != true || -z "${GITHUB_WORKSPACE:-}" || -z "${MOODLE_ROOT:-}" ]]; then
    echo 'Lifecycle gate requires the disposable GitHub Actions environment.' >&2
    exit 2
fi
if [[ "$MOODLE_ROOT" != "$GITHUB_WORKSPACE/moodle" ]]; then
    echo 'Unexpected Moodle root; lifecycle gate stopped.' >&2
    exit 2
fi
case "${VIDEOTRACK_ROOT:-}" in
    "$MOODLE_ROOT/mod/videotrack"|"$MOODLE_ROOT/public/mod/videotrack") ;;
    *) echo 'Unexpected VideoTrack root; lifecycle gate stopped.' >&2; exit 2 ;;
esac

checkout="$GITHUB_WORKSPACE/plugin"
checker="$checkout/.github/lifecycle/site.php"
reportroot="$GITHUB_WORKSPACE/ci-reports/lifecycle"
staging="$(mktemp -d "${RUNNER_TEMP:?}/videotrack-lifecycle.XXXXXX")"
mkdir -p "$reportroot" "$staging/target"
php -l "$checker"
git -C "$checkout" archive HEAD | tar -xf - -C "$staging/target"

# The normal CI installer copies the plugin; also handle a future symlink installer safely.
php "$checker" --mode=guard
if [[ -L "$VIDEOTRACK_ROOT" ]]; then
    unlink "$VIDEOTRACK_ROOT"
    mkdir "$VIDEOTRACK_ROOT"
fi
if [[ "$(realpath "$VIDEOTRACK_ROOT")" != "$VIDEOTRACK_ROOT" ]]; then
    echo 'VideoTrack path resolves outside its dedicated Moodle location.' >&2
    exit 2
fi
restore_code() {
    rsync -a --delete "$staging/target/" "$VIDEOTRACK_ROOT/"
}
trap restore_code EXIT

# Remove the initially installed, empty current plugin only to prepare the historical install.
php "$MOODLE_ROOT/admin/cli/uninstall_plugins.php" --plugins=mod_videotrack --run

for label in historical partial previous; do
    if [[ "$label" == previous ]]; then
        origin=511234197069f8f83e23f0da9444fa1d6a5f8030
        version=2026111203
    else
        origin=baa07ab580c15f8924ecb812a5832231e981bed2
        version=2026110507
    fi
    echo "=== Lifecycle $label | baseline $origin | version $version ==="
    mkdir "$staging/$label"
    git -C "$checkout" cat-file -e "$origin^{commit}"
    git -C "$checkout" archive "$origin" | tar -xf - -C "$staging/$label"
    rsync -a --delete "$staging/$label/" "$VIDEOTRACK_ROOT/"
    php "$MOODLE_ROOT/admin/cli/upgrade.php" --non-interactive
    proof="$reportroot/$label-before.json"
    php "$checker" --mode=seed --label="$label" --expected="$version" --manifest="$proof"
    if [[ "$label" == partial ]]; then
        php "$checker" --mode=partial --manifest="$proof"
    fi
    restore_code
    php "$MOODLE_ROOT/admin/cli/upgrade.php" --non-interactive
    php "$checker" --mode=verify --manifest="$proof" | tee "$reportroot/$label-upgraded.txt"
    php "$VIDEOTRACK_ROOT/cli/validate.php" --strict
    php "$MOODLE_ROOT/admin/cli/upgrade.php" --non-interactive
    php "$checker" --mode=verify --manifest="$proof" | tee "$reportroot/$label-repeated.txt"
    firsthash="$(sed -n 's/^Preserved snapshot SHA-256: //p' "$reportroot/$label-upgraded.txt")"
    secondhash="$(sed -n 's/^Preserved snapshot SHA-256: //p' "$reportroot/$label-repeated.txt")"
    if [[ -z "$firsthash" || "$firsthash" != "$secondhash" ]]; then
        echo 'Repeated upgrade changed the preserved snapshot.' >&2
        exit 1
    fi
    php "$MOODLE_ROOT/admin/cli/uninstall_plugins.php" --plugins=mod_videotrack --run
    php "$checker" --mode=residue --manifest="$proof" | tee "$reportroot/$label-uninstalled.txt"
    echo "[PASS] Lifecycle $label: upgrade, repeatability and populated uninstall"
done
echo '[PASS] Lifecycle gate: all three lineages completed.'
