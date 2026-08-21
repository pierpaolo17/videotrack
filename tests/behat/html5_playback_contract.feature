@mod @mod_videotrack @mod_videotrack_html5_playback @javascript
Feature: HTML5 playback contracts remain stable in the browser
  In order to keep resume and basic playback behaviour aligned with validated progress
  As a VideoTrack learner
  I need the local HTML5 adapter to resume, seek backward and play or pause deterministically

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
    And the following "activities" exist:
      | activity   | course | name               | behathtml5fixture | allowseekforward | allowseekbackward | resumeplayback |
      | videotrack | C1     | Playback contracts | 1                 | 0                | 1                 | 1              |

  Scenario: Validated progress resumes at the saved HTML5 position
    Given "student1" watched "Playback contracts" through "12" seconds
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Playback contracts" "link"
    Then the VideoTrack HTML5 media is ready
    And the VideoTrack HTML5 media time is between "11" and "13"

  Scenario: A backward seek remains allowed inside validated progress
    Given "student1" watched "Playback contracts" through "20" seconds
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Playback contracts" "link"
    Then the VideoTrack HTML5 media is ready
    And the VideoTrack HTML5 media time is between "19" and "21"
    When I seek the VideoTrack HTML5 media to "5" seconds
    Then the VideoTrack HTML5 media time is between "4" and "6"

  Scenario: The learner can play and pause the deterministic HTML5 media
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Playback contracts" "link"
    Then the VideoTrack HTML5 media is ready
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "paused"
