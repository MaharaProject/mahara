@javascript @core @core_administration
Feature: Broken string in user accesslist report
In order to make sure user can read the strings
As an admin
I need to check they show no errors

Background:
 Given the following "institutions" exist:
     | name | displayname | registerallowed | registerconfirm |
     | instone | Institution One | ON | OFF |
 Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | instone | internal | member |

Scenario: Accessing language string (Bug 1449350)
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 And I follow "Users"
 And I set the following fields to these values:
 | selectusers_2 | 1 |
 And I press "Get reports"
 When I press "Access list"
 Then I should not see "[[loggedin/view]]"

