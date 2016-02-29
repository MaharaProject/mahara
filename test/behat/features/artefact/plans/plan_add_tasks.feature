@javascript @core_content @failed
Feature: Creating a plan and adding a number of tasks to the plan
As a user
In order to test the pagination of the plan

Scenario: Creating a plan with 11 tasks
 Given I log in as "admin" with password "Kupuhipa1"
 And I choose "Plans" in "Content"
 And I follow "New plan"
 And I fill in the following:
 | Title *  | Plan 9 from outer space  |
 | Description  | Woooo |
 | Tags   | plan, test   |
 And I press "Save plan"

 And I follow "New task"
 And I fill in the following:
 | Title *  | Purchase Mars  |
 | Completion date  | 2015/10/30 |
 And I press "Save task"

 And I follow "New task"
 And I fill in the following:
 | Title *  | Set up atmosphere  |
 | Completion date  | 2016/10/30 |
 And I press "Save task"

 And I follow "New task"
 And I fill in the following:
 | Title *  | Terraform  |
 | Completion date  | 2017/10/30 |
 And I press "Save task"

 And I follow "New task"
 And I fill in the following:
 | Title *  | Exploit resources  |
 | Completion date  | 2017/11/30 |
 And I press "Save task"

 And I follow "New task"
 And I fill in the following:
 | Title *  | Bring colonists  |
 | Completion date  | 2018/10/30 |
 And I press "Save task"

 And I follow "New task"
 And I fill in the following:
 | Title *  | Build metropolis  |
 | Completion date  | 2019/10/30 |
 And I press "Save task"

 And I follow "New task"
 And I fill in the following:
 | Title *  | Start society  |
 | Completion date  | 2020/10/30 |
 And I press "Save task"

 And I follow "New task"
 And I fill in the following:
 | Title *  | Build utopia  |
 | Completion date  | 2021/10/30 |
 And I press "Save task"

 And I follow "New task"
 And I fill in the following:
 | Title *  | Squabble about morals  |
 | Completion date  | 2022/10/30 |
 And I press "Save task"

 And I follow "New task"
 And I fill in the following:
 | Title *  | Quell rebels  |
 | Completion date  | 2023/10/30 |
 And I press "Save task"

 And I follow "New task"
 And I fill in the following:
 | Title *  | Social collapse  |
 | Completion date  | 2024/10/30 |
 And I press "Save task"

 And I follow "New task"
 And I fill in the following:
 | Title *  | Alien invasion  |
 | Completion date  | 2025/10/30 |
 And I press "Save task"
 And I follow "Next page"
 Then I should see "Alien invasion"
 # Add the plan to a page
 And I choose "Pages" in "Portfolio"
 And I press "Create page"
 And I press "Save"
 And I expand "General" node
 And I follow "Plans" in the "div#general" "css_element"
 And I press "Add"
 And I set the following fields to these values:
 | Plan 9 from outer space | 1 |
 | Tasks to display | 5 |
 And I press "Save"
 And I display the page
 And I follow "Next page"
 Then I should see "Build utopia"
 And I follow "Next page"
 Then I should see "Social collapse"