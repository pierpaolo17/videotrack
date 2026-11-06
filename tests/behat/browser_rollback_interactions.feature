@mod @mod_videotrack @mod_videotrack_browser_rollback_interactions @javascript
Feature: Blocked forward seeks preserve learner interactions and completed viewing
  In order to keep study tools usable after navigation is refused
  As a VideoTrack learner
  I need rollback to retain safe interaction timestamps and the viewing completion earned by actual playback

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
      | activity | course | name |
      | forum    | C1     | Linked Forum |
    And the following "activities" exist:
      | activity   | course | name                | behathtml5fixture | behatproviderfixture | behatlinkedforum | durationseconds | allowseekforward | blockedseekplaybackrate | autoplay | resumeplayback | reactionsenabled | reactionsrequired | studentnotesenabled | bookmarksenabled | completion | completionview | completionpercent | acknowledgementenabled | completionacknowledgement |
      | videotrack | C1     | HTML5 rollback tools | 1               |                      | Linked Forum     | 60              | 0                | 100                     | 0        | 0              | 1                | 0                 | 1                   | 1                | 2          | 0              | 5                 | 0                      | 0                         |
      | videotrack | C1     | YouTube rollback tools |               | youtube              | Linked Forum     | 60              | 0                | 100                     | 0        | 0              | 1                | 0                 | 1                   | 1                | 2          | 0              | 5                 | 0                      | 0                         |
      | videotrack | C1     | Vimeo rollback tools |                 | vimeo                | Linked Forum     | 60              | 0                | 100                     | 0        | 0              | 1                | 0                 | 1                   | 1                | 2          | 0              | 5                 | 0                      | 0                         |

  Scenario: HTML5 rollback preserves interaction timestamps and browser-earned completion
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "HTML5 rollback tools" "link"
    Then the VideoTrack HTML5 media is ready
    And no validated playback exists for "student1" in "HTML5 rollback tools"
    And Moodle completion for "student1" in "HTML5 rollback tools" is "incomplete"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack HTML5 media time is between "5" and "10"
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "paused"
    And the playback credit window for "student1" in "HTML5 rollback tools" is closed by an accepted pause
    And browser playback completes Moodle tracking for "student1" in "HTML5 rollback tools"
    When I seek the VideoTrack HTML5 media to "30" seconds
    And I pause the VideoTrack player after rollback recovery
    Then the paused VideoTrack player matches "student1" frontier in "HTML5 rollback tools"
    And the playback credit window for "student1" in "HTML5 rollback tools" is closed by an accepted pause
    And Moodle completion for "student1" in "HTML5 rollback tools" is "complete"
    When I click on "Test reaction" "button"
    And I click on ".videotrack-student-section-reactions > summary" "css_element"
    Then I should see "Test reaction" in the "#videotrack-my-reactions" "css_element"
    When I set the field "videotrack-note-input" to "Post-rollback note"
    And I press "Save note"
    And I click on ".videotrack-student-section-notes > summary" "css_element"
    Then I should see "Post-rollback note" in the "#videotrack-notes-list" "css_element"
    When I set the field "videotrack-bookmark-input" to "Post-rollback bookmark"
    And I press "Add bookmark"
    And I click on ".videotrack-student-section-bookmarks > summary" "css_element"
    Then I should see "Post-rollback bookmark" in the "#videotrack-bookmarks-list" "css_element"
    When I press "Add post to Forum"
    Then I should see "Add a Forum post about this video"
    And I should see "Linked Forum"
    And the VideoTrack Forum time is between "0" and "22"
    And post-rollback interactions for "student1" in "HTML5 rollback tools" retain safe timestamps
    And Moodle completion for "student1" in "HTML5 rollback tools" is "complete"
    When I am on "Course 1" course homepage
    And I click on "HTML5 rollback tools" "link"
    Then Moodle completion for "student1" in "HTML5 rollback tools" is "complete"

  Scenario: YouTube rollback preserves interaction timestamps and browser-earned completion
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "YouTube rollback tools" "link"
    Then the deterministic VideoTrack "youtube" provider is ready
    And no validated playback exists for "student1" in "YouTube rollback tools"
    And Moodle completion for "student1" in "YouTube rollback tools" is "incomplete"
    When I play the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "playing"
    And the "youtube" provider time is between "5" and "10"
    When I pause the deterministic VideoTrack "youtube" provider
    Then the "youtube" provider playback is "paused"
    And the playback credit window for "student1" in "YouTube rollback tools" is closed by an accepted pause
    And browser playback completes Moodle tracking for "student1" in "YouTube rollback tools"
    When I seek the deterministic VideoTrack "youtube" provider to "30" seconds
    And I pause the VideoTrack player after rollback recovery
    Then the paused VideoTrack player matches "student1" frontier in "YouTube rollback tools"
    And the playback credit window for "student1" in "YouTube rollback tools" is closed by an accepted pause
    And Moodle completion for "student1" in "YouTube rollback tools" is "complete"
    When I click on "Test reaction" "button"
    And I click on ".videotrack-student-section-reactions > summary" "css_element"
    Then I should see "Test reaction" in the "#videotrack-my-reactions" "css_element"
    When I set the field "videotrack-note-input" to "Post-rollback note"
    And I press "Save note"
    And I click on ".videotrack-student-section-notes > summary" "css_element"
    Then I should see "Post-rollback note" in the "#videotrack-notes-list" "css_element"
    When I set the field "videotrack-bookmark-input" to "Post-rollback bookmark"
    And I press "Add bookmark"
    And I click on ".videotrack-student-section-bookmarks > summary" "css_element"
    Then I should see "Post-rollback bookmark" in the "#videotrack-bookmarks-list" "css_element"
    When I press "Add post to Forum"
    Then I should see "Add a Forum post about this video"
    And I should see "Linked Forum"
    And the VideoTrack Forum time is between "0" and "22"
    And post-rollback interactions for "student1" in "YouTube rollback tools" retain safe timestamps
    And Moodle completion for "student1" in "YouTube rollback tools" is "complete"
    When I am on "Course 1" course homepage
    And I click on "YouTube rollback tools" "link"
    Then Moodle completion for "student1" in "YouTube rollback tools" is "complete"

  Scenario: Vimeo rollback preserves interaction timestamps and browser-earned completion
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Vimeo rollback tools" "link"
    Then the deterministic VideoTrack "vimeo" provider is ready
    And no validated playback exists for "student1" in "Vimeo rollback tools"
    And Moodle completion for "student1" in "Vimeo rollback tools" is "incomplete"
    When I play the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "playing"
    And the "vimeo" provider time is between "5" and "10"
    When I pause the deterministic VideoTrack "vimeo" provider
    Then the "vimeo" provider playback is "paused"
    And the playback credit window for "student1" in "Vimeo rollback tools" is closed by an accepted pause
    And browser playback completes Moodle tracking for "student1" in "Vimeo rollback tools"
    When I seek the deterministic VideoTrack "vimeo" provider to "30" seconds
    And I pause the VideoTrack player after rollback recovery
    Then the paused VideoTrack player matches "student1" frontier in "Vimeo rollback tools"
    And the playback credit window for "student1" in "Vimeo rollback tools" is closed by an accepted pause
    And Moodle completion for "student1" in "Vimeo rollback tools" is "complete"
    When I click on "Test reaction" "button"
    And I click on ".videotrack-student-section-reactions > summary" "css_element"
    Then I should see "Test reaction" in the "#videotrack-my-reactions" "css_element"
    When I set the field "videotrack-note-input" to "Post-rollback note"
    And I press "Save note"
    And I click on ".videotrack-student-section-notes > summary" "css_element"
    Then I should see "Post-rollback note" in the "#videotrack-notes-list" "css_element"
    When I set the field "videotrack-bookmark-input" to "Post-rollback bookmark"
    And I press "Add bookmark"
    And I click on ".videotrack-student-section-bookmarks > summary" "css_element"
    Then I should see "Post-rollback bookmark" in the "#videotrack-bookmarks-list" "css_element"
    When I press "Add post to Forum"
    Then I should see "Add a Forum post about this video"
    And I should see "Linked Forum"
    And the VideoTrack Forum time is between "0" and "22"
    And post-rollback interactions for "student1" in "Vimeo rollback tools" retain safe timestamps
    And Moodle completion for "student1" in "Vimeo rollback tools" is "complete"
    When I am on "Course 1" course homepage
    And I click on "Vimeo rollback tools" "link"
    Then Moodle completion for "student1" in "Vimeo rollback tools" is "complete"
