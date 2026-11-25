@mod @mod_videotrack @mod_videotrack_youtube_report_replay @javascript
Feature: YouTube report replay keeps the actual provider playback state
  In order to review a learner reaction without false seek recovery
  As a teacher or learner
  I need an explicit replay to keep playing normally after its programmatic seek

  Background:
    Given the following config values are set as admin:
      | heartbeatinterval | 5 | mod_videotrack |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student1@example.com |
      | teacher1 | Teacher | One | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1 | 0 | 1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
      | teacher1 | C1 | editingteacher |
    And the following "activities" exist:
      | activity | course | name | behatproviderfixture | durationseconds | autoplay | resumeplayback | randomfocuspauses | allowseekforward | allowseekbackward | allowplaybackratechange | blockedseekplaybackrate | reactionsenabled | completion | completionview |
      | videotrack | C1 | YouTube report fragment | youtube | 60 | 0 | 0 | 0 | 0 | 1 | 1 | 50 | 1 | 0 | 0 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I click on "YouTube report fragment" "link"
    And the deterministic VideoTrack "youtube" provider is ready
    And I play the deterministic VideoTrack "youtube" provider
    And I watch the VideoTrack player through "8" seconds
    And I pause the deterministic VideoTrack "youtube" provider
    And the playback credit window for "student1" in "YouTube report fragment" is closed by an accepted pause
    And I click on "Test reaction" "button"
    And I click on ".videotrack-student-section-reactions > summary" "css_element"
    And I should see "Test reaction" in the "#videotrack-my-reactions" "css_element"
    And I log out

  Scenario: Teacher replay does not bounce or activate the forbidden-seek speed
    Given I log in as "teacher1"
    When I open the VideoTrack report for "YouTube report fragment" and learner "student1"
    And I click on ".videotrack-replay-inline a" "css_element"
    Then the deterministic VideoTrack "youtube" provider is ready
    And the VideoTrack learner tracking policy is "disabled"
    And the "youtube" provider playback is "playing"
    When I watch the VideoTrack player through "6" seconds
    Then the VideoTrack playback rate is "1"
    When I pause the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "paused"
    And preview creates no learner tracking for "teacher1" in "YouTube report fragment"

  Scenario: Learner report replay retains accepted viewing and a real pause
    Given I log in as "student1"
    When I open the VideoTrack report for "YouTube report fragment" and learner "student1"
    And I click on ".videotrack-replay-inline a" "css_element"
    Then the deterministic VideoTrack "youtube" provider is ready
    And the VideoTrack learner tracking policy is "enabled"
    And the "youtube" provider playback is "playing"
    When I watch the VideoTrack player through "15" seconds
    Then the VideoTrack playback rate is "1"
    When I pause the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "paused"
    And the playback credit window for "student1" in "YouTube report fragment" is closed by an accepted pause
    And the watched VideoTrack progress for "student1" in "YouTube report fragment" is contiguous and retained
