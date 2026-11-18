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

use mod_videotrack\form\microsoft_probe_form;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Native form, CSRF and administration-tree boundaries for the web probe.
 *
 * No test creates a connected OAuth account or contacts Microsoft.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(microsoft_probe_form::class)]
final class microsoft_probe_form_test extends \advanced_testcase {
    /**
     * Start each case with an isolated administrator request.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        $this->setAdminUser();
    }

    /**
     * Drive-only and video targets preserve the supported opaque-id alphabet.
     */
    public function test_valid_identifiers_and_optional_item(): void {
        $form = new microsoft_probe_form();
        foreach (['b!Drive_1-2', str_repeat('A', 256)] as $driveid) {
            foreach (['', 'Video_123-!'] as $itemid) {
                $this->assertSame([], $form->validation(compact('driveid', 'itemid'), []));
            }
        }
    }

    /**
     * Paths, URLs, separators, Unicode and overlong drive ids fail locally.
     */
    public function test_invalid_drive_ids_are_not_repaired(): void {
        $form = new microsoft_probe_form();
        foreach (['', 'https://example.org/share', '../drive', 'drive/item', 'a?x=1', 'a b', 'è', str_repeat('A', 257)] as $id) {
            $this->assertSame(['driveid'], array_keys($form->validation(['driveid' => $id, 'itemid' => 'video'], [])));
        }
    }

    /**
     * An invalid item identifies its own field and cannot change the drive.
     */
    public function test_invalid_item_has_a_field_error(): void {
        $form = new microsoft_probe_form();
        foreach (['/content', 'https://example.org/video', 'a#b', str_repeat('A', 257)] as $id) {
            $this->assertSame(['itemid'], array_keys($form->validation(['driveid' => 'drive', 'itemid' => $id], [])));
        }
        $this->assertSame(['driveid', 'itemid'], array_keys($form->validation(['driveid' => '/', 'itemid' => '/'], [])));
    }

    /**
     * Opening the page with GET fields, even a form marker, is not a submission.
     */
    public function test_get_cannot_submit_a_probe(): void {
        microsoft_probe_form::mock_submit(['driveid' => 'drive', 'itemid' => 'video'], [], 'get');
        $form = new microsoft_probe_form(null, null, 'post');
        $this->assertFalse($form->is_submitted());
        $this->assertNull($form->get_data());
    }

    /**
     * Core rejects a forged session token before submitted fields can be read.
     */
    public function test_invalid_sesskey_is_rejected(): void {
        microsoft_probe_form::mock_submit(['driveid' => 'drive']);
        $_POST['sesskey'] = 'invalid-session-marker';
        $_GET = [];
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidsesskey', 'error'));
        new microsoft_probe_form();
    }

    /**
     * Valid POST data survives without silently normalising the target identity.
     */
    public function test_valid_post_preserves_target(): void {
        microsoft_probe_form::mock_submit(['driveid' => ' b!Drive_1-2 ', 'itemid' => 'Video_123-!']);
        $form = new microsoft_probe_form();
        $data = $form->get_data();
        $this->assertNotNull($data);
        $this->assertSame('b!Drive_1-2', $data->driveid);
        $this->assertSame('Video_123-!', $data->itemid);
    }

    /**
     * Server-side validation rejects an invalid POST instead of returning a target.
     */
    public function test_invalid_post_returns_no_probe_data(): void {
        microsoft_probe_form::mock_submit(['driveid' => 'drive', 'itemid' => 'https://example.org/private']);
        $form = new microsoft_probe_form();
        $this->assertNull($form->get_data());
    }

    /**
     * The registered administration node excludes course teachers and guests.
     */
    public function test_admin_node_requires_site_configuration(): void {
        global $CFG;

        require_once($CFG->libdir . '/adminlib.php');
        $node = admin_get_root(true, false)->locate('mod_videotrack_providercheck');
        $this->assertInstanceOf(\admin_externalpage::class, $node);
        $this->assertTrue($node->check_access());
        $course = $this->getDataGenerator()->create_course();
        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');
        $this->setUser($teacher);
        $this->assertFalse($node->check_access());
        $this->setGuestUser();
        $this->assertFalse($node->check_access());
    }

    /**
     * Keep the controller's permission and CSRF checks before network-capable code.
     */
    public function test_controller_checks_precede_probe(): void {
        $source = file_get_contents(__DIR__ . '/../provider_check.php');
        $this->assertStringContainsString("require_capability('moodle/site:config', context_system::instance());", $source);
        $this->assertLessThan(strpos($source, 'configuration_status('), strpos($source, 'require_capability('));
        $this->assertStringContainsString("microsoft_probe_form(\$PAGE->url, null, 'post')", $source);
        $this->assertLessThan(strpos($source, 'require_sesskey();'), strpos($source, '$form->get_data()'));
        $this->assertLessThan(strpos($source, '$probe->run('), strpos($source, 'require_sesskey();'));
        $this->assertStringContainsString("s(json_encode(\$result,", $source);
        $this->assertStringContainsString('$PAGE->set_cacheable(false);', $source);
    }
}
