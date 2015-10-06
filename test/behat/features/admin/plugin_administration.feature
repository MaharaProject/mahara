@javascript @core @core_administration
Feature: Changing the configuration on the plugin administation page
In order to change the settings on the plugin administration page page
As an admin
So I can benefit from the uses of the switches

Scenario: Turning the switches on and off on Internal Search (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 And I go to "admin/extensions/pluginconfig.php?plugintype=search&pluginname=internal"
 # Verifing in on the right page
 And I should see "Plugin administration: search: internal"
 # Checking the default
 And the following fields match these values:
 | Exact user searches | 1 |
 # Turning the switch on and off
 And I set the following fields to these values:
 | Exact user searches | 0 |
 # Turning the switch back to default setting
 And I set the following fields to these values:
 | Exact user searches | 1 |
 And I press "Save"
