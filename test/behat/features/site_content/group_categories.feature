@javascript @core @core_administration @core_group
Feature: Group categories settings
In order to enable group categories
As an admin
So I can benefit from the use of group categories

 Scenario: Turning switches on and off on group categories page (Bug 1431569)
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Group categories" in "Groups" from administration menu
 # Flicking the switch to the opposite setting
 And I set the following fields to these values:
 | Enable group categories | 1 |
 And I click on "Submit"
 # Changing it back to the original setting
 And I set the following fields to these values:
 | Enable group categories | 0 |
 And I click on "Submit"
