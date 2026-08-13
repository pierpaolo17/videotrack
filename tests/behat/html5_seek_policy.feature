@mod @mod_videotrack @mod_videotrack_html5_seek @javascript
Feature: HTML5 forward seek policy is enforced in the browser
  In order to keep watched evidence aligned with real playback
  As a VideoTrack learner
  I need the local HTML5 adapter to distinguish allowed and blocked forward seeks

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
      | activity   | course | name         | behathtml5fixture | allowseekforward |
      | videotrack | C1     | Blocked seek | 1                 | 0                |
      | videotrack | C1     | Allowed seek | 1                 | 1                |

  Scenario: A blocked forward seek returns to the watched frontier
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Blocked seek" "link"
    Then the VideoTrack HTML5 media is ready
    When I seek the VideoTrack HTML5 media to "20" seconds
    Then the VideoTrack HTML5 media time is between "0" and "2"

  Scenario: An allowed forward seek remains at the requested timestamp
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Allowed seek" "link"
    Then the VideoTrack HTML5 media is ready
    When I seek the VideoTrack HTML5 media to "20" seconds
    Then the VideoTrack HTML5 media time is between "19" and "21"
