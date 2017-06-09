@javascript @core @core_administration
Feature: Institution statistics are displayed correctly
In order to view information about an institution
As an admin
So I can benefit from seeing the current state and history of an institution

Background:
 Given the following "institutions" exist:
 | name | displayname | registerallowed | registerconfirm |
 | instone | Institution One | ON | ON |
 And the following "users" exist:
 | username | password | email | firstname | lastname | institution | authname | role |
 | userA | Kupuhipa1 | test01@example.org | Pete | Mc | mahara | internal | member |
 | userB | Kupuhipa1 | test02@example.org | Miles | Morales | instone | internal | member |
 | userC | Kupuhipa1 | test03@example.org | Jessica | Jones | mahara | internal | member |


Scenario: Viewing user statistics
 Given I log in as "admin" with password "Kupuhipa1"
 # Users without an institution
 When I choose "Reports" in "Institutions" from administration menu
 Then I should see "People overview"
