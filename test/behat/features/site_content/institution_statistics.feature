@javascript @core @core_administration
Feature: Institution statistics are displayed correctly
In order to view information about an institution
As an admin
So I can benefit from seeing the current state and history of an institution

Background:
 Given the following "institutions" exist:
 | name | displayname | registerallowed | registerconfirm |
 | instone | Institution One | ON | ON |
 | insttwo | Institution Two | ON | ON |
 And the following "users" exist:
 | username | password | email | firstname | lastname | institution | authname | role |
 | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
 | UserB | Kupuhipa1 | UserB@example.org | Bob | User | instone | internal | member |
 | UserC | Kupuhipa1 | UserC@example.org | Cecilia | User | mahara | internal | member |

Scenario: Viewing user statistics
 Given I log in as "admin" with password "Kupuhipa1"
 # Users without an institution
 When I choose "Reports" from administration menu
 And I press "Configure report"
 And I set the select2 value "Institution One" for "reportconfigform_institution"
 And I press "Submit"
 Then I should see "People overview | Institution One"
