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

use context_system;
use mod_videotrack\local\report_support;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Behavioural coverage for teacher-report request and scope helpers.
 *
 * @package    mod_videotrack
 * @category   test
 * @copyright  2026 videotrack contributors
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(report_support::class)]
final class report_support_test extends \advanced_testcase {
    /**
     * Date-only report filters reject malformed and impossible calendar values.
     */
    public function test_date_to_timestamp_rejects_invalid_values(): void {
        $this->assertSame(0, report_support::date_to_timestamp(''));
        $this->assertSame(0, report_support::date_to_timestamp('2026-02-30'));
        $this->assertSame(0, report_support::date_to_timestamp('17-08-2026'));
        $this->assertSame(
            make_timestamp(2026, 8, 17, 0, 0, 0),
            report_support::date_to_timestamp('2026-08-17')
        );
        $this->assertSame(
            make_timestamp(2026, 8, 17, 23, 59, 59),
            report_support::date_to_timestamp('2026-08-17', true)
        );
    }

    /**
     * Duration filters keep the structured hours/minutes/seconds accessibility contract.
     */
    public function test_duration_filter_preserves_structured_controls(): void {
        $markup = report_support::duration_filter('timefrom', 'From', 3661.0, true);

        $this->assertStringContainsString('name="timefrom_hours"', $markup);
        $this->assertStringContainsString('value="1"', $markup);
        $this->assertStringContainsString('name="timefrom_minutes"', $markup);
        $this->assertStringContainsString('name="timefrom_seconds"', $markup);
        $this->assertStringContainsString('role="group"', $markup);
        $this->assertStringContainsString('aria-labelledby="id_timefrom_group_label"', $markup);
    }

    /**
     * User labels retain privacy behaviour when email visibility is disabled.
     */
    public function test_user_label_respects_email_visibility(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user([
            'firstname' => 'Ada',
            'lastname' => 'Lovelace',
            'email' => 'ada@example.invalid',
        ]);

        $withoutemail = report_support::user_label((int)$user->id, [(int)$user->id => $user], false);
        $withemail = report_support::user_label((int)$user->id, [(int)$user->id => $user], true);

        $this->assertStringContainsString('Ada', $withoutemail);
        $this->assertStringNotContainsString('ada@example.invalid', $withoutemail);
        $this->assertStringContainsString('ada@example.invalid', $withemail);
        $this->assertSame('#99', report_support::user_label(99, [], false));
    }

    /**
     * Report tabs keep learner-only and full-report visibility separated.
     */
    public function test_tabs_preserve_capability_dependent_set(): void {
        $learner = report_support::tabs(123, true, false, false, false, ['userid' => 7]);
        $aggregateassistant = report_support::tabs(123, false, true, false, false, ['userid' => 7]);
        $teacher = report_support::tabs(123, true, true, true, true, ['userid' => 7]);

        $this->assertSame(['student'], array_column($learner, 'id'));
        $this->assertSame(['cumulative', 'analytics'], array_column($aggregateassistant, 'id'));
        $this->assertSame(
            ['student', 'cumulative', 'analytics', 'export', 'recalculate'],
            array_column($teacher, 'id')
        );
    }

    /**
     * Empty Analytics/acknowledgement scope sets must never broaden the SQL result.
     */
    public function test_empty_scope_conditions_remain_deny_all(): void {
        [$analyticssql, $analyticsparams] = report_support::analytics_scope_condition([], 'analytics', 7);
        [$acksql, $ackparams] = report_support::acknowledgement_scope_condition([], 'ack', 7);

        $this->assertSame('1 = 0', $analyticssql);
        $this->assertSame([], $analyticsparams);
        $this->assertSame('1 = 0', $acksql);
        $this->assertSame([], $ackparams);
    }

    /**
     * Reaction Analytics filtering preserves scope, event type and optional provider selection.
     */
    public function test_analytics_reaction_condition_preserves_scope_and_provider_filter(): void {
        [$conditions, $params] = report_support::analytics_reaction_condition(
            'videotrackid = :analyticsreactionvt0 AND userid = :analyticsreactionlearner0',
            ['analyticsreactionvt0' => 42, 'analyticsreactionlearner0' => 7],
            'provider-video-123'
        );

        $this->assertSame(
            "(videotrackid = :analyticsreactionvt0 AND userid = :analyticsreactionlearner0)" .
                " AND isdeleted = 0 AND (notetype = '' OR notetype IS NULL)" .
                ' AND videoid = :analyticsreactionvideoid',
            $conditions
        );
        $this->assertSame([
            'analyticsreactionvt0' => 42,
            'analyticsreactionlearner0' => 7,
            'analyticsreactionvideoid' => 'provider-video-123',
        ], $params);

        [$minimalconditions, $minimalparams] = report_support::analytics_reaction_condition(
            'videotrackid = :analyticsreactionvt0',
            ['analyticsreactionvt0' => 42],
            ''
        );
        $this->assertSame(
            "(videotrackid = :analyticsreactionvt0) AND isdeleted = 0 " .
                "AND (notetype = '' OR notetype IS NULL)",
            $minimalconditions
        );
        $this->assertSame(['analyticsreactionvt0' => 42], $minimalparams);
    }

