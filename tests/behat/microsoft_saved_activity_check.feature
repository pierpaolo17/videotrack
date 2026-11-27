@mod @mod_videotrack @mod_videotrack_saved_microsoft_check @javascript
Feature: Administrators inspect the Microsoft identity saved in an activity
  In order to prepare a future Microsoft video without copying identifiers
  As a site administrator
  I need an activity-bound diagnostic that preserves the active video

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
      | videotrack | C1 | Saved Microsoft preparation | youtube |

  Scenario: The saved-reference link opens a local diagnostic in another window
    Given I log in as "admin"
    And I am on the "Saved Microsoft preparation" "videotrack activity editing" page
    And I expand all fieldsets
    When I follow "Check the saved Microsoft reference"
    And I switch to a second window
    Then I should see "Saved Microsoft preparation"
    And I should see "Saved activity"
    And "Drive ID" "field" should not exist
    And "#id_accountcontext" "css_element" should not exist
    And "#id_targetmode" "css_element" should not exist
    And I should not see "Verification result"
    When I press "Check Microsoft access"
    Then I should see "Enter a valid Microsoft reference and match or explicitly bind it to the selected enabled Microsoft service."
    And I should not see "Verification result"
    And "#videotrack-microsoft-preview" "css_element" should not exist
    And I close all opened windows

  Scenario: A course teacher cannot use the administrator shortcut
    Given I log in as "teacher1"
    And I am on the "Saved Microsoft preparation" "videotrack activity editing" page
    And I expand all fieldsets
    Then "Check the saved Microsoft reference" "link" should not exist
    And the "videosource" select box should not contain "microsoft"
