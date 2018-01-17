@javascript @core @core_administration
Feature: Checking specific registered data is being sent
In order to check specific registered data is being sent
As an admin
So I can send this data out

Scenario: Registered data that is sent (Bug 1447865)
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Register" in "Admin home" from administration menu
 When I expand all fieldsets
 Then I should see "phpversion"
 And I should see "dbversion"
 And I should see "osversion"
 And I should see "phpsapi"
 And I should see "webserver"
 And I should see "phpmodules"