    /**
     * Bookmark Analytics filtering preserves scope, event type and optional provider selection.
     */
    public function test_analytics_bookmark_condition_preserves_scope_and_provider_filter(): void {
        [$conditions, $params] = report_support::analytics_bookmark_condition(
            'videotrackid = :analyticsbookmarkvt0 AND userid = :analyticsbookmarklearner0',
            ['analyticsbookmarkvt0' => 42, 'analyticsbookmarklearner0' => 7],
            'provider-video-123'
        );

        $this->assertSame(
            "(videotrackid = :analyticsbookmarkvt0 AND userid = :analyticsbookmarklearner0)" .
                " AND isdeleted = 0 AND notetype = 'bookmark'" .
                ' AND videoid = :analyticsbookmarkvideoid',
            $conditions
        );
        $this->assertSame([
            'analyticsbookmarkvt0' => 42,
            'analyticsbookmarklearner0' => 7,
            'analyticsbookmarkvideoid' => 'provider-video-123',
        ], $params);

        [$minimalconditions, $minimalparams] = report_support::analytics_bookmark_condition(
            'videotrackid = :analyticsbookmarkvt0',
            ['analyticsbookmarkvt0' => 42],
            ''
        );
        $this->assertSame(
            "(videotrackid = :analyticsbookmarkvt0) AND isdeleted = 0 AND notetype = 'bookmark'",
            $minimalconditions
        );
        $this->assertSame(['analyticsbookmarkvt0' => 42], $minimalparams);
    }

    /**
     * Standard reaction-event filters preserve learner scope and optional bounds.
     */
    public function test_reaction_event_condition_preserves_filters_and_scope(): void {
        [$conditions, $params] = report_support::reaction_event_condition(
            42,
            'userid IN (:learnerone, :learnertwo)',
            ['learnerone' => 7, 'learnertwo' => 8],
            7,
            3,
            12.5,
            90.0
        );

        $this->assertSame(
            "videotrackid = :vtid AND isdeleted = 0 AND (notetype = '' OR notetype IS NULL)" .
                ' AND userid IN (:learnerone, :learnertwo)' .
                ' AND userid = :uid AND reactionid = :rid' .
                ' AND videotime >= :timefrom AND videotime <= :timeto',
            $conditions
        );
        $this->assertSame([
            'vtid' => 42,
            'learnerone' => 7,
            'learnertwo' => 8,
            'uid' => 7,
            'rid' => 3,
            'timefrom' => 12.5,
            'timeto' => 90.0,
        ], $params);

        [$minimalconditions, $minimalparams] = report_support::reaction_event_condition(
            42,
            'userid = :learner',
            ['learner' => 7],
            0,
            0,
            null,
            null
        );
        $this->assertSame(
            "videotrackid = :vtid AND isdeleted = 0 AND (notetype = '' OR notetype IS NULL)" .
                ' AND userid = :learner',
            $minimalconditions
        );
        $this->assertSame(['vtid' => 42, 'learner' => 7], $minimalparams);
    }

    /**
     * Bookmark-event filters preserve learner scope and optional bounds.
     */
    public function test_bookmark_event_condition_preserves_filters_and_scope(): void {
        [$conditions, $params] = report_support::bookmark_event_condition(
            42,
            'userid IN (:learnerone, :learnertwo)',
            ['learnerone' => 7, 'learnertwo' => 8],
            7,
            12.5,
            90.0
        );

        $this->assertSame(
            "videotrackid = :bookmarkvtid AND isdeleted = 0 AND notetype = 'bookmark'" .
                ' AND userid IN (:learnerone, :learnertwo)' .
                ' AND userid = :bookmarkuserid' .
                ' AND videotime >= :bookmarktimefrom AND videotime <= :bookmarktimeto',
            $conditions
        );
        $this->assertSame([
            'bookmarkvtid' => 42,
            'learnerone' => 7,
            'learnertwo' => 8,
            'bookmarkuserid' => 7,
            'bookmarktimefrom' => 12.5,
            'bookmarktimeto' => 90.0,
        ], $params);

        [$minimalconditions, $minimalparams] = report_support::bookmark_event_condition(
            42,
            'userid = :learner',
            ['learner' => 7],
            0,
            null,
            null
        );
        $this->assertSame(
            "videotrackid = :bookmarkvtid AND isdeleted = 0 AND notetype = 'bookmark' AND userid = :learner",
            $minimalconditions
        );
        $this->assertSame(['bookmarkvtid' => 42, 'learner' => 7], $minimalparams);
    }

