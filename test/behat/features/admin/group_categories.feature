@javascript @core @core_administration
Feature: Group categories settings
In order to enable group categories
As an admin
So I can benefit from the use of group categories

 Scenario: Turning switches on and off on group categories page (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 And I choose "Group categories" in "Groups (Administer groups)"
 # Checking the default is correct
 And the following fields match these values:
 | Enable group categories | 0 |
 # Flicking the switch to the opposite setting
 And I set the following fields to these values:
 | Enable group categories | 1 |
 And I press "Submit"
 # Changing it back to the original setting
 And I set the following fields to these values:
 | Enable group categories | 0 |
 And I press "Submit"
