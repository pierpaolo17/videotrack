<?php
// This file is part of Moodle - https://moodle.org/.
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Non-destructive VideoTrack installation and release validator.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/clilib.php');
require_once($CFG->libdir . '/ddllib.php');

$usage = <<<'USAGE'
Validates the installed VideoTrack plugin without modifying Moodle data.

Usage:
    php mod/videotrack/cli/validate.php [--json] [--verbose] [--strict]
    php mod/videotrack/cli/validate.php --help

Options:
    -h, --help     Print this help.
    --json         Emit the complete report as JSON.
    --verbose      Include successful per-file/per-object detail in text output.
    --strict       Return a non-zero exit code for warnings as well as failures.

Checks:
    - plugin file version vs installed version and supported Moodle branch;
    - XMLDB tables, fields and indexes declared by db/install.xml;
    - AJAX external functions declared by db/services.php;
    - maintained language-pack key and placeholder parity;
    - AMD src/build/source-map pairing and sourcesContent alignment;
    - current documentation release markers;
    - selected privacy/performance/tracking configuration values.

This command is read-only. It has no repair mode and performs no writes.
USAGE;

list($options, $unrecognised) = cli_get_params([
    'help' => false,
    'json' => false,
    'verbose' => false,
    'strict' => false,
], [
    'h' => 'help',
]);

if ($unrecognised) {
    cli_error('Unknown option(s): ' . implode(', ', $unrecognised), 2);
}
if ($options['help']) {
    cli_writeln($usage);
    exit(0);
}

$pluginroot = dirname(__DIR__);
$checks = [];
$details = [];

$addcheck = static function (string $name, string $status, string $detail) use (&$checks): void {
    $checks[] = [
        'name' => $name,
        'status' => $status,
        'detail' => $detail,
    ];
};

$normalise = static function (string $value): string {
    return str_replace(["\r\n", "\r"], "\n", $value);
};

$plugin = new stdClass();
require($pluginroot . '/version.php');
$installedversion = get_config('mod_videotrack', 'version');
$filerelease = (string)$plugin->release;
$fileversion = (int)$plugin->version;

if ($installedversion === false) {
    $addcheck('installed_version', 'warn', 'Plugin version is not present in config_plugins.');
} elseif ((int)$installedversion !== $fileversion) {
    $addcheck(
        'installed_version',
        'fail',
        'Installed version ' . (int)$installedversion . ' differs from file version ' . $fileversion . '.'
    );
} else {
    $addcheck('installed_version', 'pass', 'Installed and file versions both equal ' . $fileversion . '.');
}

$supported = $plugin->supported ?? null;
$branch = isset($CFG->branch) ? (int)$CFG->branch : 0;
if (is_array($supported) && count($supported) === 2 && $branch > 0) {
    $minimum = (int)$supported[0];
    $maximum = (int)$supported[1];
    if ($branch < $minimum || $branch > $maximum) {
        $addcheck(
            'moodle_branch',
            'fail',
            'Moodle branch ' . $branch . ' is outside supported range ' . $minimum . '-' . $maximum . '.'
        );
    } else {
        $addcheck('moodle_branch', 'pass', 'Moodle branch ' . $branch . ' is inside the supported range.');
    }
} else {
    $addcheck('moodle_branch', 'warn', 'Unable to evaluate the declared Moodle support range.');
}

$dbman = $DB->get_manager();
$installxml = new xmldb_file($pluginroot . '/db/install.xml');
if (!$installxml->loadXMLStructure() || !$installxml->getStructure()) {
    $addcheck('xmldb_schema', 'fail', 'Unable to parse db/install.xml.');
} else {
    $missing = [];
    $tablecount = 0;
    $fieldcount = 0;
    $indexcount = 0;
    foreach ($installxml->getStructure()->getTables() as $table) {
        $tablecount++;
        if (!$dbman->table_exists($table)) {
            $missing[] = 'table:' . $table->getName();
            continue;
        }
        foreach ($table->getFields() as $field) {
            $fieldcount++;
            if (!$dbman->field_exists($table, $field)) {
                $missing[] = 'field:' . $table->getName() . '.' . $field->getName();
            }
        }
        foreach ($table->getIndexes() as $index) {
            $indexcount++;
            if (!$dbman->index_exists($table, $index)) {
                $missing[] = 'index:' . $table->getName() . '.' . $index->getName();
            }
        }
    }
    $details['xmldb'] = [
        'tables' => $tablecount,
        'fields' => $fieldcount,
        'indexes' => $indexcount,
        'missing' => $missing,
    ];
    if ($missing) {
        $addcheck('xmldb_schema', 'fail', 'Missing schema objects: ' . implode(', ', $missing));
    } else {
        $addcheck(
            'xmldb_schema',
            'pass',
            $tablecount . ' tables, ' . $fieldcount . ' fields and ' . $indexcount . ' indexes match db/install.xml.'
        );
    }
}

