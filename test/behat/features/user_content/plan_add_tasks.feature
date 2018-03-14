@javascript @core @core_content
Feature: Creating a plan and adding a number of tasks to the plan
As a user
In order to test the pagination of the plan

Background:
     Given the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page admin_01 | Page 01 | user | admin |

Scenario: Creating a plan with 11 tasks (Bug #1503036)
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Plans" in "Content" from main menu
 And I follow "New plan"
 And I fill in the following:
 | Title *  | Plan 9 from outer space  |
 | Description  | Woooo |
 And I fill in select2 input "addplan_tags" with "plan" and select "plan"
 And I fill in select2 input "addplan_tags" with "test" and select "test"
 And I press "Save plan"

 And I follow "New task"
 And I fill in "Title" with "Purchase Mars"
 And I fill in "Completion date" with "-2 months" date in the format "Y/m/d"
 And I press "Save task"

 And I follow "New task"
 And I fill in "Title" with "Set up atmosphere"
 And I fill in "Completion date" with "-1 months" date in the format "Y/m/d"
 And I press "Save task"

 And I follow "New task"
 And I fill in "Title" with "Terraform"
 And I fill in "Completion date" with "-2 weeks" date in the format "Y/m/d"
 And I press "Save task"

 And I follow "New task"
 And I fill in "Title" with "Exploit resources"
 And I fill in "Completion date" with "+2 days" date in the format "Y/m/d"
 And I press "Save task"

 And I follow "New task"
 And I fill in "Title" with "Bring colonists"
 And I fill in "Completion date" with "+2 weeks" date in the format "Y/m/d"
 And I press "Save task"

 And I follow "New task"
 And I fill in "Title" with "Build metropolis"
 And I fill in "Completion date" with "+6 months" date in the format "Y/m/d"
 And I press "Save task"

 And I follow "New task"
 And I fill in "Title" with "Start society"
 And I fill in "Completion date" with "+1 year" date in the format "Y/m/d"
 And I press "Save task"

 And I follow "New task"
 And I fill in "Title" with "Build utopia"
 And I fill in "Completion date" with "+2 years" date in the format "Y/m/d"
 And I press "Save task"

 And I follow "New task"
 And I fill in "Title" with "Squabble about morals"
 And I fill in "Completion date" with "+3 years" date in the format "Y/m/d"
 And I press "Save task"

 And I follow "New task"
 And I fill in "Title" with "Quell rebels"
 And I fill in "Completion date" with "+5 years" date in the format "Y/m/d"
 And I press "Save task"

 And I follow "New task"
 And I fill in "Title" with "Social collapse"
 And I fill in "Completion date" with "+6 years" date in the format "Y/m/d"
 And I press "Save task"

 And I follow "New task"
 And I fill in "Title" with "Alien invasion"
 And I fill in "Completion date" with "+10 years" date in the format "Y/m/d"
 And I press "Save task"

 And I follow "Next page"
 Then I should see "Alien invasion"
 # Add the plan to a page
 And I choose "Pages and collections" in "Portfolio" from main menu
 And I click on "Edit" in "Page admin_01" panel menu
 And I expand "General" node
 And I follow "Plans" in the "blocktype sidebar" property
 And I press "Add"
 And I set the following fields to these values:
 | Plan 9 from outer space | 1 |
 | Tasks to display | 5 |
 And I press "Save"
 And I display the page
 And I scroll to the base of id "feedback_pagination"
 And I follow "Next page"
 Then I should see "Build utopia"
 And I follow "Next page"
 Then I should see "Social collapse"

 # Edit title of plan and then delete plan (Bug 1755680)
 And I choose "Plans" in "Content" from main menu
 And I click on "Edit \"Plan 9 from outer space\""
 And I set the field "Title" to "Life on Mars"
 And I press "Save plan"
 Then I should see "Plan saved successfully"
 And I should not see "outer space"
 And I click on "Delete \"Life on Mars\""
 And I should see "Are you sure you wish to delete this plan?"
 And I press "Delete plan"
 Then I should see "No plans yet. Add one!"
