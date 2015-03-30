@javascript @core @core_administration
Feature: Switches on Uploads Groups via CSV Page
In order to turn switches on and off
As an admin
So I can change the settings on a page

Scenario: Turning the swtich on and off on the Upload Groups Via CSV page (Bug 1431569)
Given I log in as "admin" with password "Password1"
And I follow "Administration"
And I choose "Add groups by CSV" in "Groups (Administer groups)"
# Checking the default value is correct
And the following fields match these values:
 | Update groups | 0 |
# Flicking the switch to the opposite of the default
And I set the following fields to these values:
 | Update groups | 1 |
# Setting the switch back to the default setting
And I set the following fields to these values:
 | Update groups | 0 |
And I press "Add groups by CSV"
