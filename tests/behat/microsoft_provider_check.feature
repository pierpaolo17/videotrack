@mod @mod_videotrack @mod_videotrack_provider_check @javascript
Feature: Administrators explicitly inspect the Microsoft connection
  In order to diagnose a provider connection before enabling future playback
  As a site administrator
  I need a local status page and a protected metadata form

  Background:
    Given the following config values are set as admin:
      | microsoftissuerid | 0 | mod_videotrack |
    And I log in as "admin"

  Scenario: The settings link opens local diagnostics without running a probe
    Given I navigate to "Plugins > Activity modules > VideoTrack" in site administration
    When I follow "Microsoft connection check"
    Then I should see "Local configuration"
    And I should see "not_configured"
    And I should not see "Verification result"
    And "Drive ID" "field" should exist
    And "Video item ID (optional)" "field" should exist

  Scenario: Sharing URLs are rejected before a remote check
    Given I navigate to "Plugins > Activity modules > VideoTrack" in site administration
    And I follow "Microsoft connection check"
    When I set the field "Drive ID" to "https://example.org/share/private"
    And I press "Check Microsoft access"
    Then I should see "Enter an ID of 1–256 letters, digits, !, _ or -. URLs and paths are not accepted."
    And I should not see "Verification result"

  Scenario: An unconfigured service gives a normal diagnostic result
    Given I navigate to "Plugins > Activity modules > VideoTrack" in site administration
    And I follow "Microsoft connection check"
    When I set the field "Drive ID" to "b!Demo_drive"
    And I press "Check Microsoft access"
    Then I should see "Verification result"
    And I should see "Access could not be verified. See the diagnostic status below."
    And I should see "not_configured"
    And "#page-mod-videotrack-provider_check .fatalerror" "css_element" should not exist
