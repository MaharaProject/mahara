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
 | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
 | userB | Password1 | test02@example.com | Miles | Morales | instone | internal | member |
 | userC | Password1 | test03@example.com | Jessica | Jones | mahara | internal | member |

Scenario: Viewing user statistics
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 # Users without an institution
 When I choose "Statistics" in "Institutions"
 Then I should see "Institution statistics for 'Institution One'"
 And I should see "Users: 1"
 # Users with an institution
 When I select "No Institution" from "usertypeselect_institution"
 Then I should see "Institution statistics for 'No Institution'"
 And I should see "Users: 3"
