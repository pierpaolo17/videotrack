@mod @mod_videotrack @mod_videotrack_provider_check @javascript
Feature: Personal Microsoft connection is explicit and depends on local readiness
  In order to connect my personal Microsoft account using Moodle
  As a site administrator
  I need local diagnostics to remain available without starting OAuth

  Scenario: Missing configuration offers diagnostics without a connection button
    Given the following config values are set as admin:
      | microsoftissuerid | 0 | mod_videotrack |
    And I log in as "admin"
    When I navigate to "Plugins > Activity modules > VideoTrack" in site administration
    And I follow "Microsoft connection check"
    Then "Connect my Microsoft account" "button" should not exist
    And the field "Action" matches value "metadata"
    And I should see "not_configured"
    And I should not see "Verification result"
    And "Open in Microsoft" "link" should not exist
