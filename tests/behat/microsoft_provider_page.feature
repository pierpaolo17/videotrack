@mod @mod_videotrack @mod_videotrack_provider_check @javascript
Feature: Microsoft page links stay separate from tracked playback
  In order to open the original provider page without importing a video
  As a site administrator
  I need an explicit personal-account action and honest validation

  Background:
    Given the following config values are set as admin:
      | microsoftissuerid | 0 | mod_videotrack |
    And I log in as "admin"
    And I navigate to "Plugins > Activity modules > VideoTrack" in site administration
    And I follow "Microsoft connection check"

  Scenario: Existing checks retain metadata as the default action
    Then the field "Action" matches value "metadata"
    And "Open in Microsoft" "link" should not exist
    And I should not see "Verification result"

  Scenario: A provider page cannot be requested through the shared account or without an item
    When I select "Resolve Microsoft page link" from the "Action" singleselect
    And I set the field "Drive ID" to "drive"
    And I press "Check Microsoft access"
    Then I should see "Select your connected personal Microsoft account for this action."
    And I should see "Enter the video item ID to resolve its Microsoft page."
    And "Open in Microsoft" "link" should not exist
    And I should not see "Verification result"

  Scenario: Missing configuration returns a normal result without a provider link
    When I select "Resolve Microsoft page link" from the "Action" singleselect
    And I select "My connected Microsoft account" from the "Account to check" singleselect
    And I set the field "Drive ID" to "drive"
    And I set the field "Video item ID (optional)" to "video"
    And I press "Check Microsoft access"
    Then I should see "Verification result"
    And I should see "not_configured"
    And "Open in Microsoft" "link" should not exist
    And "#page-mod-videotrack-provider_check .fatalerror" "css_element" should not exist