$functions = [];
require($pluginroot . '/db/services.php');
$serviceissues = [];
foreach ($functions as $name => $definition) {
    $classname = $definition['classname'] ?? '';
    $methodname = $definition['methodname'] ?? '';
    if (empty($definition['ajax'])) {
        $serviceissues[] = $name . ':ajax=false';
    }
    if ($classname === '' || !class_exists($classname)) {
        $serviceissues[] = $name . ':missing-class';
        continue;
    }
    if ($methodname === '' || !method_exists($classname, $methodname)) {
        $serviceissues[] = $name . ':missing-method';
    }
}
$details['services'] = [
    'count' => count($functions),
    'issues' => $serviceissues,
];
if ($serviceissues) {
    $addcheck('ajax_services', 'fail', 'Service declaration issues: ' . implode(', ', $serviceissues));
} else {
    $addcheck('ajax_services', 'pass', count($functions) . ' AJAX services resolve to declared external methods.');
}

$languages = ['de', 'en', 'es', 'fr', 'hi', 'it', 'pl', 'pt'];
$languagecontracts = [];
$languageissues = [];
foreach ($languages as $language) {
    $path = $pluginroot . '/lang/' . $language . '/videotrack.php';
    $source = is_readable($path) ? file_get_contents($path) : false;
    if ($source === false) {
        $languageissues[] = $language . ':missing';
        continue;
    }
    preg_match_all('/\$string\[\'([^\']+)\'\]\s*=/', $source, $keymatches, PREG_OFFSET_CAPTURE);
    $keys = array_map(static fn(array $match): string => $match[0], $keymatches[1]);
    $sortedkeys = $keys;
    sort($sortedkeys);
    if (count($sortedkeys) !== count(array_unique($sortedkeys))) {
        $languageissues[] = $language . ':duplicate-keys';
    }
    $placeholders = [];
    $matchcount = count($keymatches[0]);
    for ($index = 0; $index < $matchcount; $index++) {
        $key = $keymatches[1][$index][0];
        $assignmentstart = $keymatches[0][$index][1] + strlen($keymatches[0][$index][0]);
        $assignmentend = $index + 1 < $matchcount ? $keymatches[0][$index + 1][1] : strlen($source);
        $assignment = substr($source, $assignmentstart, $assignmentend - $assignmentstart);
        preg_match_all('/\{\$a(?:->\w+)?\}/', $assignment, $placeholdermatches);
        $values = array_values(array_unique($placeholdermatches[0]));
        sort($values);
        $placeholders[$key] = $values;
    }
    ksort($placeholders);
    $languagecontracts[$language] = [
        'keys' => $sortedkeys,
        'placeholders' => $placeholders,
    ];
}
if (isset($languagecontracts['en'])) {
    foreach ($languages as $language) {
        if (!isset($languagecontracts[$language])) {
            continue;
        }
        if ($languagecontracts[$language]['keys'] !== $languagecontracts['en']['keys']) {
            $languageissues[] = $language . ':key-mismatch';
        }
        if ($languagecontracts[$language]['placeholders'] !== $languagecontracts['en']['placeholders']) {
            $languageissues[] = $language . ':placeholder-mismatch';
        }
    }
} else {
    $languageissues[] = 'en:reference-missing';
}
$details['languages'] = [
    'languages' => count($languagecontracts),
    'keys' => isset($languagecontracts['en']) ? count($languagecontracts['en']['keys']) : 0,
    'issues' => array_values(array_unique($languageissues)),
];
if ($languageissues) {
    $addcheck('language_contract', 'fail', 'Language contract issues: ' . implode(', ', array_unique($languageissues)));
} else {
    $addcheck(
        'language_contract',
        'pass',
        count($languages) . ' language packs share ' . count($languagecontracts['en']['keys']) . ' keys and placeholders.'
    );
}

