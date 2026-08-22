@mod @mod_videotrack @mod_videotrack_vimeo_provider @javascript
Feature: Vimeo provider contracts remain deterministic in the browser
  In order to verify provider parity without public network availability
  As a VideoTrack learner
  I need the production Vimeo adapter to run against a local deterministic SDK double

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
      | activity   | course | name                     | behatproviderfixture | allowseekforward | allowseekbackward | blockedseekplaybackrate | resumeplayback |
      | videotrack | C1     | Vimeo provider contracts | vimeo                | 0                | 1                 | 100                     | 1              |

  Scenario: Resume seek policy and recovery use the production Vimeo adapter
    Given "student1" watched "Vimeo provider contracts" through "12" seconds
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Vimeo provider contracts" "link"
    Then the deterministic VideoTrack "vimeo" provider is ready
    And the "vimeo" provider time is between "11" and "13"
    When I play the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider time is between "14" and "16"
    When I seek the deterministic VideoTrack "vimeo" provider to "5" seconds
    Then the "vimeo" provider time is between "4" and "7"
    When I seek the deterministic VideoTrack "vimeo" provider to "30" seconds
    Then the "vimeo" provider matches "student1" frontier in "Vimeo provider contracts"
    And the "vimeo" provider playback is "playing"
    And the "vimeo" provider time is between "20.5" and "24"
    When I pause the deterministic VideoTrack "vimeo" provider
    Then the playback credit window for "student1" in "Vimeo provider contracts" is closed by an accepted pause
