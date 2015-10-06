@javascript @core @core_administration
Feature: Configuration settings on networking page
In order to configure settings
As an admin
I need to be able to turn switches on and off

Scenario: Turning swtitches on and off on Networking page (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 And I choose "Networking" in "Configure site"
 # Checking the default settings are set
 And the following fields match these values:
 | Enable networking | 0 |
 | Auto-register all hosts | 0 |
 And I set the following fields to these values:
 | Enable networking | 1 |
 | Auto-register all hosts | 1 |
 # Checks that the swtiches can change back
 And I set the following fields to these values:
 | Enable networking | 0 |
 | Auto-register all hosts | 0 |
 And I press "Save changes"
