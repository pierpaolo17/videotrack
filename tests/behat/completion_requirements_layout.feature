@mod @mod_videotrack @mod_videotrack_completion @javascript
Feature: VideoTrack composite completion requirements remain visually grouped
  In order to understand which requirements belong to VideoTrack
  As a VideoTrack learner
  I need the VideoTrack logic label to precede the shared Moodle requirements list

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category | enablecompletion | showcompletionconditions |
      | Course 1 | C1 | 0 | 1 | 1 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
    And the following "activities" exist:
      | activity | course | name | behathtml5fixture | completion | completionpercent | reactionsenabled | reactionsrequired | minreactions | acknowledgementenabled | acknowledgementtext | completionacknowledgement | completionlogic | grade | gradepass | completionusegrade | completionpassgrade |
      | videotrack | C1 | Grouped completion requirements | 1 | 2 | 90 | 1 | 1 | 1 | 1 | I acknowledge this statement | 1 | and | 100 | 50 | 1 | 1 |

  Scenario: The composite logic label is outside the list while its conditions retain one status item
    Given I am on the "Grouped completion requirements" "videotrack activity" page logged in as student1
    Then "//p[contains(concat(' ', normalize-space(@class), ' '), ' videotrack-completion-logic ') and normalize-space(.) = 'All of the following VideoTrack conditions:' and following-sibling::*[1][self::ul or self::ol or @role='list']]" "xpath_element" should exist
    And I should see "Require viewing at least 90% of the video" in the "[data-region='completionrequirements']" "css_element"
    And I should see "Receive a grade" in the "[data-region='completionrequirements']" "css_element"
    And I should see "Receive a passing grade" in the "[data-region='completionrequirements']" "css_element"
    And I should not see "All of the following VideoTrack conditions: Require viewing" in the "[data-region='completionrequirements']" "css_element"
    And the VideoTrack completion requirements are stacked vertically
