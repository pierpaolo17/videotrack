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

/**
 * Video timestamps associated with one teacher-report CSV event row.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_event_timestamps {
    /** @var float|null Representative video timestamp. */
    public readonly ?float $timestamp;

    /** @var float|null First timestamp in the aggregate. */
    public readonly ?float $firsttimestamp;

    /** @var float|null Last timestamp in the aggregate. */
    public readonly ?float $lasttimestamp;

    /**
     * Captures the original three nullable video timestamps for an event row.
     *
     * @param float|null $timestamp Representative video timestamp.
     * @param float|null $firsttimestamp First timestamp in the aggregate.
     * @param float|null $lasttimestamp Last timestamp in the aggregate.
     */
    public function __construct(?float $timestamp, ?float $firsttimestamp, ?float $lasttimestamp) {
        $this->timestamp = $timestamp;
        $this->firsttimestamp = $firsttimestamp;
        $this->lasttimestamp = $lasttimestamp;
    }
}
