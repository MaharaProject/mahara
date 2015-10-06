@javascript @core @core_administration
Feature: Configuration on user search page
In order to change the configuration of the user search page
As an admin
So I can benefit from the use of different configurations

Scenario: Turning the switches on and off on user search page (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | admin |
 And I follow "Administration"
 And I choose "User search" in "Users"
 And I follow "userA"
 And I should see "Account settings: Pete Mc (userA)"
 # Checking the default settings are right
 And the following fields match these values:
 | Force password change on next login  | 0 |
 | Site staff | 0 |
 | Site administrator | 1 |
 | Disable email | 0 |
# Flicking the switch to the opposite of the default
 And I set the following fields to these values:
 | Force password change on next login  | 1 |
 | Site staff | 1 |
 | Site administrator | 0 |
 | Disable email | 1 |
# Flicking the switch back to the default setting
 And I set the following fields to these values:
 | Force password change on next login  | 0 |
 | Site staff | 0 |
 | Site administrator | 1 |
 | Disable email | 0 |
And I press "Save changes"

