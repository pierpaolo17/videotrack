@mod @mod_videotrack @mod_videotrack_random_attention @javascript
Feature: Teacher attention intervals preserve the provider tracking lifecycle
  In order to configure attention pauses independently of completion duration
  As a teacher and learner
  I need automatic thirds and optional overrides to reach the real player safely

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

  Scenario: Automatic saved-duration thirds
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | durationseconds | randomfocuspauses | randompauseminseconds | randompausemaxseconds |
      | videotrack | C1 | Attention settings | 1 | 900 | 1 | 0 | 0 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Attention settings" "link"
    Then the VideoTrack attention policy is "enabled" from "300" to "600" seconds

  Scenario: Minimum-only override
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | durationseconds | randomfocuspauses | randompauseminseconds | randompausemaxseconds |
      | videotrack | C1 | Attention settings | 1 | 900 | 1 | 900 | 0 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Attention settings" "link"
    Then the VideoTrack attention policy is "enabled" from "900" to "900" seconds

  Scenario: Maximum-only override
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | durationseconds | randomfocuspauses | randompauseminseconds | randompausemaxseconds |
      | videotrack | C1 | Attention settings | 1 | 900 | 1 | 0 | 120 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Attention settings" "link"
    Then the VideoTrack attention policy is "enabled" from "120" to "120" seconds

  Scenario: Disabled attention switch
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | durationseconds | randomfocuspauses | randompauseminseconds | randompausemaxseconds |
      | videotrack | C1 | Attention settings | 1 | 900 | 0 | 60 | 60 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Attention settings" "link"
    Then the VideoTrack attention policy is "disabled" from "60" to "60" seconds

  Scenario: Zero-duration site fallback
    Given the following config values are set as admin:
      | randompauseminseconds | 120 | mod_videotrack |
      | randompausemaxseconds | 900 | mod_videotrack |
    And the following "activities" exist:
      | activity | course | name | behathtml5fixture | durationseconds | randomfocuspauses | randompauseminseconds | randompausemaxseconds |
      | videotrack | C1 | Attention settings | 1 | 0 | 1 | 0 | 0 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Attention settings" "link"
    Then the VideoTrack attention policy is "enabled" from "120" to "900" seconds

  Scenario: HTML5 attention pause closes server credit and permits a validated resume
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | behatproviderfixture | durationseconds | randomfocuspauses | randompauseminseconds | randompausemaxseconds | allowplaybackratechange | maxplaybackrate | autoplay | resumeplayback |
      | videotrack | C1 | HTML5 attention pause | 1 |  | 1800 | 1 | 60 | 60 | 1 | 100 | 0 | 0 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "HTML5 attention pause" "link"
    Then the VideoTrack HTML5 media is ready
    And the VideoTrack attention policy is "enabled" from "60" to "60" seconds
    When I use half speed for the VideoTrack attention check
    And I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack attention check pauses playback
    And the playback credit window for "student1" in "HTML5 attention pause" is closed by an accepted pause
    And the paused VideoTrack player matches "student1" frontier in "HTML5 attention pause"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack HTML5 media time is between "31" and "38"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "paused"
    And the playback credit window for "student1" in "HTML5 attention pause" is closed by an accepted pause

  Scenario: YOUTUBE attention pause closes server credit and permits a validated resume
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | behatproviderfixture | durationseconds | randomfocuspauses | randompauseminseconds | randompausemaxseconds | allowplaybackratechange | maxplaybackrate | autoplay | resumeplayback |
      | videotrack | C1 | YOUTUBE attention pause |  | youtube | 1800 | 1 | 60 | 60 | 1 | 100 | 0 | 0 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "YOUTUBE attention pause" "link"
    Then the deterministic VideoTrack "youtube" provider is ready
    And the VideoTrack attention policy is "enabled" from "60" to "60" seconds
    When I use half speed for the VideoTrack attention check
    And I play the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "playing"
    And the VideoTrack attention check pauses playback
    And the playback credit window for "student1" in "YOUTUBE attention pause" is closed by an accepted pause
    And the paused VideoTrack player matches "student1" frontier in "YOUTUBE attention pause"
    When I play the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "playing"
    And the "youtube" provider time is between "31" and "38"
    When I pause the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "paused"
    And the playback credit window for "student1" in "YOUTUBE attention pause" is closed by an accepted pause

  Scenario: VIMEO attention pause closes server credit and permits a validated resume
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | behatproviderfixture | durationseconds | randomfocuspauses | randompauseminseconds | randompausemaxseconds | allowplaybackratechange | maxplaybackrate | autoplay | resumeplayback |
      | videotrack | C1 | VIMEO attention pause |  | vimeo | 1800 | 1 | 60 | 60 | 1 | 100 | 0 | 0 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "VIMEO attention pause" "link"
    Then the deterministic VideoTrack "vimeo" provider is ready
    And the VideoTrack attention policy is "enabled" from "60" to "60" seconds
    When I use half speed for the VideoTrack attention check
    And I play the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "playing"
    And the VideoTrack attention check pauses playback
    And the playback credit window for "student1" in "VIMEO attention pause" is closed by an accepted pause
    And the paused VideoTrack player matches "student1" frontier in "VIMEO attention pause"
    When I play the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "playing"
    And the "vimeo" provider time is between "31" and "38"
    When I pause the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "paused"
    And the playback credit window for "student1" in "VIMEO attention pause" is closed by an accepted pause
