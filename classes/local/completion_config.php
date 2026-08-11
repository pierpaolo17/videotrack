<?php
// This file is part of Moodle - https://moodle.org/
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

use context_module;
use stdClass;

/**
 * Canonical helpers for VideoTrack custom completion configuration.
 *
 * @package    mod_videotrack
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class completion_config {
    /** @var array<int, array<int, bool>> Required-reaction activity ids cached per course for this request. */
    private static array $requiredreactioncoursecache = [];

    /**
     * Returns active reaction ids explicitly required for completion.
     *
     * @param int $videotrackid Activity instance id.
     * @return int[] Sorted reaction ids.
     */
    public static function required_reaction_ids(int $videotrackid): array {
        global $DB;

        $records = $DB->get_records('videotrack_react', [
            'videotrackid' => $videotrackid,
            'requiredforcompletion' => 1,
            'isdeleted' => 0,
        ], 'sortorder ASC, id ASC', 'id');

        return array_map('intval', array_keys($records));
    }

    /**
     * Returns whether the activity has at least one active VideoTrack custom rule.
     *
     * Reaction rules only participate while reactions are enabled. This prevents
     * stale required-reaction definitions from making an activity impossible to complete.
     *
     * @param stdClass $videotrack Activity instance.
     * @param bool|null $hasrequiredreactions Optional preloaded existence of an individually required reaction.
     * @return bool True when a custom completion condition is active.
     */
    public static function has_custom_rules(stdClass $videotrack, ?bool $hasrequiredreactions = null): bool {
        if (!empty($videotrack->completionpercent)) {
            return true;
        }
        if (!empty($videotrack->completionacknowledgement) && acknowledgement::is_enabled($videotrack)) {
            return true;
        }
        if (empty($videotrack->reactionsenabled)) {
            return false;
        }
        if (!empty($videotrack->reactionsrequired) && !empty($videotrack->minreactions)) {
            return true;
        }
        if (!empty($videotrack->requireallreactiontypes)) {
            return true;
        }
        if ($hasrequiredreactions === null) {
            $hasrequiredreactions = self::has_required_reactions((int)$videotrack->id);
        }
        return $hasrequiredreactions;
    }

    /**
     * Returns whether the activity has an active individually required reaction.
     *
     * @param int $videotrackid Activity instance id.
     * @return bool True when at least one active reaction is individually required.
     */
    public static function has_required_reactions(int $videotrackid): bool {
        global $DB;

        return $DB->record_exists('videotrack_react', [
            'videotrackid' => $videotrackid,
            'requiredforcompletion' => 1,
            'isdeleted' => 0,
        ]);
    }

    /**
     * Returns activity ids with individually required reactions, cached once per course/request.
     *
     * get_coursemodule_info() is called once per activity while rebuilding modinfo.
     * Preloading this set avoids an extra reaction query for every VideoTrack whose
     * only possible custom rule is an individually required reaction.
     *
     * @param int $courseid Course id.
     * @return array<int, bool> Set keyed by VideoTrack instance id.
     */
    public static function required_reaction_activity_set(int $courseid): array {
        global $DB;

        if (!array_key_exists($courseid, self::$requiredreactioncoursecache)) {
            $sql = "SELECT DISTINCT reaction.videotrackid
                      FROM {videotrack_react} reaction
                      JOIN {videotrack} vt ON vt.id = reaction.videotrackid
                     WHERE vt.course = :courseid
                       AND reaction.requiredforcompletion = 1
                       AND reaction.isdeleted = 0";
            $ids = array_map('intval', $DB->get_fieldset_sql($sql, ['courseid' => $courseid]));
            self::$requiredreactioncoursecache[$courseid] = array_fill_keys($ids, true);
        }
        return self::$requiredreactioncoursecache[$courseid];
    }

    /**
     * Clears the request-local required-reaction cache after reaction definitions change.
     *
     * @param int|null $courseid Course id, or null to clear all cached courses.
     */
    public static function reset_required_reaction_cache(?int $courseid = null): void {
        if ($courseid === null) {
            self::$requiredreactioncoursecache = [];
            return;
        }
        unset(self::$requiredreactioncoursecache[$courseid]);
    }

    /**
     * Returns human-readable descriptions of the active component conditions.
     *
     * @param stdClass $videotrack Activity instance.
     * @param context_module $context Module context used to format reaction labels.
     * @return string[] Active condition descriptions in display order.
     */
    public static function active_condition_descriptions(stdClass $videotrack, context_module $context): array {
        global $DB;

        $descriptions = [];
        if (!empty($videotrack->completionpercent)) {
            $descriptions[] = get_string(
                'completiondetail:percent',
                'mod_videotrack',
                $videotrack->completionpercent
            );
        }

        $reactiondescriptions = [];
        if (!empty($videotrack->reactionsenabled)) {
            if (!empty($videotrack->reactionsrequired) && !empty($videotrack->minreactions)) {
                $reactiondescriptions[] = get_string(
                    'completiondetail:minreactions',
                    'mod_videotrack',
                    $videotrack->minreactions
                );
            }

            $required = $DB->get_records('videotrack_react', [
                'videotrackid' => $videotrack->id,
                'requiredforcompletion' => 1,
                'isdeleted' => 0,
            ], 'sortorder ASC, id ASC', 'id,label');
            if ($required) {
                $labels = array_map(static function (stdClass $reaction) use ($context): string {
                    return format_string($reaction->label, true, ['context' => $context]);
                }, array_values($required));
                $reactiondescriptions[] = get_string(
                    'completiondetail:requiredreactions',
                    'mod_videotrack',
                    implode(', ', $labels)
                );
            }

            if (!empty($videotrack->requireallreactiontypes)) {
                $reactiondescriptions[] = get_string('completiondetail:allreactiontypes', 'mod_videotrack');
            }
        }
        if ($reactiondescriptions) {
            $reactionlogic = ($videotrack->completionlogic ?? 'and') === 'or'
                ? get_string('logicor', 'mod_videotrack')
                : get_string('logicand', 'mod_videotrack');
            $descriptions[] = get_string('completionreactionrules', 'mod_videotrack')
                . ' — ' . $reactionlogic . ': ' . implode('; ', $reactiondescriptions);
        }

        if (!empty($videotrack->completionacknowledgement) && acknowledgement::is_enabled($videotrack)) {
            $descriptions[] = get_string('completiondetail:acknowledgement', 'mod_videotrack');
        }

        return $descriptions;
    }

    /**
     * Returns a stable signature for settings that can change persisted completion state.
     *
     * @param stdClass $videotrack Activity instance.
     * @return string SHA-256 configuration signature.
     */
    public static function signature(stdClass $videotrack): string {
        global $DB;

        $reactions = $DB->get_records('videotrack_react', [
            'videotrackid' => $videotrack->id,
            'isdeleted' => 0,
        ], 'sortorder ASC, id ASC', 'id,requiredforcompletion');
        $activeids = [];
        $requiredids = [];
        foreach ($reactions as $reaction) {
            $activeids[] = (int)$reaction->id;
            if (!empty($reaction->requiredforcompletion)) {
                $requiredids[] = (int)$reaction->id;
            }
        }

        $payload = [
            'durationseconds' => (float)($videotrack->durationseconds ?? 0),
            'completionpercent' => (int)($videotrack->completionpercent ?? 0),
            'reactionsenabled' => empty($videotrack->reactionsenabled) ? 0 : 1,
            'reactionsrequired' => empty($videotrack->reactionsrequired) ? 0 : 1,
            'minreactions' => (int)($videotrack->minreactions ?? 0),
            'requireallreactiontypes' => empty($videotrack->requireallreactiontypes) ? 0 : 1,
            'completionlogic' => (string)($videotrack->completionlogic ?? 'and'),
            'acknowledgementenabled' => empty($videotrack->acknowledgementenabled) ? 0 : 1,
            'completionacknowledgement' => empty($videotrack->completionacknowledgement) ? 0 : 1,
            'acknowledgementhash' => acknowledgement::statement_hash($videotrack),
            'activeids' => $activeids,
            'requiredids' => $requiredids,
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES));
    }
}
