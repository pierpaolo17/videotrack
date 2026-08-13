@mod @mod_videotrack @javascript
Feature: Learner interaction controls respect participant scope
  In order to keep VideoTrack participation separate from report permissions
  As a course user
  I need learner controls to be active only when I am also a VideoTrack participant

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student1@example.com |
      | teacher1 | Teacher | One | teacher1@example.com |
      | dual1    | Dual    | Role | dual1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |
      | dual1    | C1     | student        |
    And the following "role assigns" exist:
      | user  | role           | contextlevel | reference |
      | dual1 | editingteacher | Course       | C1        |
    And the following "activities" exist:
      | activity   | course | name    | reactionsenabled | studentnotesenabled | bookmarksenabled |
      | videotrack | C1     | Video 1 | 1                | 1                   | 1                |

  Scenario: A learner gets active personal interaction controls
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Video 1" "link"
    Then "#videotrack-reactions .videotrack-reaction-btn[aria-disabled=\"false\"]" "css_element" should exist
    And "#videotrack-note-input[disabled]" "css_element" should not exist
    And "#videotrack-bookmark-input[disabled]" "css_element" should not exist
    And ".videotrack-student-section-reactions" "css_element" should exist

  Scenario: A teacher without learner participation gets read-only personal controls
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I click on "Video 1" "link"
    Then "#videotrack-reactions .videotrack-reaction-btn[aria-disabled=\"true\"]" "css_element" should exist
    And "#videotrack-note-input[disabled]" "css_element" should exist
    And "#videotrack-bookmark-input[disabled]" "css_element" should exist
    And ".videotrack-student-section-reactions" "css_element" should not exist

  Scenario: A dual-role user keeps learner interaction controls active
    Given I log in as "dual1"
    And I am on "Course 1" course homepage
    When I click on "Video 1" "link"
    Then "#videotrack-reactions .videotrack-reaction-btn[aria-disabled=\"false\"]" "css_element" should exist
    And "#videotrack-note-input[disabled]" "css_element" should not exist
    And "#videotrack-bookmark-input[disabled]" "css_element" should not exist
    And ".videotrack-student-section-reactions" "css_element" should exist
