@mod @mod_videotrack @mod_videotrack_source_policy @javascript
Feature: Site source policy controls activity choices and playback
  In order to offer only institution-approved video sources
  As a site administrator
  I need disabled sources to be unavailable without deleting existing activity data

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | One | teacher1@example.com |
      | student1 | Student | One | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    And the following "activities" exist:
      | activity | course | name | behatproviderfixture |
      | videotrack | C1 | Source policy video | youtube |

  Scenario: Existing sites offer all three supported sources by default
    Given I log in as "teacher1"
    When I am on the "Source policy video" "videotrack activity editing" page
    Then the "videosource" select box should contain "youtube"
    And the "videosource" select box should contain "vimeo"
    And the "videosource" select box should contain "upload"
    And the field "videosource" matches value "youtube"

  Scenario: A disabled alternative is absent while the current disabled source stays explicit
    Given the following config values are set as admin:
      | enableyoutube | 0 | mod_videotrack |
      | enablevimeo | 0 | mod_videotrack |
    And I log in as "teacher1"
    When I am on the "Source policy video" "videotrack activity editing" page
    Then the "videosource" select box should not contain "vimeo"
    And the "videosource" select box should contain "upload"
    And the "videosource" select box should contain "YouTube (disabled by the site administrator)"
    And the field "videosource" matches value "youtube"

  Scenario: Learners cannot launch an existing activity with a disabled source
    Given the following config values are set as admin:
      | enableyoutube | 0 | mod_videotrack |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "Source policy video" "link"
    Then I should see "The video source (YouTube) is disabled by the site administrator."
    And I should not see "My notes"
    And ".videotrack-source-unavailable" "css_element" should exist
    And ".videotrack-player-wrap" "css_element" should not exist
    And "[data-rel=fatalerror]" "css_element" should not exist
    When I press "Continue"
    Then I should see "Course 1"

  Scenario: Disabling every source never silently replaces an existing activity source
    Given the following config values are set as admin:
      | enableyoutube | 0 | mod_videotrack |
      | enablevimeo | 0 | mod_videotrack |
      | enableupload | 0 | mod_videotrack |
    And I log in as "teacher1"
    When I am on the "Source policy video" "videotrack activity editing" page
    Then the "videosource" select box should contain "YouTube (disabled by the site administrator)"
    And the "videosource" select box should not contain "vimeo"
    And the "videosource" select box should not contain "upload"
    And the field "videosource" matches value "youtube"
