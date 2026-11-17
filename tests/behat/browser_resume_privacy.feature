@mod @mod_videotrack @mod_videotrack_browser_resume_privacy @javascript
Feature: Browser resume hints belong to the current learner
  In order to use a shared browser without inheriting another learner's position
  As a participating learner
  I need browser hints separated by learner and old shared keys ignored

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | First | Learner | first@example.com |
      | student2 | Second | Learner | second@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | C1 | student |
      | student2 | C1 | student |
    And the following "activities" exist:
      | activity | course | name | behatproviderfixture | durationseconds | autoplay | resumeplayback | loopenabled | randomfocuspauses | pauseonfocusloss | allowseekforward |
      | videotrack | C1 | YouTube resume privacy | youtube | 60 | 0 | 1 | 0 | 0 | 0 | 1 |
      | videotrack | C1 | Vimeo resume privacy | vimeo | 60 | 0 | 1 | 0 | 0 | 0 | 1 |

  Scenario Outline: Shared browser hints stay scoped for each provider
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I click on "<Activity>" "link"
    Then the deterministic VideoTrack "<Provider>" provider is ready
    When I store a VideoTrack browser resume hint of "30" seconds
    And I reload the page
    Then the deterministic VideoTrack "<Provider>" provider is ready
    And the "<Provider>" provider time is between "29" and "31"
    When I log out
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I restore stale VideoTrack browser hints before opening "<Activity>"
    And I click on "<Activity>" "link"
    Then the deterministic VideoTrack "<Provider>" provider is ready
    And the VideoTrack browser resume key belongs to a different learner
    And the "<Provider>" provider time is between "0" and "2"

    Examples:
      | Activity | Provider |
      | YouTube resume privacy | youtube |
      | Vimeo resume privacy | vimeo |
