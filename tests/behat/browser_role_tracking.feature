@mod @mod_videotrack @mod_videotrack_browser_role_tracking @javascript
Feature: Staff preview and dual-role browser playback keep learner evidence separate
  In order to preserve accurate viewing evidence independently from report permissions
  As a course teacher who may also hold a learner role
  I need preview to stay untracked while explicit learner participation earns completion

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Preview | Teacher | teacher1@example.com |
      | dual1 | Participating | Teacher | dual1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1 | 0 | 1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | dual1 | C1 | student |
    And the following "role assigns" exist:
      | user | role | contextlevel | reference |
      | dual1 | editingteacher | Course | C1 |

  Scenario: HTML5 preview remains untracked and a dual-role learner earns completion
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | behatproviderfixture | durationseconds | autoplay | resumeplayback | loopenabled | randomfocuspauses | pauseonfocusloss | allowseekforward | allowseekbackward | allowplaybackratechange | maxplaybackrate | blockedseekplaybackrate | playbackspeed_1 | completion | completionview | completionpercent | acknowledgementenabled | completionacknowledgement |
      | videotrack | C1 | HTML5 role tracking | 1 |  | 60 | 0 | 0 | 0 | 0 | 0 | 1 | 1 | 0 | 100 | 100 | 1 | 2 | 0 | 5 | 0 | 0 |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I click on "HTML5 role tracking" "link"
    Then the VideoTrack HTML5 media is ready
    And the VideoTrack learner tracking policy is "disabled"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack HTML5 media time is between "5" and "10"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "paused"
    And preview creates no learner tracking for "teacher1" in "HTML5 role tracking"
    When I am on "Course 1" course homepage
    And I click on "HTML5 role tracking" "link"
    Then the VideoTrack learner tracking policy is "disabled"
    And preview creates no learner tracking for "teacher1" in "HTML5 role tracking"
    When I log out
    Given I log in as "dual1"
    And I am on "Course 1" course homepage
    When I click on "HTML5 role tracking" "link"
    Then the VideoTrack HTML5 media is ready
    And the VideoTrack learner tracking policy is "enabled"
    And no validated playback exists for "dual1" in "HTML5 role tracking"
    And Moodle completion for "dual1" in "HTML5 role tracking" is "incomplete"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack HTML5 media time is between "5" and "10"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "paused"
    And the playback credit window for "dual1" in "HTML5 role tracking" is closed by an accepted pause
    And browser playback completes Moodle tracking for "dual1" in "HTML5 role tracking"
    When I am on "Course 1" course homepage
    And I click on "HTML5 role tracking" "link"
    Then Moodle completion for "dual1" in "HTML5 role tracking" is "complete"
    And preview creates no learner tracking for "teacher1" in "HTML5 role tracking"

  Scenario: YOUTUBE preview remains untracked and a dual-role learner earns completion
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | behatproviderfixture | durationseconds | autoplay | resumeplayback | loopenabled | randomfocuspauses | pauseonfocusloss | allowseekforward | allowseekbackward | allowplaybackratechange | maxplaybackrate | blockedseekplaybackrate | playbackspeed_1 | completion | completionview | completionpercent | acknowledgementenabled | completionacknowledgement |
      | videotrack | C1 | YOUTUBE role tracking |  | youtube | 60 | 0 | 0 | 0 | 0 | 0 | 1 | 1 | 0 | 100 | 100 | 1 | 2 | 0 | 5 | 0 | 0 |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I click on "YOUTUBE role tracking" "link"
    Then the deterministic VideoTrack "youtube" provider is ready
    And the VideoTrack learner tracking policy is "disabled"
    When I play the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "playing"
    And the "youtube" provider time is between "5" and "10"
    When I pause the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "paused"
    And preview creates no learner tracking for "teacher1" in "YOUTUBE role tracking"
    When I am on "Course 1" course homepage
    And I click on "YOUTUBE role tracking" "link"
    Then the VideoTrack learner tracking policy is "disabled"
    And preview creates no learner tracking for "teacher1" in "YOUTUBE role tracking"
    When I log out
    Given I log in as "dual1"
    And I am on "Course 1" course homepage
    When I click on "YOUTUBE role tracking" "link"
    Then the deterministic VideoTrack "youtube" provider is ready
    And the VideoTrack learner tracking policy is "enabled"
    And no validated playback exists for "dual1" in "YOUTUBE role tracking"
    And Moodle completion for "dual1" in "YOUTUBE role tracking" is "incomplete"
    When I play the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "playing"
    And the "youtube" provider time is between "5" and "10"
    When I pause the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "paused"
    And the playback credit window for "dual1" in "YOUTUBE role tracking" is closed by an accepted pause
    And browser playback completes Moodle tracking for "dual1" in "YOUTUBE role tracking"
    When I am on "Course 1" course homepage
    And I click on "YOUTUBE role tracking" "link"
    Then Moodle completion for "dual1" in "YOUTUBE role tracking" is "complete"
    And preview creates no learner tracking for "teacher1" in "YOUTUBE role tracking"

  Scenario: VIMEO preview remains untracked and a dual-role learner earns completion
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | behatproviderfixture | durationseconds | autoplay | resumeplayback | loopenabled | randomfocuspauses | pauseonfocusloss | allowseekforward | allowseekbackward | allowplaybackratechange | maxplaybackrate | blockedseekplaybackrate | playbackspeed_1 | completion | completionview | completionpercent | acknowledgementenabled | completionacknowledgement |
      | videotrack | C1 | VIMEO role tracking |  | vimeo | 60 | 0 | 0 | 0 | 0 | 0 | 1 | 1 | 0 | 100 | 100 | 1 | 2 | 0 | 5 | 0 | 0 |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I click on "VIMEO role tracking" "link"
    Then the deterministic VideoTrack "vimeo" provider is ready
    And the VideoTrack learner tracking policy is "disabled"
    When I play the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "playing"
    And the "vimeo" provider time is between "5" and "10"
    When I pause the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "paused"
    And preview creates no learner tracking for "teacher1" in "VIMEO role tracking"
    When I am on "Course 1" course homepage
    And I click on "VIMEO role tracking" "link"
    Then the VideoTrack learner tracking policy is "disabled"
    And preview creates no learner tracking for "teacher1" in "VIMEO role tracking"
    When I log out
    Given I log in as "dual1"
    And I am on "Course 1" course homepage
    When I click on "VIMEO role tracking" "link"
    Then the deterministic VideoTrack "vimeo" provider is ready
    And the VideoTrack learner tracking policy is "enabled"
    And no validated playback exists for "dual1" in "VIMEO role tracking"
    And Moodle completion for "dual1" in "VIMEO role tracking" is "incomplete"
    When I play the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "playing"
    And the "vimeo" provider time is between "5" and "10"
    When I pause the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "paused"
    And the playback credit window for "dual1" in "VIMEO role tracking" is closed by an accepted pause
    And browser playback completes Moodle tracking for "dual1" in "VIMEO role tracking"
    When I am on "Course 1" course homepage
    And I click on "VIMEO role tracking" "link"
    Then Moodle completion for "dual1" in "VIMEO role tracking" is "complete"
    And preview creates no learner tracking for "teacher1" in "VIMEO role tracking"
