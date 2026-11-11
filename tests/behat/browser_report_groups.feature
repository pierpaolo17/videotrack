@mod @mod_videotrack @mod_videotrack_browser_report_groups @javascript
Feature: Separate-group reports expose only browser-earned evidence in the viewer scope
  In order to protect learner data independently from report capabilities
  As a report viewer
  I need membership and all-groups permission to constrain selectors and direct learner requests

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Inside | Learner | student1@example.com |
      | student2 | Outside | Learner | student2@example.com |
      | reviewer1 | Group | Reviewer | reviewer1@example.com |
      | reviewer2 | Ungrouped | Reviewer | reviewer2@example.com |
      | reviewer3 | Full | Reviewer | reviewer3@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | groupmode | groupmodeforce |
      | Course 1 | C1 | 0 | 1 | 1 | 1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
      | student2 | C1 | student |
      | reviewer1 | C1 | teacher |
      | reviewer2 | C1 | teacher |
      | reviewer3 | C1 | editingteacher |
    And the following "permission overrides" exist:
      | capability | permission | role | contextlevel | reference |
      | moodle/site:accessallgroups | Prevent | teacher | Course | C1 |
      | moodle/site:accessallgroups | Allow | editingteacher | Course | C1 |
    And the following "groups" exist:
      | name | course | idnumber |
      | Group A | C1 | A |
      | Group B | C1 | B |
    And the following "group members" exist:
      | group | user |
      | A | student1 |
      | A | reviewer1 |
      | B | student2 |
    And the following "activities" exist:
      | activity | course | name | behathtml5fixture | behatproviderfixture | durationseconds | grade | autoplay | resumeplayback | loopenabled | randomfocuspauses | pauseonfocusloss | allowseekforward | allowseekbackward | allowplaybackratechange | maxplaybackrate | blockedseekplaybackrate | playbackspeed_1 | completion | completionview | completionpercent | acknowledgementenabled | completionacknowledgement | groupmode |
      | videotrack | C1 | Grouped viewing | 1 |  | 60 | 100 | 0 | 0 | 0 | 0 | 0 | 1 | 1 | 0 | 100 | 100 | 1 | 2 | 0 | 5 | 0 | 0 | 1 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Grouped viewing" "link"
    Then the VideoTrack HTML5 media is ready
    And no validated playback exists for "student1" in "Grouped viewing"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack HTML5 media time is between "5" and "10"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "paused"
    And the playback credit window for "student1" in "Grouped viewing" is closed by an accepted pause
    And browser playback completes Moodle tracking for "student1" in "Grouped viewing"
    When I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    When I click on "Grouped viewing" "link"
    Then the VideoTrack HTML5 media is ready
    And no validated playback exists for "student2" in "Grouped viewing"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack HTML5 media time is between "5" and "10"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "paused"
    And the playback credit window for "student2" in "Grouped viewing" is closed by an accepted pause
    And browser playback completes Moodle tracking for "student2" in "Grouped viewing"
    When I log out

  Scenario: A group-limited reviewer cannot select or request a learner in another group
    Given I log in as "reviewer1"
    When I open the VideoTrack report for "Grouped viewing" and learner "all"
    Then the VideoTrack report learner choices are "student1"
    And I should not see "Outside Learner"
    When I open the VideoTrack report for "Grouped viewing" and learner "student1"
    Then the VideoTrack report learner choices are "student1"
    And the VideoTrack report shows only learner "student1"
    When I request the VideoTrack report for "Grouped viewing" and forbidden learner "student2"
    Then the VideoTrack report learner choices are "student1"
    And I should not see "Outside Learner"

  Scenario: A report-capable reviewer without group membership sees no learner data
    Given I log in as "reviewer2"
    When I open the VideoTrack report for "Grouped viewing" and learner "all"
    Then the VideoTrack report learner choices are "none"
    And I should not see "Inside Learner"
    And I should not see "Outside Learner"
    When I request the VideoTrack report for "Grouped viewing" and forbidden learner "student1"
    Then the VideoTrack report learner choices are "none"
    And I should not see "Inside Learner"
    And I should not see "Outside Learner"

  Scenario: All-groups permission permits both actual learners without including report-only staff
    Given I log in as "reviewer3"
    When I open the VideoTrack report for "Grouped viewing" and learner "all"
    Then the VideoTrack report learner choices are "student1,student2"
    When I open the VideoTrack report for "Grouped viewing" and learner "student1"
    Then the VideoTrack report learner choices are "student1,student2"
    And the VideoTrack report shows only learner "student1"
    When I open the VideoTrack report for "Grouped viewing" and learner "student2"
    Then the VideoTrack report learner choices are "student1,student2"
    And the VideoTrack report shows only learner "student2"

  Scenario: A report viewer cannot bypass the reset management permission with a direct POST
    Given I log in as "reviewer1"
    When I open the VideoTrack report for "Grouped viewing" and learner "student1"
    Then I should not see "Reset VideoTrack data"
    When I submit a VideoTrack reset for "student1" in "Grouped viewing" expecting "denied"
    Then the VideoTrack report shows only learner "student1"
    And Moodle completion for "student1" in "Grouped viewing" is "complete"
    And Moodle completion for "student2" in "Grouped viewing" is "complete"

  Scenario: An authorised reset clears only the selected learner and its native grade and completion
    Given I log in as "reviewer3"
    When I open the VideoTrack report for "Grouped viewing" and learner "student1"
    Then I should see "Reset VideoTrack data"
    When I submit a VideoTrack reset for "student1" in "Grouped viewing" expecting "accepted"
    Then no validated playback exists for "student1" in "Grouped viewing"
    And Moodle completion for "student1" in "Grouped viewing" is "incomplete"
    And Moodle completion for "student2" in "Grouped viewing" is "complete"
