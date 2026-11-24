@mod @mod_videotrack @mod_videotrack_microsoft_reference @javascript
Feature: Optional Microsoft reference preparation keeps the active player unchanged
  In order to prepare a future Microsoft source without losing watched evidence
  As an editing teacher
  I need empty references to be harmless and invalid references to block saving

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | One | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
    And the following "activities" exist:
      | activity | course | name | behatproviderfixture |
      | videotrack | C1 | Prepared video | youtube |
    And I log in as "teacher1"
    And I am on the "Prepared video" "videotrack activity editing" page
    And I expand all fieldsets

  Scenario: Saving an empty prepared reference retains the selected playable source
    Then the field "microsoftreference" matches value ""
    And the "videosource" select box should not contain "microsoft"
    When I press "Save and return to course"
    And I am on the "Prepared video" "videotrack activity editing" page
    And I expand all fieldsets
    Then the field "videosource" matches value "youtube"
    And the field "microsoftreference" matches value ""

  Scenario: URLs and credentials cannot be saved as a prepared reference
    When I set the field "microsoftreference" to "https://example.org/private-marker"
    And I press "Save and return to course"
    Then I should see "Enter a valid Microsoft reference and match or explicitly bind it to the selected enabled Microsoft service."
    And the field "videosource" matches value "youtube"
    And I should not see "Exception"
