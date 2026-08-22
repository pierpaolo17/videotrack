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
      | activity | course | name |
      | forum    | C1     | Linked Forum |
    And the following "activities" exist:
      | activity   | course | name                     | behathtml5fixture | allowseekforward | reactionsenabled | studentnotesenabled | bookmarksenabled | behatlinkedforum |
      | videotrack | C1     | Blocked seek             | 1                 | 0                | 0                | 0                   | 0                |                  |
      | videotrack | C1     | Allowed seek             | 1                 | 1                | 0                | 0                   | 0                |                  |
      | videotrack | C1     | Persisted seek snapshot  | 1                 | 0                | 0                | 0                   | 0                |                  |
      | videotrack | C1     | Blocked interaction seek | 1                 | 0                | 1                | 1                   | 1                | Linked Forum     |

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

  Scenario: A blocked forward seek persists only the played pre-seek interval
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Persisted seek snapshot" "link"
    Then the VideoTrack HTML5 media is ready
    When I click on ".videotrack-ctrl-play" "css_element"
    Then the VideoTrack HTML5 media playback is "playing"
    And the VideoTrack HTML5 media time is between "4" and "10"
    When I seek the VideoTrack HTML5 media to "20" seconds
    Then the VideoTrack HTML5 media time is between "0" and "11"
    And the seek segment for "student1" in "Persisted seek snapshot" matches the pre-seek time

  Scenario: Personal interactions remain valid after a blocked seek rolls back
    Given "student1" watched "Blocked interaction seek" through "5" seconds
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Blocked interaction seek" "link"
    Then the VideoTrack HTML5 media is ready
    When I seek the VideoTrack HTML5 media to "20" seconds
    Then the VideoTrack HTML5 media time is between "0" and "6"
    When I click on "Test reaction" "button"
    And I click on ".videotrack-student-section-reactions > summary" "css_element"
    Then I should see "Test reaction" in the "#videotrack-my-reactions" "css_element"
    When I set the field "videotrack-note-input" to "Post-seek note"
    And I press "Save note"
    And I click on ".videotrack-student-section-notes > summary" "css_element"
    Then I should see "Post-seek note" in the "#videotrack-notes-list" "css_element"
    When I set the field "videotrack-bookmark-input" to "Post-seek bookmark"
    And I press "Add bookmark"
    And I click on ".videotrack-student-section-bookmarks > summary" "css_element"
    Then I should see "Post-seek bookmark" in the "#videotrack-bookmarks-list" "css_element"
    When I press "Add post to Forum"
    Then I should see "Add a Forum post about this video"
    And I should see "Linked Forum"
    And the VideoTrack Forum time is between "0" and "6"
