@mod @mod_videotrack @mod_videotrack_focus_exception @javascript
Feature: Course focus exceptions preserve visible split-view accessibility
  In order to use visible side-by-side tools without bypassing hidden-tab protection
  As a VideoTrack learner
  I need the effective player focus policy to honour the hidden course exception group

  Background:
    Given the following config values are set as admin:
      | focuslosspolicy | strict | mod_videotrack |
    And the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student1@example.com |
      | student2 | Student | Two | student2@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
      | student2 | C1 | student |
    And the following "activities" exist:
      | activity | course | name | behathtml5fixture | pauseonfocusloss |
      | videotrack | C1 | Focus policy | 1 | 1 |
    And the following "group members" exist:
      | group | user |
      | mod_videotrack_focus_exception | student2 |

  Scenario: Strict policy remains effective outside the exception group
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Focus policy" "link"
    Then the VideoTrack focus policy is "strict"

  Scenario: Exception membership changes strict blur handling to hidden-only
    Given I log in as "student2"
    And I am on "Course 1" course homepage
    When I click on "Focus policy" "link"
    Then the VideoTrack focus policy is "hiddenonly"
