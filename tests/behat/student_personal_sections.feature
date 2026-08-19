@mod @mod_videotrack @javascript
Feature: Student personal history uses compact collapsible sections
  In order to keep the VideoTrack learner page focused on the active interaction controls
  As a student
  I need reaction buttons, note input and bookmark input to stay visible while only saved history is collapsed

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
      | activity   | course | name    | reactionsenabled | studentnotesenabled | bookmarksenabled |
      | videotrack | C1     | Video 1 | 1                | 1                   | 1                |

  Scenario: Active controls stay visible and saved personal history is collapsed by default
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Video 1" "link"
    Then "#videotrack-interval-bar ~ #videotrack-progress-summary" "css_element" should exist
    And "#videotrack-progress-summary ~ #videotrack-reactions" "css_element" should exist
    And "#videotrack-reactions .videotrack-reaction-btn" "css_element" should exist
    And "#videotrack-note-composer #videotrack-note-input" "css_element" should exist
    And ".videotrack-student-section-notes #videotrack-note-input" "css_element" should not exist
    And "#videotrack-bookmark-composer #videotrack-bookmark-input" "css_element" should exist
    And ".videotrack-student-section-bookmarks #videotrack-bookmark-input" "css_element" should not exist
    And I should see "My reactions" in the ".videotrack-student-section-reactions > summary" "css_element"
    And I should see "My notes" in the ".videotrack-student-section-notes > summary" "css_element"
    And I should see "My bookmarks" in the ".videotrack-student-section-bookmarks > summary" "css_element"
    And I should see "Student notes" in the "#videotrack-note-composer h3" "css_element"
    And I should see "Student bookmarks" in the "#videotrack-bookmark-composer h3" "css_element"
    And ".videotrack-student-section-reactions[open]" "css_element" should not exist
    And ".videotrack-student-section-notes[open]" "css_element" should not exist
    And ".videotrack-student-section-bookmarks[open]" "css_element" should not exist
    And "#videotrack-reactions-list-section ~ #videotrack-note-composer" "css_element" should exist
    And "#videotrack-note-composer ~ #videotrack-notes-panel" "css_element" should exist
    And "#videotrack-notes-panel ~ #videotrack-bookmark-composer" "css_element" should exist
    And "#videotrack-bookmark-composer ~ #videotrack-bookmarks-panel" "css_element" should exist
    When I click on ".videotrack-student-section-reactions > summary" "css_element"
    Then ".videotrack-student-section-reactions[open]" "css_element" should exist
    And ".videotrack-student-section-notes[open]" "css_element" should not exist
    When I click on ".videotrack-student-section-notes > summary" "css_element"
    Then ".videotrack-student-section-notes[open]" "css_element" should exist
    When I click on ".videotrack-student-section-bookmarks > summary" "css_element"
    Then ".videotrack-student-section-bookmarks[open]" "css_element" should exist