    /**
     * Integrity-event filters preserve learner scope and optional bounds.
     */
    public function test_integrity_event_condition_preserves_filters_and_scope(): void {
        [$conditions, $params] = report_support::integrity_event_condition(
            42,
            'userid IN (:learnerone, :learnertwo)',
            ['learnerone' => 7, 'learnertwo' => 8],
            7,
            12.5,
            90.0
        );

        $this->assertSame(
            'videotrackid = :integrityvtid AND userid IN (:learnerone, :learnertwo)' .
                ' AND userid = :integrityuserid' .
                ' AND videotime >= :integritytimefrom AND videotime <= :integritytimeto',
            $conditions
        );
        $this->assertSame([
            'integrityvtid' => 42,
            'learnerone' => 7,
            'learnertwo' => 8,
            'integrityuserid' => 7,
            'integritytimefrom' => 12.5,
            'integritytimeto' => 90.0,
        ], $params);

        [$minimalconditions, $minimalparams] = report_support::integrity_event_condition(
            42,
            'userid = :learner',
            ['learner' => 7],
            0,
            null,
            null
        );
        $this->assertSame(
            'videotrackid = :integrityvtid AND userid = :learner',
            $minimalconditions
        );
        $this->assertSame(['integrityvtid' => 42, 'learner' => 7], $minimalparams);
    }

    /**
     * Note-user discovery preserves learner scope and optional user filtering.
     */
    public function test_note_user_condition_preserves_scope_and_optional_user(): void {
        [$conditions, $params] = report_support::note_user_condition(
            42,
            'userid IN (:learnerone, :learnertwo)',
            ['learnerone' => 7, 'learnertwo' => 8],
            7
        );

        $this->assertSame(
            "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note'" .
                ' AND userid IN (:learnerone, :learnertwo) AND userid = :uid',
            $conditions
        );
        $this->assertSame([
            'vtid' => 42,
            'learnerone' => 7,
            'learnertwo' => 8,
            'uid' => 7,
        ], $params);

        [$minimalconditions, $minimalparams] = report_support::note_user_condition(
            42,
            'userid = :learner',
            ['learner' => 7],
            0
        );
        $this->assertSame(
            "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note' AND userid = :learner",
            $minimalconditions
        );
        $this->assertSame(['vtid' => 42, 'learner' => 7], $minimalparams);
    }

    /**
     * Personal-note event filters preserve scope, learner filtering and creation-time bounds.
     */
    public function test_note_event_condition_preserves_scope_user_and_creation_bounds(): void {
        [$conditions, $params] = report_support::note_event_condition(
            42,
            'userid IN (:learnerone, :learnertwo)',
            ['learnerone' => 7, 'learnertwo' => 8],
            7,
            100,
            200
        );

        $this->assertSame(
            "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note'" .
                ' AND userid IN (:learnerone, :learnertwo) AND userid = :uid' .
                ' AND timecreated >= :notecreatedfrom AND timecreated <= :notecreatedto',
            $conditions
        );
        $this->assertSame([
            'vtid' => 42,
            'learnerone' => 7,
            'learnertwo' => 8,
            'uid' => 7,
            'notecreatedfrom' => 100,
            'notecreatedto' => 200,
        ], $params);

        [$minimalconditions, $minimalparams] = report_support::note_event_condition(
            42,
            'userid = :learner',
            ['learner' => 7],
            0,
            0,
            0
        );
        $this->assertSame(
            "videotrackid = :vtid AND isdeleted = 0 AND notetype = 'note' AND userid = :learner",
            $minimalconditions
        );
        $this->assertSame(['vtid' => 42, 'learner' => 7], $minimalparams);
    }

