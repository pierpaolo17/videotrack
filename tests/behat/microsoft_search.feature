@mod @mod_videotrack @mod_videotrack_provider_check @javascript
Feature: Personal Microsoft video search requires explicit protected input
  In order to find a reusable video identity without importing media
  As a site administrator
  I need local validation and guarded personal metadata search

  Background:
    Given the following config values are set as admin:
      | microsoftissuerid | 0 | mod_videotrack |
    And I log in as "admin"
    And I navigate to "Plugins > Activity modules > VideoTrack" in site administration
    And I follow "Microsoft connection check"
    And I follow "Find Microsoft videos"

  Scenario: Opening search does not submit a query or initialise a player
    Then I should see "Search text"
    And I should not see "Verification result"
    And "iframe" "css_element" should not exist

  Scenario: Invalid drive and query are rejected locally
    When I set the field "Drive ID" to "https://example.org"
    And I set the field "Search text" to "x"
    And I press "Search videos"
    Then I should see "Enter an ID of 1–256 letters, digits, !, _ or -. URLs and paths are not accepted."
    And I should see "Enter between 2 and 100 characters without line breaks."
    And I should not see "Verification result"

  Scenario: A valid search without configuration returns a normal failure
    When I set the field "Drive ID" to "drive"
    And I set the field "Search text" to "lesson"
    And I press "Search videos"
    Then I should see "Verification result"
    And I should see "not_configured"
    And "#page-mod-videotrack-provider_search .fatalerror" "css_element" should not exist
