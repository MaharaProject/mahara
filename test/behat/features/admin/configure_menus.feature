@javascript @core @core_administration
Feature: Configuration of menus page in site configuration
In order to be able to change the menu configuration
As an admin
So I can benefit from the use of the configuration changes

 Scenario: Turning switches on and off on Menus page in Configure site (Bug 1431569)
 Given I log in as "admin" with password "Password1"
 And I follow "Administration"
 And I choose "Menus" in "Configure site"
 # Checking the default settings are correct
 And the following fields match these values:
 | Terms and conditions | 0 |
 | Privacy statement | 1 |
 | About | 1 |
 | Contact us | 1 |
 # Flicking the switches to the opposite
 And I set the following fields to these values:
 | Terms and conditions | 1 |
 | Privacy statement | 0 |
 | About | 0 |
 | Contact us | 0 |
# Checking the switches held the setting
  And the following fields match these values:
 | Terms and conditions | 1 |
 | Privacy statement | 0 |
 | About | 0 |
 | Contact us | 0 |
 And I press "Save changes"
