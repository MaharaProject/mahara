@javascript @core @blocktype @outcomes @activitypage @group @tutor @activity @checkpoint @outcome_access
Feature: Roles and permissions around functionality in outcomes portfolios
  Ensure that group admins, group tutors, and group members have the correct access

# Test cases covered in background
# Group changes: New database tables 'Outcome category', and 'Outcome subject' related.
  Background: Set up background data and interact with outcomes portfolio as a group ADMIN
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
    Then I log out

# WORKS!!

  Scenario Outline: Outcomes collection: 'Manage pages' is not on the 'Portfolios' overview page
    When I log in as "<person>" with password "Kupuh1pa!"
    * I go to group "Group1"
    * I click on "Portfolios" in the "Arrow-bar nav" "Nav" property
    And I cannot click on "Manage pages" in "Outcome collection" card menu

    Examples: group admin = admin, Group1_Tutor = tutor, Group1_Member = member
      | person        |
      | admin         |
      | Group1_Tutor  |
      | Group1_Member |

  Scenario Outline: Outcomes collection: can see and action the 'Configure' button on the 'Outcomes page'
    When I log in as "<person>" with password "Kupuh1pa!"
    * I go to group "Group1"
    * I click on "Portfolios" in the "Arrow-bar nav" "Nav" property
    * I click on "Outcome collection"
    Then I <can_configure_outcomes>

    Examples: group admin = admin, Group1_Tutor = tutor, Group1_Member = member
      | person        | can_configure_outcomes              |
      | admin         | click on "Configure outcomes"       |
      | Group1_Tutor  | should not see "Configure outcomes" |
      | Group1_Member | should not see "Configure outcomes" |


  Scenario Outline: Can access the option 'Manage' from the collection settings screen and also on the
