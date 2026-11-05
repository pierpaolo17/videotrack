@mod @mod_videotrack @mod_videotrack_browser_allowed_seek @javascript
Feature: Allowed forward seeks preserve real playback evidence around the skipped interval
  In order to distinguish navigation from watched coverage
  As a VideoTrack learner
  I need allowed forward seeks to keep playback running without completing the skipped viewing requirement

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1 | 0 | 1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
    And the following "activities" exist:
      | activity   | course | name                | behathtml5fixture | behatproviderfixture | durationseconds | allowseekforward | autoplay | resumeplayback | completion | completionview | completionpercent | acknowledgementenabled | completionacknowledgement |
      | videotrack | C1     | HTML5 allowed gap   | 1                |                      | 60              | 1                | 0        | 0              | 2          | 0              | 50                | 0                      | 0                         |
      | videotrack | C1     | YouTube allowed gap |                  | youtube              | 60              | 1                | 0        | 0              | 2          | 0              | 50                | 0                      | 0                         |
      | videotrack | C1     | Vimeo allowed gap   |                  | vimeo                | 60              | 1                | 0        | 0              | 2          | 0              | 50                | 0                      | 0                         |

  Scenario: HTML5 allowed forward seek separates played regions without completing the skipped gap
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "HTML5 allowed gap" "link"
    Then the VideoTrack HTML5 media is ready
    And no validated playback exists for "student1" in "HTML5 allowed gap"
    And Moodle completion for "student1" in "HTML5 allowed gap" is "incomplete"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack HTML5 media time is between "4" and "10"
    When I seek the VideoTrack HTML5 media to "30" seconds
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack HTML5 media time is between "33" and "39"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "paused"
    And the playback credit window for "student1" in "HTML5 allowed gap" is closed by an accepted pause
    And the allowed forward seek for "student1" in "HTML5 allowed gap" preserves the unwatched gap
    And Moodle completion for "student1" in "HTML5 allowed gap" is "incomplete"
    When I am on "Course 1" course homepage
    And I click on "HTML5 allowed gap" "link"
    Then Moodle completion for "student1" in "HTML5 allowed gap" is "incomplete"

  Scenario: YouTube allowed forward seek separates played regions without completing the skipped gap
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "YouTube allowed gap" "link"
    Then the deterministic VideoTrack "youtube" provider is ready
    And no validated playback exists for "student1" in "YouTube allowed gap"
    And Moodle completion for "student1" in "YouTube allowed gap" is "incomplete"
    When I play the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "playing"
    And the "youtube" provider time is between "4" and "10"
    When I seek the deterministic VideoTrack "youtube" provider to "30" seconds
    Then the "youtube" provider playback is "playing"
    And the "youtube" provider time is between "33" and "39"
    When I pause the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "paused"
    And the playback credit window for "student1" in "YouTube allowed gap" is closed by an accepted pause
    And the allowed forward seek for "student1" in "YouTube allowed gap" preserves the unwatched gap
    And Moodle completion for "student1" in "YouTube allowed gap" is "incomplete"
    When I am on "Course 1" course homepage
    And I click on "YouTube allowed gap" "link"
    Then Moodle completion for "student1" in "YouTube allowed gap" is "incomplete"

  Scenario: Vimeo allowed forward seek separates played regions without completing the skipped gap
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Vimeo allowed gap" "link"
    Then the deterministic VideoTrack "vimeo" provider is ready
    And no validated playback exists for "student1" in "Vimeo allowed gap"
    And Moodle completion for "student1" in "Vimeo allowed gap" is "incomplete"
    When I play the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "playing"
    And the "vimeo" provider time is between "4" and "10"
    When I seek the deterministic VideoTrack "vimeo" provider to "30" seconds
    Then the "vimeo" provider playback is "playing"
    And the "vimeo" provider time is between "33" and "39"
    When I pause the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "paused"
    And the playback credit window for "student1" in "Vimeo allowed gap" is closed by an accepted pause
    And the allowed forward seek for "student1" in "Vimeo allowed gap" preserves the unwatched gap
    And Moodle completion for "student1" in "Vimeo allowed gap" is "incomplete"
    When I am on "Course 1" course homepage
    And I click on "Vimeo allowed gap" "link"
    Then Moodle completion for "student1" in "Vimeo allowed gap" is "incomplete"
