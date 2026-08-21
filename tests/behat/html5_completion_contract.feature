@mod @mod_videotrack @mod_videotrack_completion @javascript
Feature: VideoTrack conditions synchronise Moodle completion in the browser
  In order to trust activity completion outside the VideoTrack page
  As a VideoTrack learner
  I need server-validated viewing evidence and browser acknowledgements to update Moodle completion end to end

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
      | activity   | course | name                         | behathtml5fixture | completion | completionpercent | acknowledgementenabled | acknowledgementtext          | acknowledgementtiming | completionacknowledgement | completionlogic |
      | videotrack | C1     | Completion by viewing        | 1                 | 2          | 50                | 0                      |                              | 0                     | 0                         | and             |
      | videotrack | C1     | Completion by acknowledgement | 1                 | 2          | 0                 | 1                      | I acknowledge this statement | 0                     | 1                         | and             |
      | videotrack | C1     | Completion by viewing and acknowledgement | 1       | 2          | 50                | 1                      | I acknowledge this statement | 0                     | 1                         | and             |

  Scenario: Validated viewing crosses the Moodle completion threshold
    Given "student1" watched "Completion by viewing" through "10" seconds
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Completion by viewing" "link"
    Then the VideoTrack HTML5 media is ready
    And Moodle completion for "student1" in "Completion by viewing" is "incomplete"
    When "student1" watched "Completion by viewing" through "30" seconds
    And I am on "Course 1" course homepage
    And I click on "Completion by viewing" "link"
    Then the VideoTrack HTML5 media is ready
    And Moodle completion for "student1" in "Completion by viewing" is "complete"

  Scenario: Acknowledgement-only completion is persisted by Moodle after confirmation
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Completion by acknowledgement" "link"
    Then Moodle completion for "student1" in "Completion by acknowledgement" is "incomplete"
    When I click on "#id_ackconfirm" "css_element"
    And I press "Confirm acknowledgement"
    Then I should see "Your acknowledgement has been recorded."
    And Moodle completion for "student1" in "Completion by acknowledgement" is "complete"

  Scenario: AND completion waits for acknowledgement after viewing is already sufficient
    Given "student1" watched "Completion by viewing and acknowledgement" through "30" seconds
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Completion by viewing and acknowledgement" "link"
    Then the VideoTrack HTML5 media is ready
    And Moodle completion for "student1" in "Completion by viewing and acknowledgement" is "incomplete"
    When I click on "#id_ackconfirm" "css_element"
    And I press "Confirm acknowledgement"
    Then I should see "Your acknowledgement has been recorded."
    And Moodle completion for "student1" in "Completion by viewing and acknowledgement" is "complete"