    /**
     * State-row filters preserve learner scope and optional user filtering.
     */
    public function test_state_condition_preserves_scope_and_optional_user(): void {
        [$conditions, $params] = report_support::state_condition(
            42,
            'userid IN (:learnerone, :learnertwo)',
            ['learnerone' => 7, 'learnertwo' => 8],
            7
        );

        $this->assertSame(
            'videotrackid = :svtid AND userid IN (:learnerone, :learnertwo) AND userid = :suid',
            $conditions
        );
        $this->assertSame([
            'svtid' => 42,
            'learnerone' => 7,
            'learnertwo' => 8,
            'suid' => 7,
        ], $params);

        [$minimalconditions, $minimalparams] = report_support::state_condition(
            42,
            'userid = :learner',
            ['learner' => 7],
            0
        );
        $this->assertSame('videotrackid = :svtid AND userid = :learner', $minimalconditions);
        $this->assertSame(['svtid' => 42, 'learner' => 7], $minimalparams);
    }

    /**
     * Segment-user discovery preserves the canonical learner scope and parameter names.
     */
    public function test_segment_user_condition_preserves_scope(): void {
        [$conditions, $params] = report_support::segment_user_condition(
            42,
            'userid IN (:learnerone, :learnertwo)',
            ['learnerone' => 7, 'learnertwo' => 8]
        );

        $this->assertSame(
            'videotrackid = :vtid AND userid IN (:learnerone, :learnertwo)',
            $conditions
        );
        $this->assertSame([
            'vtid' => 42,
            'learnerone' => 7,
            'learnertwo' => 8,
        ], $params);

        [$minimalconditions, $minimalparams] = report_support::segment_user_condition(
            42,
            'userid = :learner',
            ['learner' => 7]
        );
        $this->assertSame('videotrackid = :vtid AND userid = :learner', $minimalconditions);
        $this->assertSame(['vtid' => 42, 'learner' => 7], $minimalparams);
    }

    /**
     * User options preserve source-group priority, deduplicate ids and omit missing users.
     */
    public function test_user_options_preserve_source_priority_and_privacy(): void {
        $this->resetAfterTest();
        $first = $this->getDataGenerator()->create_user([
            'firstname' => 'First',
            'lastname' => 'Learner',
            'email' => 'first@example.invalid',
        ]);
        $second = $this->getDataGenerator()->create_user([
            'firstname' => 'Second',
            'lastname' => 'Learner',
            'email' => 'second@example.invalid',
        ]);
        $usermap = [
            (int)$first->id => $first,
            (int)$second->id => $second,
        ];

        $options = report_support::user_options(
            [[(int)$second->id, 999], [(int)$first->id, (int)$second->id]],
            $usermap,
            false
        );

        $this->assertSame([0, (int)$second->id, (int)$first->id], array_keys($options));
        $this->assertStringContainsString('Second', $options[(int)$second->id]);
        $this->assertStringNotContainsString('second@example.invalid', $options[(int)$second->id]);
        $this->assertArrayNotHasKey(999, $options);
    }

    /**
     * Reaction clustering preserves per-type windows, unique-student counts and report sorting.
     */
    public function test_cluster_reaction_events_preserves_report_semantics(): void {
        $reactionone = (object)['id' => 1, 'label' => 'Like'];
        $reactiontwo = (object)['id' => 2, 'label' => 'Question'];
        $events = [
            (object)['reactionid' => 2, 'reactionlabel' => 'Question', 'userid' => 10, 'videotime' => 5.0],
            (object)['reactionid' => 1, 'reactionlabel' => 'Like', 'userid' => 10, 'videotime' => 10.0],
            (object)['reactionid' => 1, 'reactionlabel' => 'Like', 'userid' => 11, 'videotime' => 15.0],
            (object)['reactionid' => 2, 'reactionlabel' => 'Question', 'userid' => 11, 'videotime' => 20.0],
        ];
        $limitreached = false;

        $clusters = report_support::cluster_reaction_events(
            $events,
            10,
            'type',
            [1 => $reactionone, 2 => $reactiontwo],
            'reaction',
            context_system::instance(),
            $limitreached
        );

        $this->assertFalse($limitreached);
        $this->assertCount(3, $clusters);
        $this->assertSame('Like', $clusters[0]['reactionlabel']);
        $this->assertSame(2, $clusters[0]['count']);
        $this->assertSame(2, $clusters[0]['students']);
        $this->assertSame(10.0, $clusters[0]['first']);
        $this->assertSame(15.0, $clusters[0]['last']);
        $this->assertSame(12.5, $clusters[0]['timestamp']);
        $this->assertSame($reactionone, $clusters[0]['reaction']);
        $this->assertSame(['Question', 'Question'], array_column(array_slice($clusters, 1), 'reactionlabel'));
    }
}
