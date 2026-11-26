@mod @mod_videotrack @mod_videotrack_settings_copy @javascript
Feature: Activity copies retain settings and ordinary recalculation stays available
  In order to reuse a configured video activity
  As an editing teacher
  I need duplication and an unrelated settings save to preserve my choices

  Background:
    Given the following config values are set as admin:
      | playbackspeeds | 0.5,1,1.5 | mod_videotrack |
    And the following config values are set as admin:
      | showuseridentity | email |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | One | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And the following "activities" exist:
      | activity | course | name | type |
      | forum | C1 | Linked Forum | general |
    And the following "activities" exist:
      | activity | course | name | behatproviderfixture | playbackspeed_0_5 | playbackspeed_1 | playbackspeed_1_5 | csvexportfields | csvdelimiter | behatlinkedforum | bookmarksenabled | studentnotesenabled |
      | videotrack | C1 | Copy source | youtube | 0.5 | 1 | 1.5 | coursefullname,email,department | semicolon | Linked Forum | 1 | 1 |
    And I log in as "teacher1"

  Scenario: A real duplicate keeps settings before and after an unrelated form save
    Given I am on "Course 1" course homepage with editing mode "on"
    When I duplicate "Copy source" activity
    Then I should see "Copy source (copy)"
    And the VideoTrack settings in "Copy source (copy)" match "Copy source"
    When I am on the "Copy source (copy)" "videotrack activity editing" page
    And I expand all fieldsets
    And I set the field "name" to "Saved copy"
    And I submit VideoTrack settings with deterministic YouTube duration metadata
    Then the VideoTrack settings in "Saved copy" match "Copy source"

  Scenario: Ordinary completion recalculation has no historical repair checkbox
    When I open the VideoTrack report for "Copy source" and learner "all"
    And I follow "Completion recalculation"
    Then I should see "Completion recalculation"
    And "[name='recoverplayback']" "css_element" should not exist
    And "Recalculate completion" "button" should exist
