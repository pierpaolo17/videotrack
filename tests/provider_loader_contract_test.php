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
 * Static contracts for external provider SDK loaders.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversNothing]
final class provider_loader_contract_test extends advanced_testcase {
    /**
     * Vimeo loaders must use RequireJS without mutating the global AMD define function.
     */
    public function test_vimeo_loaders_use_requirejs_without_global_define_mutation(): void {
        foreach (['form/duration.js', 'vimeo_player.js'] as $filename) {
            $source = file_get_contents(__DIR__ . '/../amd/src/' . $filename);
            $this->assertIsString($source);
            $this->assertStringContainsString("window.requirejs || window.require", $source);
            $this->assertStringContainsString("loader([vimeoUrl]", $source);
            $this->assertStringContainsString('forgetRequireModule(loader, vimeoUrl)', $source);
            $this->assertStringContainsString('loader.undef(moduleId)', $source);
            $this->assertStringNotContainsString('window.define = undefined', $source);
            $this->assertStringNotContainsString('amdDefine', $source);
        }
    }

    /**
     * Provider load failures must not poison later duration or Vimeo runtime attempts.
     */
    public function test_provider_loader_promises_reset_after_rejection(): void {
        $duration = file_get_contents(__DIR__ . '/../amd/src/form/duration.js');
        $vimeo = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($duration);
        $this->assertIsString($vimeo);

        $this->assertStringContainsString('youtubeApiPromise = null;', $duration);
        $this->assertStringContainsString('vimeoApiPromise = null;', $duration);
        $this->assertStringContainsString('vimeoSdkPromise = null;', $vimeo);
        $this->assertStringContainsString('cleanup(true);', $duration);
    }

    /**
     * RequireJS Vimeo imports must be consumed as Player constructors.
     */
    public function test_vimeo_requirejs_result_is_used_as_player_constructor(): void {
        $duration = file_get_contents(__DIR__ . '/../amd/src/form/duration.js');
        $vimeo = file_get_contents(__DIR__ . '/../amd/src/vimeo_player.js');
        $this->assertIsString($duration);
        $this->assertIsString($vimeo);

        $this->assertStringContainsString('loadVimeoApi().then(function(Player)', $duration);
        $this->assertStringContainsString('var player = new Player(iframe);', $duration);
        $this->assertStringContainsString('function buildPlayer(VimeoPlayer)', $vimeo);
        $this->assertStringContainsString('player = new VimeoPlayer(iframe);', $vimeo);
        $this->assertStringContainsString('player = new VimeoPlayer(container, {', $vimeo);
    }
}
