@javascript @core @core_content
Feature: Creating a plan and adding a number of tasks to the plan
As a user
In order to test the pagination of the plan

Background:
    Given the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page admin_01 | Page 01 | user | admin |

Scenario: Create a plan -> add plan block to page -> create new task from block on page
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Plans" in "Create" from main menu
    And I follow "New plan"
    And I fill in the following:
    | Title *  | Plan 9 from outer space  |
    | Description  | Woooo |
    And I fill in select2 input "addplan_tags" with "plan" and select "plan"
    And I fill in select2 input "addplan_tags" with "test" and select "test"
    And I press "Save plan"
    #  add plan block to page
    Given I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page admin_01" card menu
    When I follow "Add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on "Show more"
    And I click on "Show more"
    And I click on "Plans" in the "Content types" property
    And I set the field "Block title" to ""
    # select the plan to display in block
    And I check "Plan 9 from outer space"
    And I press "Save"
    Then I should see "Woooo" in the block "Plan 9 from outer space"
    # create new task from block on page
    When I follow "Add task"
    Then I should see " New task"
    When I fill in the following:
    | Title | New Space Task |
    | Completion date | 2019/01/01 |
    | Description | Space Task - hold breath for a really long time |
    And I enable the switch "Completed"
    And I press "Save task"
    # confirm user taken back to Plan block on page
    Then I should see " Page admin_01"
    And I should see "Woooo" in the block "Plan 9 from outer space"
    And I should see "New Space Task" in the block "Plan 9 from outer space"

