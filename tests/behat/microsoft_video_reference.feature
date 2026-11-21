@mod @mod_videotrack @mod_videotrack_provider_check @javascript
Feature: Reusable Microsoft identities do not grant access or reuse temporary URLs
  In order to repeat an explicit personal video diagnostic
  As a site administrator
  I need locally validated identities without credentials or stale preview links

  Background:
    Given the following config values are set as admin:
      | microsoftissuerid | 0 | mod_videotrack |
    And I log in as "admin"
    And I navigate to "Plugins > Activity modules > VideoTrack" in site administration
    And I follow "Microsoft connection check"

  Scenario: Opening the diagnostic keeps manual input and does not export an identity
    Then the field "Target selection" matches value "manual"
    And "#videotrack-microsoft-reference" "css_element" should not exist
    And "#videotrack-microsoft-preview" "css_element" should not exist
    And I should not see "Verification result"

  Scenario: A URL cannot be used as a reusable Microsoft identity
    When I select "My connected Microsoft account" from the "Account to check" singleselect
    And I select "Use a reusable video reference" from the "Target selection" singleselect
    And I set the field "Microsoft video reference" to "https://tenant.sharepoint.com/embed.aspx?tempauth=private-marker"
    And I press "Check Microsoft access"
    Then I should see "Enter a valid VideoTrack Microsoft video reference. URLs and credentials are not supported."
    And I should not see "Verification result"
    And "#videotrack-microsoft-reference" "css_element" should not exist
    And "#videotrack-microsoft-preview" "css_element" should not exist

  Scenario: A reference cannot silently switch the configured service or use a shared account
    When I select "Use a reusable video reference" from the "Target selection" singleselect
    And I set the following fields to these values:
      | Microsoft video reference | {"version":1,"provider":"microsoft","issuerid":7,"driveid":"drive","itemid":"video"} |
    And I press "Check Microsoft access"
    Then I should see "Select your connected personal Microsoft account for this action."
    And I should see "This reference uses a different Microsoft OAuth service. Restore that service or check the video again using its IDs."
    And I should not see "Verification result"
    And "#videotrack-microsoft-reference" "css_element" should not exist
    And "#videotrack-microsoft-preview" "css_element" should not exist
