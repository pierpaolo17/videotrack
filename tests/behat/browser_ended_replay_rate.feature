@mod @mod_videotrack @mod_videotrack_browser_ended_replay_rate @javascript
Feature: Natural end and replay retain validated coverage across playback-rate changes
  In order to trust completion after normal viewing and replay
  As a VideoTrack learner
  I need rate caps, natural end and a fresh replay handshake to preserve unique watched time

  Background:
    Given the following config values are set as admin:
      | maxplaybackrate | 200 | mod_videotrack |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion |
      | Course 1 | C1 | 0 | 1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |

  Scenario: HTML5 natural end and replay retain coverage across allowed and above-cap rates
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | behatproviderfixture | durationseconds | autoplay | resumeplayback | loopenabled | randomfocuspauses | allowseekforward | allowseekbackward | allowplaybackratechange | maxplaybackrate | blockedseekplaybackrate | playbackspeed_1 | playbackspeed_2 | completion | completionview | completionpercent | acknowledgementenabled | completionacknowledgement |
      | videotrack | C1 | HTML5 end and replay | 1 |  | 60 | 0 | 0 | 0 | 0 | 1 | 1 | 1 | 200 | 100 | 1 | 2 | 2 | 0 | 95 | 0 | 0 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "HTML5 end and replay" "link"
    Then the VideoTrack HTML5 media is ready
    And no validated playback exists for "student1" in "HTML5 end and replay"
    And Moodle completion for "student1" in "HTML5 end and replay" is "incomplete"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack HTML5 media time is between "3" and "8"
    When I request VideoTrack playback rate "4"
    Then the VideoTrack playback rate is "1"
    When I request VideoTrack playback rate "2"
    Then the VideoTrack playback rate is "2"
    And the VideoTrack player reaches its natural end
    And an accepted end closes playback for "student1" in "HTML5 end and replay"
    And Moodle completion for "student1" in "HTML5 end and replay" is "complete"
    When I request VideoTrack playback rate "1"
    Then the VideoTrack playback rate is "1"
    When I seek the VideoTrack HTML5 media to "5" seconds
    Then the VideoTrack HTML5 media time is between "4.75" and "5.25"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack HTML5 media time is between "8" and "12"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "paused"
    And replaying watched footage adds no coverage for "student1" in "HTML5 end and replay"
    And Moodle completion for "student1" in "HTML5 end and replay" is "complete"
    When I am on "Course 1" course homepage
    And I click on "HTML5 end and replay" "link"
    Then replaying watched footage adds no coverage for "student1" in "HTML5 end and replay"
    And Moodle completion for "student1" in "HTML5 end and replay" is "complete"

  Scenario: YOUTUBE natural end and replay retain coverage across allowed and above-cap rates
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | behatproviderfixture | durationseconds | autoplay | resumeplayback | loopenabled | randomfocuspauses | allowseekforward | allowseekbackward | allowplaybackratechange | maxplaybackrate | blockedseekplaybackrate | playbackspeed_1 | playbackspeed_2 | completion | completionview | completionpercent | acknowledgementenabled | completionacknowledgement |
      | videotrack | C1 | YOUTUBE end and replay |  | youtube | 60 | 0 | 0 | 0 | 0 | 1 | 1 | 1 | 200 | 100 | 1 | 2 | 2 | 0 | 95 | 0 | 0 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "YOUTUBE end and replay" "link"
    Then the deterministic VideoTrack "youtube" provider is ready
    And no validated playback exists for "student1" in "YOUTUBE end and replay"
    And Moodle completion for "student1" in "YOUTUBE end and replay" is "incomplete"
    When I play the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "playing"
    And the "youtube" provider time is between "3" and "8"
    When I request VideoTrack playback rate "4"
    Then the VideoTrack playback rate is "1"
    When I request VideoTrack playback rate "2"
    Then the VideoTrack playback rate is "2"
    And the VideoTrack player reaches its natural end
    And an accepted end closes playback for "student1" in "YOUTUBE end and replay"
    And Moodle completion for "student1" in "YOUTUBE end and replay" is "complete"
    When I request VideoTrack playback rate "1"
    Then the VideoTrack playback rate is "1"
    When I seek the deterministic VideoTrack "youtube" provider to "5" seconds
    Then the "youtube" provider time is between "4.75" and "5.25"
    When I play the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "playing"
    And the "youtube" provider time is between "8" and "12"
    When I pause the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "paused"
    And replaying watched footage adds no coverage for "student1" in "YOUTUBE end and replay"
    And Moodle completion for "student1" in "YOUTUBE end and replay" is "complete"
    When I am on "Course 1" course homepage
    And I click on "YOUTUBE end and replay" "link"
    Then replaying watched footage adds no coverage for "student1" in "YOUTUBE end and replay"
    And Moodle completion for "student1" in "YOUTUBE end and replay" is "complete"

  Scenario: VIMEO natural end and replay retain coverage across allowed and above-cap rates
    Given the following "activities" exist:
      | activity | course | name | behathtml5fixture | behatproviderfixture | durationseconds | autoplay | resumeplayback | loopenabled | randomfocuspauses | allowseekforward | allowseekbackward | allowplaybackratechange | maxplaybackrate | blockedseekplaybackrate | playbackspeed_1 | playbackspeed_2 | completion | completionview | completionpercent | acknowledgementenabled | completionacknowledgement |
      | videotrack | C1 | VIMEO end and replay |  | vimeo | 60 | 0 | 0 | 0 | 0 | 1 | 1 | 1 | 200 | 100 | 1 | 2 | 2 | 0 | 95 | 0 | 0 |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "VIMEO end and replay" "link"
    Then the deterministic VideoTrack "vimeo" provider is ready
    And no validated playback exists for "student1" in "VIMEO end and replay"
    And Moodle completion for "student1" in "VIMEO end and replay" is "incomplete"
    When I play the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "playing"
    And the "vimeo" provider time is between "3" and "8"
    When I request VideoTrack playback rate "4"
    Then the VideoTrack playback rate is "1"
    When I request VideoTrack playback rate "2"
    Then the VideoTrack playback rate is "2"
    And the VideoTrack player reaches its natural end
    And an accepted end closes playback for "student1" in "VIMEO end and replay"
    And Moodle completion for "student1" in "VIMEO end and replay" is "complete"
    When I request VideoTrack playback rate "1"
    Then the VideoTrack playback rate is "1"
    When I seek the deterministic VideoTrack "vimeo" provider to "5" seconds
    Then the "vimeo" provider time is between "4.75" and "5.25"
    When I play the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "playing"
    And the "vimeo" provider time is between "8" and "12"
    When I pause the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "paused"
    And replaying watched footage adds no coverage for "student1" in "VIMEO end and replay"
    And Moodle completion for "student1" in "VIMEO end and replay" is "complete"
    When I am on "Course 1" course homepage
    And I click on "VIMEO end and replay" "link"
    Then replaying watched footage adds no coverage for "student1" in "VIMEO end and replay"
    And Moodle completion for "student1" in "VIMEO end and replay" is "complete"