Scenario: Creating a plan with 11 tasks (Bug #1503036)
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Plans" in "Create" from main menu
    And I follow "New plan"
    And I fill in the following:
    | Title *  | Plan 9 from outer space  |
    | Description  | Woooo |
    And I fill in select2 input "addplan_tags" with "plan" and select "plan"
    And I fill in select2 input "addplan_tags" with "test" and select "test"
    And I press "Save plan"
    When I follow "New task"
    And I fill in "Title" with "Purchase Mars"
    And I fill in "Completion date" with "-2 months" date in the format "Y/m/d"
    And I fill in "Description" with "Purchase mars description"
    And I fill in select2 input "addtasks_tags" with "Task1" and select "Task1"
    And I fill in select2 input "addtasks_tags" with "Plan task" and select "Plan task"
    Then I press "Save task"
    When I follow "New task"
    And I fill in "Title" with "Set up atmosphere"
    And I fill in "Completion date" with "-1 months" date in the format "Y/m/d"
    And I fill in "Description" with "Purchase mars description"
    And I fill in select2 input "addtasks_tags" with "Task2" and select "Task2"
    And I fill in select2 input "addtasks_tags" with "Plan task" and select "Plan task"
    Then I press "Save task"
    When  I follow "New task"
    And I fill in "Title" with "Terraform"
    And I fill in "Completion date" with "-2 weeks" date in the format "Y/m/d"
    And I fill in select2 input "addtasks_tags" with "Task3" and select "Task3"
    And I fill in select2 input "addtasks_tags" with "Plan task" and select "Plan task"
    Then I press "Save task"
    When I follow "New task"
    And I fill in "Title" with "Exploit resources"
    And I fill in "Completion date" with "+2 days" date in the format "Y/m/d"
    And I fill in select2 input "addtasks_tags" with "Task4" and select "Task4"
    And I fill in select2 input "addtasks_tags" with "Plan task" and select "Plan task"
    Then I press "Save task"
    When I follow "New task"
    And I fill in "Title" with "Bring colonists"
    And I fill in "Completion date" with "+2 weeks" date in the format "Y/m/d"
    And I fill in select2 input "addtasks_tags" with "Task5" and select "Task5"
    And I fill in select2 input "addtasks_tags" with "Plan task" and select "Plan task"
    Then I press "Save task"
    When I follow "New task"
    And I fill in "Title" with "Build metropolis"
    And I fill in "Completion date" with "+6 months" date in the format "Y/m/d"
    And I fill in select2 input "addtasks_tags" with "Task6" and select "Task6"
    And I fill in select2 input "addtasks_tags" with "Plan task" and select "Plan task"
    Then I press "Save task"
    When I follow "New task"
    And I fill in "Title" with "Start society"
    And I fill in "Completion date" with "+1 year" date in the format "Y/m/d"
    And I fill in select2 input "addtasks_tags" with "Task7" and select "Task7"
    And I fill in select2 input "addtasks_tags" with "Plan task" and select "Plan task"
    Then I press "Save task"
    When I follow "New task"
    And I fill in "Title" with "Build utopia"
    And I fill in "Completion date" with "+2 years" date in the format "Y/m/d"
    And I fill in select2 input "addtasks_tags" with "Task8" and select "Task8"
    And I fill in select2 input "addtasks_tags" with "Plan task" and select "Plan task"
    Then I press "Save task"
    When I follow "New task"
    And I fill in "Title" with "Squabble about morals"
    And I fill in "Completion date" with "+3 years" date in the format "Y/m/d"
    And I fill in select2 input "addtasks_tags" with "Task9" and select "Task9"
    And I fill in select2 input "addtasks_tags" with "Plan task" and select "Plan task"
    Then I press "Save task"
    When I follow "New task"
    And I fill in "Title" with "Quell rebels"
    And I fill in "Completion date" with "+5 years" date in the format "Y/m/d"
    And I fill in select2 input "addtasks_tags" with "Task10" and select "Task10"
    And I fill in select2 input "addtasks_tags" with "Plan task" and select "Plan task"
    Then I press "Save task"
    When I follow "New task"
    And I fill in "Title" with "Social collapse"
    And I fill in "Completion date" with "+6 years" date in the format "Y/m/d"
    And I fill in select2 input "addtasks_tags" with "Task11" and select "Task11"
    And I fill in select2 input "addtasks_tags" with "Plan task" and select "Plan task"
    Then I press "Save task"
    When I follow "New task"
    And I fill in "Title" with "Alien invasion"
    And I fill in "Completion date" with "+10 years" date in the format "Y/m/d"
    And I fill in select2 input "addtasks_tags" with "Task12" and select "Task12"
    And I fill in select2 input "addtasks_tags" with "Plan task" and select "Plan task"
    Then I press "Save task"
    When I follow "Next page"
    Then I should see "Alien invasion"
    # Add the plan to a page
    When I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page admin_01" card menu
    When I follow "Add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on "Show more"
    And I click on "Show more"
    And I click on "Plans" in the "Content types" property
    And I set the following fields to these values:
    | Plan 9 from outer space | 1 |
    | Tasks to display | 5 |
    And I press "Save"
    # verify that the Add task icon is displayed
    Then I should see "Add task"
    #click add Task - confirm user taken to tasks page -create a task and save - confirm user taken back to Page - Plan block
    When I follow "Add task"
    Then I should see "New task"
    When I fill in "Title" with "Man in the moon"
    And I fill in "Completion date" with "+10 years" date in the format "Y/m/d"
    And I fill in "Description" with "Man in the moon description text"
    And I press "Save task"
    Then I should see "Page admin_01 | Edit"
    #viewing the page in frontend
    Then I display the page
    And I should see "Plan task"
    And I should see "Task12"
    And I should not see "Task6"
    When I click on the "Show more tags" property
    Then I should see "Task6"
    And I scroll to the base of id "feedback_pagination"
    When I follow "Next page"
    Then I should see "Build utopia"
    When I follow "Next page"
    Then I should see "Social collapse"
    # Edit title of plan and then delete plan (Bug 1755680)
    When I choose "Plans" in "Create" from main menu
    And I click on "Edit \"Plan 9 from outer space\""
    And I set the field "Title" to "Life on Mars"
    And I press "Save plan"
    Then I should see "Plan saved successfully"
    And I should not see "outer space"
    And I click on "Delete \"Life on Mars\""
    And I should see "Are you sure you wish to delete this plan?"
    And I press "Delete plan"
    Then I should see "No plans yet. Add one!"
