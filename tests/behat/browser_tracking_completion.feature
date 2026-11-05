@mod @mod_videotrack @mod_videotrack_browser_tracking_completion @javascript
Feature: Browser playback persists viewing completion through the production tracking path
  In order to verify viewing completion independently of seeded progress
  As a VideoTrack learner
  I need play and pause to persist validated segments and native Moodle completion for every provider

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
      | activity   | course | name                 | behathtml5fixture | behatproviderfixture | durationseconds | autoplay | resumeplayback | completion | completionview | completionpercent | acknowledgementenabled | completionacknowledgement |
      | videotrack | C1     | HTML5 live completion | 1                |                      | 60              | 0        | 0              | 2          | 0              | 5                 | 0                      | 0                         |
      | videotrack | C1     | YouTube live completion |                | youtube              | 60              | 0        | 0              | 2          | 0              | 5                 | 0                      | 0                         |
      | videotrack | C1     | Vimeo live completion |                  | vimeo                | 60              | 0        | 0              | 2          | 0              | 5                 | 0                      | 0                         |

  Scenario: HTML5 play and pause persist viewing completion without seeded progress
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "HTML5 live completion" "link"
    Then the VideoTrack HTML5 media is ready
    And no validated playback exists for "student1" in "HTML5 live completion"
    And Moodle completion for "student1" in "HTML5 live completion" is "incomplete"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack HTML5 media time is between "5" and "10"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "paused"
    And the playback credit window for "student1" in "HTML5 live completion" is closed by an accepted pause
    And browser playback completes Moodle tracking for "student1" in "HTML5 live completion"
    When I am on "Course 1" course homepage
    And I click on "HTML5 live completion" "link"
    Then Moodle completion for "student1" in "HTML5 live completion" is "complete"

  Scenario: YouTube adapter play and pause persist viewing completion without seeded progress
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "YouTube live completion" "link"
    Then the deterministic VideoTrack "youtube" provider is ready
    And no validated playback exists for "student1" in "YouTube live completion"
    And Moodle completion for "student1" in "YouTube live completion" is "incomplete"
    When I play the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "playing"
    And the "youtube" provider time is between "5" and "10"
    When I pause the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "paused"
    And the playback credit window for "student1" in "YouTube live completion" is closed by an accepted pause
    And browser playback completes Moodle tracking for "student1" in "YouTube live completion"
    When I am on "Course 1" course homepage
    And I click on "YouTube live completion" "link"
    Then Moodle completion for "student1" in "YouTube live completion" is "complete"

  Scenario: Vimeo adapter play and pause persist viewing completion without seeded progress
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Vimeo live completion" "link"
    Then the deterministic VideoTrack "vimeo" provider is ready
    And no validated playback exists for "student1" in "Vimeo live completion"
    And Moodle completion for "student1" in "Vimeo live completion" is "incomplete"
    When I play the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "playing"
    And the "vimeo" provider time is between "5" and "10"
    When I pause the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "paused"
    And the playback credit window for "student1" in "Vimeo live completion" is closed by an accepted pause
    And browser playback completes Moodle tracking for "student1" in "Vimeo live completion"
    When I am on "Course 1" course homepage
    And I click on "Vimeo live completion" "link"
    Then Moodle completion for "student1" in "Vimeo live completion" is "complete"