$srcroot = $pluginroot . '/amd/src';
$amdissues = [];
$amdcount = 0;
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcroot, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $fileinfo) {
    if (!$fileinfo->isFile() || $fileinfo->getExtension() !== 'js') {
        continue;
    }
    $amdcount++;
    $sourcepath = $fileinfo->getPathname();
    $relative = substr($sourcepath, strlen($srcroot) + 1);
    $buildpath = $pluginroot . '/amd/build/' . substr($relative, 0, -3) . '.min.js';
    $mappath = $buildpath . '.map';
    if (!is_readable($buildpath)) {
        $amdissues[] = $relative . ':missing-build';
        continue;
    }
    if (!is_readable($mappath)) {
        $amdissues[] = $relative . ':missing-map';
        continue;
    }
    $map = json_decode((string)file_get_contents($mappath), true);
    if (!is_array($map) || empty($map['sourcesContent'][0])) {
        $amdissues[] = $relative . ':invalid-map';
        continue;
    }
    $source = (string)file_get_contents($sourcepath);
    if ($normalise($source) !== $normalise((string)$map['sourcesContent'][0])) {
        $amdissues[] = $relative . ':map-source-mismatch';
    }
}
$details['amd'] = [
    'sources' => $amdcount,
    'issues' => $amdissues,
];
if ($amdissues) {
    $addcheck('amd_contract', 'fail', 'AMD build/source-map issues: ' . implode(', ', $amdissues));
} else {
    $addcheck('amd_contract', 'pass', $amdcount . ' AMD sources have matching builds and source-map source content.');
}

$historyfile = $pluginroot . '/VIDEOTRACK_CHANGELOG_LESSONS_ROADMAP_1.7.5_' . $filerelease . '.md';
$readme = file_get_contents($pluginroot . '/README.md');
$readmeit = file_get_contents($pluginroot . '/README_IT.md');
if (!is_file($historyfile) || $readme === false || $readmeit === false ||
        !str_contains($readme, 'Current release documented by this tree: **' . $filerelease . '**') ||
        !str_contains($readmeit, 'Release corrente documentata da questo albero: **' . $filerelease . '**')) {
    $addcheck('documentation_release', 'fail', 'Current release documentation markers are incomplete or stale.');
} else {
    $addcheck('documentation_release', 'pass', 'README and consolidated history track release ' . $filerelease . '.');
}

$criticalsettings = [
    'retentionperioddays',
    'retentionunlimitedconfirmed',
    'validationfallbackdays',
    'heartbeatinterval',
    'analyticsminusers',
    'strictsessionvalidation',
    'maxplaybackrate',
    'focuslosspolicy',
    'focuslossgraceseconds',
    'randompauseminseconds',
    'randompausemaxseconds',
];
$config = get_config('mod_videotrack');
if (!is_object($config)) {
    $config = new stdClass();
}
$criticalconfig = [];
foreach ($criticalsettings as $setting) {
    $criticalconfig[$setting] = property_exists($config, $setting) ? $config->{$setting} : null;
}
$details['critical_config'] = $criticalconfig;
$addcheck('critical_config', 'pass', 'Selected privacy/performance/tracking configuration captured in the report.');

$failures = count(array_filter($checks, static fn(array $check): bool => $check['status'] === 'fail'));
$warnings = count(array_filter($checks, static fn(array $check): bool => $check['status'] === 'warn'));
$passes = count(array_filter($checks, static fn(array $check): bool => $check['status'] === 'pass'));

$report = [
    'validator' => 'mod_videotrack',
    'release' => $filerelease,
    'version' => $fileversion,
    'moodle_release' => $CFG->release,
    'moodle_branch' => $CFG->branch,
    'php_version' => PHP_VERSION,
    'db_family' => $DB->get_dbfamily(),
    'summary' => [
        'pass' => $passes,
        'warn' => $warnings,
        'fail' => $failures,
    ],
    'checks' => $checks,
    'details' => $details,
];

if ($options['json']) {
    cli_writeln(json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
} else {
    cli_writeln('VideoTrack ' . $filerelease . ' validation');
    cli_writeln('Moodle ' . $CFG->release . ' | PHP ' . PHP_VERSION . ' | DB ' . $DB->get_dbfamily());
    cli_writeln('');
    foreach ($checks as $check) {
        $label = strtoupper($check['status']);
        cli_writeln('[' . $label . '] ' . $check['name'] . ': ' . $check['detail']);
    }
    cli_writeln('');
    cli_writeln('Summary: ' . $passes . ' pass, ' . $warnings . ' warn, ' . $failures . ' fail.');
    if ($options['verbose']) {
        cli_writeln(json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

if ($failures > 0 || ($options['strict'] && $warnings > 0)) {
    exit(1);
}
exit(0);