'Portfolios' overview page when clicking the 'More options' icon on a collection for group admins only.
    When I log in as "<person>" with password "Kupuh1pa!"
    * I go to group "Group1"
    * I click on "Portfolios" in the "Arrow-bar nav" "Nav" property
    And I <can_manage_edit_outcomes> "Manage" in "Outcome collection" card menu
    Then I <do_next_step>
    And I <can_manage_edit_outcomes> "Edit" in "Outcome collection" card menu
    And I <continue>
    Then I should <see_outcome1>

    Examples: group admin = admin, Group1_Tutor = tutor, Group1_Member = member
      | person        | can_manage_edit_outcomes | do_next_step | continue            | see_outcome1        |
      | admin         | click on                 | go back      | click on "Continue" | see "Outcome 1"     |
      | Group1_Tutor  | cannot click on          | do nothing   | do nothing          | not see "Outcome 1" |
      | Group1_Member | cannot click on          | do nothing   | do nothing          | not see "Outcome 1" |

  # Multiple cases
  # - Group changes: can change 'Prevent removing of blocks' setting on activity page setting to delete blocks
  # - Outcomes collection: Progress: Text is saved when the 'Save' button is pressed.
  # - Outcomes collection: 'Add activity': Clicking the button sets up a new page in this collection where you can fill in all the activity information.
  # - Outcomes collection: The full outcome text from the 'Full title' of the outcome is displayed in the uncollapsed panel.
  #- Outcomes collection: Support is taking place: This is a Yes/No switch whose status is saved immediately.
  # - Outcomes collection: Progress: Text is saved when the 'Save' button is pressed.
  Scenario Outline: Multi-case outcomes collection + group changes
    Given I log in as "<person>" with password "Kupuh1pa!"
    * I go to group "Group1"
    * I click on "Outcome collection"
    * I expand the section "Going to school"
    Then I should see "Navigating public transport to get to school"
    And I enable the switch "Support is taking place"
    And I fill in the following:
      | Progress | Happy days progress! |
    And I click on "Save"
    Then I click on "Add activity"
    And I fill in the following:
      | Page title           | Walking to the bus stop   |
      | Activity description | Reading the bus stop info |
    # 🟡 Check: Hard-coded subject
    # 🟡 Check: Staff is the default currently logged in person
    * I should see "Level 1 - Maths"
    * I should see "<responsible_staff>"
    And I set the following fields to these values:
      | Prevent removing of blocks | 0 |
    Then I click on "Save"
    And I should see "Walking to the bus stop | Edit"
    And I click on "Reading the bus stop info"
    * I should see "Maths"
    * I should see "<responsible_staff>"
    Then I click on "Reading the bus stop info"
    And I log out

    Examples: group admin = admin, Group1_Tutor = tutor, Group1_Member = member
      | person       | responsible_staff                  | do_next_step | continue            |
      | admin        | Admin Account (admin)              | go back      | click on "Continue" |
      | Group1_Tutor | Archie_Tutor Mahara (Group1_Tutor) | do nothing   | do nothing          |

  Scenario: Outcomes collection: 'Add activity': Group member cannot create activity
    Given I log in as "Group1_Member" with password "Kupuh1pa!"
    * I go to group "Group1"
    * I click on "Outcome collection"
    * I expand the section "Going to school"
    And I should not see "Add activity"

  # Multiple test cases
  # - Group changes: Can see the 'Create' button on the 'Portfolios' overview page
  # - Group changes: Can see the 'Copy' button on the 'Portfolios' overview page.
  # - Group changes: Can change the sharing permissions for a portfolio either directly on a page or via the 'Portfolios' overview page.
  Scenario Outline: Check create/copy/manage access permissions
    When I log in as "<person>" with password "Kupuh1pa!"
    * I go to group "Group1"
    * I click on "Portfolios" in the "Arrow-bar nav" "Nav" property
    Then I should <see_create> in the "Create" "Views" property
    And I should <see_copy> in the "Create" "Views" property
    And I <can_manage_access> in "Outcome collection" card access menu
    Then I <go_back>
    And I <can_manage_outcomes> in "Outcome collection" card menu
    Then I <go_back>
    Then I click on "Outcome collection"
    * I expand the section "Going to school"
    * I click on "Taking public transport" in the "Activity pages" "Outcomes" property
    Then I <click_share> in the "Toolbar buttons" "Nav" property

    Examples: group admin = admin, Group1_Tutor = tutor, Group1_Member = member
      | person        | see_create       | see_copy       | can_manage_access               | click_share            | can_manage_outcomes               | go_back    | can_see_settings_on_edit  |
      | admin         | see "Create"     | not see "Copy" | click on "Manage access"        | click on "Share"       | click on "Manage outcomes"        | go back    | should see "Settings"     |
      | Group1_Tutor  | not see "Create" | not see "Copy" | click on "Manage access"        | click on "Share"       | cannot click on "Manage outcomes" | go back    | should see "Settings"     |
      | Group1_Member | not see "Create" | not see "Copy" | cannot click on "Manage access" | should not see "Share" | cannot click on "Manage outcomes" | do nothing | should not see "Settings" |

  Scenario Outline: Can delete an entire outcomes portfolio or individual pages within it in a group.
    When I log in as "<person>" with password "Kupuh1pa!"
    * I go to group "Group1"
    * I click on "Portfolios" in the "Arrow-bar nav" "Nav" property
    And I <can_delete_outcomes> in "Outcome collection" card menu
    Then I <see_delete_collection>
    * I <click_yes>
    And I <see_confirmation> "Collection deleted successfully"

    Examples: group admin = admin, Group1_Tutor = tutor, Group1_Member = member
      | person        | can_delete_outcomes      | see_delete_collection                                   | click_yes      | see_confirmation |
      | admin         | click on "Delete"        | should see "Delete collection 'Outcome collection'"     | click on "Yes" | should see       |
      | Group1_Tutor  | click on "Delete"        | should see "Delete collection 'Outcome collection'"     | click on "Yes" | should see       |
      | Group1_Member | cannot click on "Delete" | should not see "Delete collection 'Outcome collection'" | do nothing     | should not see   |

  Scenario Outline: Progress: Display the text without the text field and the 'Save' button when the outcome hasn't been marked as completed yet.
    # Display who made the last change and when once the outcome has not been completed.
    Given I log in as "admin" with password "Kupuh1pa!"
    * I go to group "Group1"
    * I click on "Outcome collection"
    * I expand the section "Going to school"
    Then I should see "Navigating public transport to get to school"
    And I enable the switch "Support is taking place"
    And I fill in the following:
      | Progress | Happy days progress! |
    And I click on "Save"
    Then I log out
    When I log in as "<person>" with password "Kupuh1pa!"
    Then I click on "Outcome collection"
    Then I expand the section "Going to school"
    And I should see "Happy days progress!"
    But I <can_see_save> "Save" in the "Progress form" "Outcomes" property
    And I <can_see_author> "Admin Account, on" in the "Progress form" "Outcomes" property

    Examples: group admin = admin, Group1_Tutor = tutor, Group1_Member = member
      | person        | can_see_save   | can_see_author |
      | admin         | should see     | should not see |
      | Group1_Tutor  | should see     | should not see |
      | Group1_Member | should not see | should see     |

  Scenario Outline: Mark outcome complete
    # Set outcome to be complete
    Given I log in as "<person>" with password "Kupuh1pa!"
    * I go to group "Group1"
    * I click on "Outcome collection"
    Then I click on "Mark outcome 'Going to school' as completed"
    And I should see "Are you sure you want to mark this outcome as completed"
    And I click on "Yes" in the "Complete outcome" "Outcomes" property
    When I expand the section "Going to school"
    Then I should not see "Save" in the "Sign off activity page" "Outcomes" property

    Examples: group admin = admin, Group1_Tutor = tutor, Group1_Member = member
      | person       | can_set_outcome_complete |
      | admin        | should see               |
      | Group1_Tutor | should see               |