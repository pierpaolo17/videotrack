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

namespace mod_videotrack\local;

use context;
use stdClass;

/**
 * Stable activity and viewer context for custom teacher-report CSV rows.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_event_context {
    /** @var stdClass Course record. */
    public readonly stdClass $course;

    /** @var stdClass Activity record. */
    public readonly stdClass $videotrack;

    /** @var array<int, stdClass> Export users keyed by id. */
    public readonly array $usermap;

    /** @var int Course-module id. */
    public readonly int $cmid;

    /** @var context Module context. */
    public readonly context $context;

    /** @var float Video duration in seconds. */
    public readonly float $duration;

    /** @var bool Whether the export is the aggregate overall format. */
    public readonly bool $overall;

    /**
     * Captures the same stable context for every event row in one export.
     *
     * @param stdClass $course Course record.
     * @param stdClass $videotrack Activity record.
     * @param array $usermap Export users keyed by id.
     * @param int $cmid Course-module id.
     * @param context $context Module context.
     * @param float $duration Video duration in seconds.
     * @param bool $overall Whether the export is the aggregate overall format.
     */
    public function __construct(
        stdClass $course,
        stdClass $videotrack,
        array $usermap,
        int $cmid,
        context $context,
        float $duration,
        bool $overall
    ) {
        $this->course = $course;
        $this->videotrack = $videotrack;
        $this->usermap = $usermap;
        $this->cmid = $cmid;
        $this->context = $context;
        $this->duration = $duration;
        $this->overall = $overall;
    }
}
