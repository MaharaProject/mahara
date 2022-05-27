@javascript @core @core_administration
Feature: Configuration on people search page
In order to change the configuration of the people search page
As an admin
So I can benefit from the use of different configurations

Background:
 Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | admin |

Scenario: Testing functions for people search page (Bug 1431569)
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "People search" in "People" from administration menu
 And I click on "Advanced options"
 And I click on "A" in the "Filter by first name" "People" property
 And I click on "UserA"
 And I should see "Account settings | Angela User (UserA)"
 # Flicking the switches to new settings
 And I set the following fields to these values:
 | Force password change on next login  | 1 |
 | Disable email | 1 |
 And I click on "Save changes"

 # Check that I can do a people search when 'Email address' option is on
 Given the following plugin settings are set:
 | plugintype | plugin  | field | value |
 | artefact | internal | profilepublic | email |
 And I set the following fields to these values:
 | Search for people | UserA@example.org |
 And I click on the key "Enter" in the "Search for people" field
 Then I should see "UserA"
