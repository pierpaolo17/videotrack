@mod @mod_videotrack @javascript
Feature: Student personal lists use compact collapsible sections
  In order to keep the VideoTrack learner page focused on the video
  As a student
  I need my reactions, notes and bookmarks to be collapsed until I choose to review them

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

  Scenario: Personal sections are collapsed by default and can be opened independently
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Video 1" "link"
    Then I should see "My reactions" in the ".videotrack-student-section-reactions > summary" "css_element"
    And I should see "My notes" in the ".videotrack-student-section-notes > summary" "css_element"
    And I should see "My bookmarks" in the ".videotrack-student-section-bookmarks > summary" "css_element"
    And ".videotrack-student-section-reactions[open]" "css_element" should not exist
    And ".videotrack-student-section-notes[open]" "css_element" should not exist
    And ".videotrack-student-section-bookmarks[open]" "css_element" should not exist
    When I click on "My reactions" "text"
    Then ".videotrack-student-section-reactions[open]" "css_element" should exist
    And ".videotrack-student-section-notes[open]" "css_element" should not exist
    When I click on "My notes" "text"
    Then ".videotrack-student-section-notes[open]" "css_element" should exist
    When I click on "My bookmarks" "text"
    Then ".videotrack-student-section-bookmarks[open]" "css_element" should exist
