@javascript @core @core_administration
Feature: User options lang string and switch
In order to use the switch
As as admin
So change the settings

Scenario: Checking lang string on switch on site options user settings page (Bug 1432523 & 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 And I go to "admin/site/options.php"
 And I follow "User settings"
 And the "Profile access for all registered users" checkbox should be checked
 And I uncheck "Profile access for all registered users"
 And I check "Profile access for all registered users"
 And I should see "Profile access for all registered users"
 And I press "Update site options"
