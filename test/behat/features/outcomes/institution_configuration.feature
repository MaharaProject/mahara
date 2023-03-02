@javascript @core @blocktype @outcomes @activitypage @group @tutor
Feature: Outcomes portfolio is available in the institution settings
  As a site admin
  Check that outcomes portfolio is available within institution settings

  Background: Set up background data
    And the following "institutions" exist:
      | name       | displayname | registerallowed | registerconfirm | allowinstitutionpublicviews |
      | school     | school      | ON              | OFF             | 1                           |
      | university | university  | ON              | OFF             | 1                           |

    Given the following "outcometypes" exist:
      | abbreviation | title                       | styleclass | outcome_category   | institution |
      | CL           | Cognition & learning        | outcome1   | Outcome category 1 | school      |
      | CI           | Communication & interaction | outcome1   | Outcome category 1 | school      |
      | PH           | Physical health             | outcome2   | Outcome category 2 | university  |
      | PS           | Problem solving             | outcome2   | Outcome category 2 | university  |

    Given the following "outcomesubjects" exist:
      | abbreviation | title   | subject_category | institution |
      | MT           | Maths   | Level 1          | school      |
      | EN           | English | Level 1          | school      |
      | MT           | Algebra | Level 2          | university  |
      | EN           | Essays  | Level 2          | university  |

  Scenario: Group changes: Turn on 'Outcomes portfolio' option in an institution
    Given I log in as "admin" with password "Kupuh1pa!"
    * I choose "Settings" in "Institutions" from administration menu
    * I click on "Edit" in "school" row
    And I enable the switch "Outcomes portfolio"
    And I click on "Submit"
    And I choose "Groups" in "Engage" from main menu
    And I click on "Create group"
    Then I wait "1" seconds
    And I set the following fields to these values:
    | Group name           | Outcome group                                |
    | Group description    | Description for group with outcome grouptype |
    | Group type and roles | Outcomes: Member, Tutor, Admin               |
    And I click on "Save group"
    And I should see "Group saved successfully"
    And I log out