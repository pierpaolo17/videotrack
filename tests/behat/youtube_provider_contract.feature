@mod @mod_videotrack @mod_videotrack_youtube_provider @javascript
Feature: YouTube provider contracts remain deterministic in the browser
  In order to verify provider parity without public network availability
  As a VideoTrack learner
  I need the production YouTube adapter to run against a local deterministic SDK double

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
      | activity   | course | name                       | behatproviderfixture | allowseekforward | allowseekbackward | resumeplayback |
      | videotrack | C1     | YouTube provider contracts | youtube              | 0                | 1                 | 1              |

  Scenario: Resume play pause and seek policy use the production YouTube adapter
    Given "student1" watched "YouTube provider contracts" through "12" seconds
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "YouTube provider contracts" "link"
    Then the deterministic VideoTrack "youtube" provider is ready
    And the "youtube" provider time is between "11" and "13"
    When I play the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider time is between "14" and "20"
    When I pause the deterministic VideoTrack "youtube" provider
    Then the playback credit window for "student1" in "YouTube provider contracts" is closed by an accepted pause
    When I seek the deterministic VideoTrack "youtube" provider to "5" seconds
    Then the "youtube" provider time is between "4" and "6"
    When I seek the deterministic VideoTrack "youtube" provider to "30" seconds
    Then the "youtube" provider matches "student1" frontier in "YouTube provider contracts"
