@javascript @core @blocktype @outcomes @activitypage @group @tutor @activity @checkpointcomment
Feature: Creating outcome portfolios with activity pages and checkpoint blocks
  As a group admin/tutor
  I want to set up outcomes portfolios as a background step
  As a group admin/tutor
  I want to create new activities for each outcome and mange the achievement of each activity
  As a group member
  I want to keep track of my progress of activities as I work on them

  Background: Set up background data and interact with outcomes portfolio as a group ADMIN
    And the following "institutions" exist:
      | name       | displayname | registerallowed | registerconfirm | allowinstitutionpublicviews | commentsortorder |
      | school     | school      | ON              | OFF             | 1                           | latest           |
      | university | university  | ON              | OFF             | 1                           | latest           |

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

    Given the following "users" exist:
      | username      | password  | email                     | firstname    | lastname | institution | authname | role   |
      | Group1_Tutor  | Kupuh1pa! | Group1_Tutor@example.org  | Archie_Tutor | Mahara   | school      | internal | admin  |
      | Group1_Member | Kupuh1pa! | Group1_Member@example.org | Bella_Member | Mahara   | school      | internal | member |
      | Group2_Tutor  | Kupuh1pa! | Group2_Tutor@example.org  | Callum_Tutor | Mahara   | university  | internal | admin  |
      | Group2_Member | Kupuh1pa! | Group2_Member@example.org | Ducky_Member | Mahara   | university  | internal | member |

    And the following "groups" exist:
      | name   | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members       | staff        | institution |
      | Group1 | admin | G1 Outcomes | outcomes  | ON   | ON            | all       | ON            | ON            | Group1_Member | Group1_Tutor | school      |
      | Group2 | admin | G2 Outcomes | outcomes  | ON   | ON            | all       | ON            | ON            | Group2_Member | Group2_Tutor | university  |

    And the following "pages" exist:
      | title            | description    | ownertype | ownername |
      | Portfolio page A | Group 1 Page 1 | group     | Group1    |
      | Portfolio page B | Group 2 Page 1 | group     | Group2    |

    And the following "collections" exist:
      | title     | ownertype | ownername | description                                                                     | pages            |
      | Portfolio | group     | Group1    | Portfolio to check that outcomes is checked by default institution setting rule | Portfolio page A |
      | Portfolio | group     | Group2    | Portfolio to check that outcomes is checked by default institution setting rule | Portfolio page B |

    # GROUP ADMIN/TUTOR SETUP  🟡 Only group admins can create an outcome collection
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to group "Group1"
    When I click on "Portfolios" in the "Arrow-bar nav" "Nav" property

    # Create an collection
    Then I click on "Create" in the "Create" "Views" property
    * I click on "Collection" in the dialog
    * I fill in the following:
      | Collection name        | Outcome collection               |
      | Collection description | Outcomes portfolio 2 description |
    And I click on "Continue"

    # Create an outcome
    When I fill in the following:
      | Short title | Going to school                              |
      | Full title  | Navigating public transport to get to school |
    * I select "Communication & interaction" from "Outcome type"
    And I click on "Save"

    # Add activity
    When I expand the section "Going to school"
    And I click on "Add activity"
    And I fill in the following:
      | Page title           | Taking public transport                      |
      | Activity description | Navigating public transport to get to school |
    And I click on "Save"

    # Add checkpoint block
    When I click on the add block button
    * I click on "Add" in the "Add new block" "Blocks" property
    * I click on blocktype "Checkpoint"
    * I click on "Save" in the "Save block" "Blocks" property
    And I click on "Add checkpoint comment"
    * I fill in "You are simply the best" in editor "Comment"
    * I click on input "checkpoint_comment[submit]"
    Then I log out

 Scenario Outline: Auto-numbering outcomes
    Given I log in as "<person>" with password "Kupuh1pa!"
    * I go to group "Group1"
    * I click on "Portfolios" in the "Arrow-bar nav" "Nav" property
    And I click on "Manage" in "Outcome collection" card menu
    Then I click on "Add an outcome"
    And I should see "Outcome 2"
    And I set the field "Short title" in "#outcome1" "css_element" to "Reading the timetable"
    Then I click on "Add an outcome"
    And I should see "Outcome 3"
    And I set the field "Short title" in "#outcome2" "css_element" to "Setting an alarm"
    Then I click on "Add an outcome"
    And I should see "Outcome 4"
    And I set the field "Short title" in "#outcome3" "css_element" to "Calculating time"
    And I click on "Delete Outcome 3"
    And I should not see "Outcome 4"
    And I click on "Save"
    Then I should see "Calculating time"
    And I should not see "Setting an alarm"

    Examples:
    | person       |
    | admin        |

