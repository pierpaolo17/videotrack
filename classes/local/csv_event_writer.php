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
 * Writes custom teacher-report CSV event rows with stable export context.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class csv_event_writer {
    /** @var resource Output stream. */
    private $handle;

    /** @var string CSV delimiter. */
    private readonly string $delimiter;

    /** @var string[] Selected identity/context fields. */
    private readonly array $fields;

    /** @var stdClass Course record. */
    private readonly stdClass $course;

    /** @var stdClass Activity record. */
    private readonly stdClass $videotrack;

    /** @var array<int, stdClass> Export users keyed by id. */
    private readonly array $usermap;

    /** @var int Course-module id. */
    private readonly int $cmid;

    /** @var context Module context. */
    private readonly context $context;

    /** @var float Video duration in seconds. */
    private readonly float $duration;

    /** @var bool Whether the export is the aggregate overall format. */
    private readonly bool $overall;

    /**
     * Creates a writer for one custom CSV export response.
     *
     * @param resource $handle Output stream.
     * @param string $delimiter CSV delimiter.
     * @param string[] $fields Selected identity/context fields.
     * @param stdClass $course Course record.
     * @param stdClass $videotrack Activity record.
     * @param array<int, stdClass> $usermap Export users keyed by id.
     * @param int $cmid Course-module id.
     * @param context $context Module context.
     * @param float $duration Video duration in seconds.
     * @param bool $overall Whether the export is the aggregate overall format.
     */
    public function __construct(
        $handle,
        string $delimiter,
        array $fields,
        stdClass $course,
        stdClass $videotrack,
        array $usermap,
        int $cmid,
        context $context,
        float $duration,
        bool $overall
    ) {
        $this->handle = $handle;
        $this->delimiter = $delimiter;
        $this->fields = $fields;
        $this->course = $course;
        $this->videotrack = $videotrack;
        $this->usermap = $usermap;
        $this->cmid = $cmid;
        $this->context = $context;
        $this->duration = $duration;
        $this->overall = $overall;
    }

    /**
     * Writes one event row, silently skipping an unknown positive user id.
     *
     * @param int $userid User id, or 0 for aggregate rows.
     * @param string $eventtype Localised event type.
     * @param string $reactionlabel Reaction label.
     * @param string $comment Note/comment text.
     * @param float|null $timestamp Representative video timestamp.
     * @param float|null $firsttimestamp First timestamp in the aggregate.
     * @param float|null $lasttimestamp Last timestamp in the aggregate.
     * @param int $count Event count.
     * @param string $created Localised creation time for non-overall exports.
     * @param int $studentcount Distinct student count for overall exports.
     * @return void
     */
    public function write(
        int $userid,
        string $eventtype,
        string $reactionlabel,
        string $comment,
        ?float $timestamp,
        ?float $firsttimestamp,
        ?float $lasttimestamp,
        int $count,
        string $created,
        int $studentcount = 1
    ): void {
        $user = $userid > 0 ? ($this->usermap[$userid] ?? null) : null;
        if ($userid > 0 && !$user) {
            return;
        }

        $row = csv_export::identity_values(
            $this->fields,
            $this->course,
            $this->videotrack,
            $user,
            $userid > 0 ? report_support::user_label($userid, $this->usermap, false) : '',
            $this->cmid,
            $this->context
        );
        $row = array_merge($row, [
            $eventtype,
            $reactionlabel,
            $comment,
            $timestamp === null ? '' : videotrack_format_video_timestamp($timestamp, $this->duration),
            $firsttimestamp === null ? '' : videotrack_format_video_timestamp($firsttimestamp, $this->duration),
            $lasttimestamp === null ? '' : videotrack_format_video_timestamp($lasttimestamp, $this->duration),
            $count,
        ]);
        if ($this->overall) {
            $row[] = $studentcount;
        } else {
            $row[] = $created;
        }
        csv_export::write_row($this->handle, $row, $this->delimiter);
    }
}
