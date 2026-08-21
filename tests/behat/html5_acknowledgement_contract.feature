@mod @mod_videotrack @mod_videotrack_acknowledgement @javascript
Feature: HTML5 acknowledgement contracts remain stable in the browser
  In order to keep explicit learner acknowledgement aligned with validated playback
  As a VideoTrack learner
  I need immediate and video-end acknowledgement policies to behave deterministically

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
      | activity   | course | name                     | behathtml5fixture | acknowledgementenabled | acknowledgementtext          | acknowledgementtiming |
      | videotrack | C1     | Anytime acknowledgement  | 1                 | 1                      | I acknowledge this statement | 0                     |
      | videotrack | C1     | Video-end acknowledgement | 1                 | 1                      | I acknowledge this statement | 1                     |

  Scenario: An anytime acknowledgement can be confirmed and remains persisted
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Anytime acknowledgement" "link"
    Then I should see "Acknowledgement"
    And I should see "I acknowledge this statement"
    And "#id_ackconfirm:not([disabled])" "css_element" should exist
    And "#videotrack-acknowledgement-submit:not([disabled])" "css_element" should exist
    When I click on "#id_ackconfirm" "css_element"
    And I press "Confirm acknowledgement"
    Then I should see "Your acknowledgement has been recorded."
    And I should see "Confirmed on"
    And "#videotrack-acknowledgement-form" "css_element" should not exist

  Scenario: A video-end acknowledgement stays disabled before validated playback reaches the end
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Video-end acknowledgement" "link"
    Then the VideoTrack HTML5 media is ready
    And I should see "The confirmation will become available after the final second of the video has been reached."
    And "#id_ackconfirm[disabled]" "css_element" should exist
    And "#videotrack-acknowledgement-submit[disabled]" "css_element" should exist

  Scenario: Validated playback through the final second unlocks the video-end acknowledgement
    Given "student1" watched "Video-end acknowledgement" through "59.5" seconds
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Video-end acknowledgement" "link"
    Then the VideoTrack HTML5 media is ready
    And "#videotrack-acknowledgement-pending" "css_element" should not exist
    And "#id_ackconfirm:not([disabled])" "css_element" should exist
    And "#videotrack-acknowledgement-submit:not([disabled])" "css_element" should exist
    When I click on "#id_ackconfirm" "css_element"
    And I press "Confirm acknowledgement"
    Then I should see "Your acknowledgement has been recorded."
    And I should see "Confirmed on"
