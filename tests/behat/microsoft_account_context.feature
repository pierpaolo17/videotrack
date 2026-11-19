@mod @mod_videotrack @mod_videotrack_provider_check @javascript
Feature: Microsoft diagnostics distinguish shared and personal accounts
  In order to avoid treating a shared account as learner authorisation
  As a site administrator
  I need to choose the identity used by an explicit metadata check

  Background:
    Given the following config values are set as admin:
      | microsoftissuerid | 0 | mod_videotrack |
    And I log in as "admin"
    And I navigate to "Plugins > Activity modules > VideoTrack" in site administration
    And I follow "Microsoft connection check"

  Scenario: Existing diagnostics keep the shared account as the explicit default
    Then the field "Account to check" matches value "system"
    And I should see "My connected Microsoft account"
    And I should not see "Verification result"

  Scenario: Personal-account verification keeps its context when configuration is missing
    When I select "My connected Microsoft account" from the "Account to check" singleselect
    And I set the field "Drive ID" to "b!Demo_drive"
    And I press "Check Microsoft access"
    Then I should see "Verification result"
    And the field "Account to check" matches value "current_user"
    And I should see "not_configured"
    And I should see "current_user"
    And "#page-mod-videotrack-provider_check .fatalerror" "css_element" should not exist