# Multiple test cases:
# - Checkpoint block: Click 'Add comment' to add a comment to the checkpoint. Comments are displayed in reverse chronological order (as defined by institution setting).
  Scenario Outline: Adding another checkpoint block and comments
    Given I log in as "<person>" with password "Kupuh1pa!"
    * I go to group "Group1"
    * I click on "Portfolios" in the "Arrow-bar nav" "Nav" property
    * I click on "Outcome collection"
    * I expand the section "Going to school"
    * I click on "Taking public transport" in the "Activity pages" "Outcomes" property

    # Add checkpoint comment feedback
    And I <can_delete> the "You are simply the best" row
    And I wait "1" seconds
    Then I <see_deleted_msg> "Checkpoint comment deleted"
    And I click on "Add checkpoint comment"
    * I fill in "<commenttext>" in editor "Comment"
    * I click on input "checkpoint_comment[submit]"
    And I click on "Add checkpoint comment"
    * I fill in "<commenttext2>" in editor "Comment"
    * I click on input "checkpoint_comment[submit]"
    And "<commenttext2>" "text" should appear before "<commenttext>" "text"
    # Add checkpoint block
    When I click on the add block button
    * I click on "Add" in the "Add new block" "Blocks" property
    * I click on blocktype "Checkpoint"
    * I click on "Save" in the "Save block" "Blocks" property
    Then I should see "Checkpoint 2"
    And I click on "Add checkpoint comment"
    * I fill in "<commenttext>" in editor "Comment"
    * I click on input "checkpoint_comment[submit]"

    Examples:
      | person        | deletecommenttext                      | can_delete    | commenttext                                                 | commenttext2          | see_deleted_msg |
      | Group1_Tutor  | You are simply the best                | delete        | Group1_Tutor is making great progress!                      | Tutor second comment  | should see      |
      | Group1_Member | Group1_Tutor is making great progress! | cannot delete | Group1_Member thinks Group1_Tutor is making great progress! | Member second comment | should not see  |

 Scenario Outline: Strategies and support checking after signing off page (samething as setting activity = achieved)
   # Sign off page = set activity (page) to achieved on the *activity page* with the switch
   Given I log in as "<person>" with password "Kupuh1pa!"
   * I go to group "Group1"
   * I click on "Outcome collection"
   * I expand the section "Going to school"
   * I click on "Taking public transport" in the "Activity pages" "Outcomes" property
   * I expand the section "Navigating public transport to get to school"
   * I fill in "Strategies and support" with "My new strategy"
   And I click on "Save" in the "#activity_support_strategy_support_submit_container" "css_element"
   * I fill in "Resources" with "My new resource"
   And I click on "Save" in the "#activity_support_resources_support_submit_container" "css_element"
   * I fill in "Learner support" with "My new learner support"
   And I click on "Save" in the "#activity_support_learner_support_submit_container" "css_element"
   And I click on "signoff"
   And I click on "Yes" in the "Sign off activity page" "Outcomes" property
   And the "signoff" checkbox should be checked
   * I expand the section "Navigating public transport to get to school"
   And I should see "<personsignoff>, on" in the "#activity_support" "css_element"
   And I log out

   Examples:
   | person       | personsignoff       |
   | admin        | Admin Account       |
   | Group1_Tutor | Archie_Tutor Mahara |

 Scenario: Strategies and support checking normal member cannot sign off an activity page
   Given I log in as "Group1_Member" with password "Kupuh1pa!"
   * I go to group "Group1"
   * I click on "Outcome collection"
   * I expand the section "Going to school"
   * I click on "Taking public transport" in the "Activity pages" "Outcomes" property
   Then I see that "Signed off" is displayed disabled
   And I log out

 Scenario: Strategies and support checking after signing off page (samething as setting activity = achieved)
   # Display who made the last change and when once the outcome has been completed.
   # Sign off page = set activity (page) to achieved on the *outcomes page*
   # Tutor signs off page
   # Group admin completes the outcome
   Given I log in as "Group1_Tutor" with password "Kupuh1pa!"
   * I go to group "Group1"
   * I click on "Outcome collection"
   When I expand the section "Going to school"
   Then I click on "Sign off activity 'Taking public transport'"
   And I click on "Yes" in the "Sign off activity page" "Outcomes" property
   Then I should see "Activity status has been updated"
   Then I log out

   # Log in as group member to check the disabled buttons and line for outcomes and activity permissions
   And I log in as "Group1_Member" with password "Kupuh1pa!"
   * I go to group "Group1"
   * I click on "Outcome collection"
   Then an "a" element should contain the text "Mark outcome 'Going to school' as completed is disabled" in the "title" attribute
   When I expand the section "Going to school"
   Then a "span" element should contain the text "Activity 'Taking public transport' has been signed off" in the "title" attribute
   Then I log out

   # Mark the outcome as completed as only admin can do this
   Given I log in as "admin" with password "Kupuh1pa!"
   * I go to group "Group1"
   * I click on "Outcome collection"
   Then I click on "Mark outcome 'Going to school' as completed"
   Then I click on "Yes" in the "Modal content" "Modal" property
   Then an "a" element should contain the text "Outcome 'Going to school' has been completed. Click to reset it." in the "title" attribute
   And I log out

   # Check as a member that the outcome is completed
   And I log in as "Group1_Member" with password "Kupuh1pa!"
   * I go to group "Group1"
   * I click on "Outcome collection"
   Then an "a" element should contain the text "Outcome 'Going to school' has been completed" in the "title" attribute
   Then I log out
   # Now reset the outcome as incomplete again and un-sign off the page (AKA set the activity as not achieved)
   Given I log in as "admin" with password "Kupuh1pa!"
   * I go to group "Group1"
   * I click on "Outcome collection"
   And I click on "Outcome 'Going to school' has been completed. Click to reset it."
   Then I click on "Yes" in the "Incomplete outcome form" "Outcomes" property
   Then an "a" element should contain the text "Mark outcome 'Going to school' as completed" in the "title" attribute
   When I expand the section "Going to school"
   And I click on "Remove sign-off on activity 'Taking public transport'"
   Then I click on "Yes" in the "Un-sign off activity page" "Outcomes" property
   And I log out
