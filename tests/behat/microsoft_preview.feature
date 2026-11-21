@mod @mod_videotrack @mod_videotrack_provider_check @javascript
Feature: Microsoft diagnostic previews require an explicit personal request
  In order to test a provider preview without exposing shared-account access
  As a site administrator
  I need personal-account validation and a local-only default page

  Background:
    Given the following config values are set as admin:
      | microsoftissuerid | 0 | mod_videotrack |
    And I log in as "admin"
    And I navigate to "Plugins > Activity modules > VideoTrack" in site administration
    And I follow "Microsoft connection check"

  Scenario: Opening the diagnostic does not create a Microsoft preview
    Then the field "Action" matches value "metadata"
    And "#videotrack-microsoft-preview" "css_element" should not exist
    And I should not see "Verification result"

  Scenario: A preview cannot use the shared account or omit a video item
    When I select "Preview with Microsoft player" from the "Action" singleselect
    And I set the field "Drive ID" to "drive"
    And I press "Check Microsoft access"
    Then I should see "Select your connected personal Microsoft account for this action."
    And I should see "Enter the video item ID to request a private preview."
    And "#videotrack-microsoft-preview" "css_element" should not exist
    And I should not see "Verification result"

  Scenario: A personal preview with missing configuration returns a normal failure
    When I select "Preview with Microsoft player" from the "Action" singleselect
    And I select "My connected Microsoft account" from the "Account to check" singleselect
    And I set the field "Drive ID" to "drive"
    And I set the field "Video item ID (optional)" to "video"
    And I press "Check Microsoft access"
    Then I should see "Verification result"
    And I should see "not_configured"
    And "#videotrack-microsoft-preview" "css_element" should not exist
    And "#page-mod-videotrack-provider_check .fatalerror" "css_element" should not exist
