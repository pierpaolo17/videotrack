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

namespace mod_videotrack;

use advanced_testcase;
use PHPUnit\Framework\Attributes\CoversNothing;

/**
 * Static regression contracts for the teacher report.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class report_contract_test extends advanced_testcase {
    /**
     * Per-student reporting must expose reaction replay links.
     */
    public function test_student_report_contains_reaction_replay_section(): void {
        $source = file_get_contents(__DIR__ . '/../report.php');
        $this->assertIsString($source);

        $this->assertStringContainsString("get_string('report:studentreactions_title'", $source);
        $this->assertStringContainsString('$getstudenteventrecordset()', $source);
        $this->assertStringContainsString("'replaystart' => max(0, \$replaytimestamp - \$window)", $source);
        $this->assertStringContainsString("'replayend' => \$replaytimestamp + \$window", $source);
    }

    /**
     * Instance Analytics is exact only when every included activity permits individual report access.
     */
    public function test_instance_analytics_uses_capability_aware_privacy_threshold(): void {
        $source = file_get_contents(__DIR__ . '/../report.php');
        $this->assertIsString($source);

        $start = strpos($source, "if (\$mode === 'analytics') {");
        $this->assertNotFalse($start);
        $end = strpos($source, "\n\$sortsql = 'videotime ASC';", $start);
        $this->assertNotFalse($end);
        $analytics = substr($source, $start, $end - $start);

        $this->assertStringContainsString('report_access::can_view_individual(', $analytics);
        $this->assertStringContainsString(
            '$minusers = $canviewexactanalytics',
            $analytics
        );
        $this->assertStringContainsString('analytics::EXACT_REPORT_MIN_USERS', $analytics);
        $this->assertStringContainsString("videotrack_get_config_int('analyticsminusers'", $analytics);
        $this->assertStringContainsString('analytics::apply_privacy_threshold($analytics, $minusers)', $analytics);
    }

    /**
     * Activity reports keep aggregate viewing, individual viewing and both export levels separated.
     */
    public function test_report_controller_enforces_granular_report_capabilities(): void {
        $source = file_get_contents(__DIR__ . '/../report.php');
        $this->assertIsString($source);

        $this->assertStringContainsString('report_access::can_view_aggregate($context)', $source);
        $this->assertStringContainsString('report_access::can_view_individual($context)', $source);
        $this->assertStringContainsString('report_access::can_export_aggregate($context)', $source);
        $this->assertStringContainsString('report_access::can_export_individual($context)', $source);
        $this->assertStringContainsString(
            "require_capability('mod/videotrack:viewindividualreport', \$context);",
            $source
        );
        $this->assertStringContainsString(
            "require_capability('mod/videotrack:exportaggregatereport', \$context);",
            $source
        );
        $this->assertStringContainsString(
            "require_capability('mod/videotrack:exportindividualreport', \$context);",
            $source
        );
        $this->assertStringContainsString('$reportreactionssuppressed', $source);
        $this->assertStringContainsString('report:cumulative_privacy_suppressed', $source);
        $this->assertStringContainsString(
            "if (!\$canviewindividualreport) {\n        require_capability('mod/videotrack:viewindividualreport', \$context);",
            $source
        );
        $this->assertStringContainsString(
            "\$canviewfullreport && has_capability('mod/videotrack:managereactions', \$context)",
            $source
        );
    }

    /**
     * Granular report capabilities must preserve customised legacy permissions on upgrade.
     */
    public function test_granular_report_capabilities_clone_legacy_assignments(): void {
        $source = file_get_contents(__DIR__ . '/../db/access.php');
        $this->assertIsString($source);

        $capabilities = [
            'viewaggregatereport',
            'viewindividualreport',
            'exportaggregatereport',
            'exportindividualreport',
        ];
        foreach ($capabilities as $capability) {
            $start = strpos($source, "'mod/videotrack:" . $capability . "' => [");
            $this->assertNotFalse($start);
            $end = strpos($source, "\n    ],", $start);
            $this->assertNotFalse($end);
            $definition = substr($source, $start, $end - $start);
            $this->assertStringContainsString(
                "'clonepermissionsfrom' => 'mod/videotrack:viewreport'",
                $definition
            );
        }
    }

    /**
     * Moodle 5.0 save/cancel modals do not expose setCancelButtonText().
     */
    public function test_report_confirmation_uses_supported_modal_api(): void {
        $source = file_get_contents(__DIR__ . '/../amd/src/core/confirm.js');
        $this->assertIsString($source);

        $this->assertStringContainsString('modal.setSaveButtonText(strings[1]);', $source);
        $this->assertStringNotContainsString('setCancelButtonText', $source);
        $this->assertStringContainsString('root.on(ModalEvents.save', $source);
        $this->assertStringContainsString('submitForm(form);', $source);
    }

    /**
     * Custom CSV event rows must delegate stable export context to the dedicated writer.
     */
    public function test_custom_csv_event_rows_delegate_to_writer(): void {
        $source = file_get_contents(__DIR__ . '/../report.php');
        $this->assertIsString($source);

        $this->assertStringContainsString('new \mod_videotrack\local\csv_event_writer(', $source);
        $this->assertStringContainsString('$eventwriter->write(', $source);
        $this->assertStringNotContainsString('$writeeventrow = static function', $source);
    }

    /**
     * The custom teacher CSV export must offer privacy-safe bookmark counts.
     */
    public function test_custom_csv_export_supports_private_bookmark_counts(): void {
        $source = file_get_contents(__DIR__ . '/../report.php');
        $this->assertIsString($source);

        $this->assertStringContainsString("optional_param('csvincludebookmarks'", $source);
        $this->assertStringContainsString("'name' => 'csvincludebookmarks'", $source);
        $this->assertStringContainsString("AND notetype = 'bookmark'", $source);
        $this->assertStringContainsString('COUNT(DISTINCT userid) AS studentcount', $source);
        $this->assertStringContainsString("get_string('report:bookmarks_count'", $source);
        $this->assertStringContainsString("get_string('report:csvexport_bookmarks_help'", $source);
        $this->assertStringNotContainsString('SELECT userid, notetext', $source);
    }

    /**
     * The custom CSV event writer must receive the module context used by identity formatting.
     */
    public function test_custom_csv_event_writer_receives_module_context(): void {
        $source = file_get_contents(__DIR__ . '/../report.php');
        $this->assertIsString($source);

        $start = strpos($source, 'new \mod_videotrack\local\csv_event_writer(');
        $this->assertNotFalse($start);
        $end = strpos($source, ');', $start);
        $this->assertNotFalse($end);
        $constructor = substr($source, $start, $end - $start);

        $this->assertStringContainsString('$context', $constructor);
    }

    /**
     * Dual-role learners keep their own grade even when they can also view reports.
     */
    public function test_student_grade_visibility_depends_on_participation_not_report_access(): void {
        $source = file_get_contents(__DIR__ . '/../view.php');
        $this->assertIsString($source);

        $this->assertStringContainsString('learner_scope::can_participate($context)', $source);
        $this->assertMatchesRegularExpression(
            '/showgradeto.*?grade.*?\$islearner/s',
            $source
        );
        $this->assertStringNotContainsString(
            '!has_capability(\'mod/videotrack:viewreport\', $context)',
            $source
        );
    }

    /**
     * Student grade label uses the plugin-owned translated string.
     */
    public function test_student_grade_label_uses_plugin_string(): void {
        $source = file_get_contents(__DIR__ . '/../view.php');
        $this->assertIsString($source);
        $this->assertStringNotContainsString("get_string('grade')", $source);
        $this->assertStringContainsString("get_string('report:grade', 'mod_videotrack')", $source);
    }

    /**
     * Provider privacy and integrity guidance share the same player alert.
     */
    public function test_provider_and_integrity_notices_share_one_alert(): void {
        $source = file_get_contents(__DIR__ . '/../view.php');
        $this->assertIsString($source);

        $this->assertStringContainsString('$playernotices = [];', $source);
        $this->assertStringContainsString("\$playernotices[] = get_string('externalproviderprivacy_notice'", $source);
        $this->assertStringContainsString("\$playernotices[] = get_string('integrity:studentnotice'", $source);
        $this->assertStringContainsString('videotrack-player-notice videotrack-inline-notice alert alert-info', $source);
        $this->assertStringContainsString('btn-close videotrack-inline-notice-close ms-2', $source);
        $this->assertStringContainsString("'data-bs-dismiss' => 'alert'", $source);
        $this->assertStringNotContainsString("\$OUTPUT->notification(\$playernoticehtml, 'info', true)", $source);
        $this->assertStringNotContainsString('small text-muted videotrack-integrity-notice', $source);
    }

    /**
     * Request/filter/scope helpers stay outside the report controller.
     */
    public function test_report_support_helpers_are_extracted_from_controller(): void {
        $report = file_get_contents(__DIR__ . '/../report.php');
        $support = file_get_contents(__DIR__ . '/../classes/local/report_support.php');
        $this->assertIsString($report);
        $this->assertIsString($support);

        $this->assertStringContainsString('report_support::optional_time_param(', $report);
        $this->assertStringContainsString('report_support::analytics_scope_condition(', $report);
        $this->assertStringContainsString('report_support::tabs(', $report);
        $this->assertStringContainsString('report_support::user_options(', $report);
        $this->assertStringContainsString('report_support::cluster_reaction_events(', $report);
        $this->assertStringContainsString('report_support::reaction_event_condition(', $report);
        $this->assertStringContainsString('report_support::bookmark_event_condition(', $report);
        $this->assertStringContainsString('report_support::integrity_event_condition(', $report);
        $this->assertStringContainsString('report_support::note_user_condition(', $report);
        $this->assertStringContainsString('report_support::note_event_condition(', $report);
        $this->assertStringContainsString('report_support::state_condition(', $report);
        $this->assertStringContainsString('report_support::segment_user_condition(', $report);
        $this->assertStringContainsString('final class report_support', $support);
        $this->assertStringNotContainsString('function videotrack_report_user_label(', $report);
        $this->assertStringNotContainsString('function videotrack_report_tabs(', $report);
        $this->assertStringNotContainsString('$clusterize = function', $report);
        $this->assertStringNotContainsString(
            '$eventconditions = "videotrackid = :vtid AND isdeleted = 0',
            $report
        );
        $this->assertStringNotContainsString(
            '$bookmarkconditions = "videotrackid = :bookmarkvtid AND isdeleted = 0',
            $report
        );
        $this->assertStringNotContainsString(
            '"videotrackid = :vtid AND {$learnerwhere}",',
            $report
        );
        $this->assertStringNotContainsString(
            '$notewhere = "videotrackid = :vtid AND isdeleted = 0 AND notetype = \'note\'',
            $report
        );
    }

    /**
     * The request controller delegates Analytics presentation to the dedicated presentation helper.
     */
    public function test_analytics_rendering_is_extracted_from_report_controller(): void {
        $report = file_get_contents(__DIR__ . '/../report.php');
        $renderer = file_get_contents(__DIR__ . '/../classes/local/report_view.php');
        $this->assertIsString($report);
        $this->assertIsString($renderer);

        $this->assertStringContainsString('report_view::analytics_heatmap(', $report);
        $this->assertStringContainsString('report_view::analytics_retention(', $report);
        $this->assertStringContainsString('final class report_view', $renderer);
        $this->assertStringNotContainsString('function videotrack_report_render_analytics_heatmap(', $report);
        $this->assertStringNotContainsString('function videotrack_report_render_analytics_retention(', $report);
    }
}
