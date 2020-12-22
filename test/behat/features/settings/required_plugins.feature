@javascript @core @core_administration
Feature: Required plugins
In order to utilize default Mahara certain required plugins need to be active for the site
As an admin
I check that the plugins are active

Scenario: Checking that the Multirecipient notifications plugin is active (Bug 1497065)
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Plugin administration" in "Extensions" from administration menu
 # Currently only tests for multirecipientnotifications plugin which
 # should be active and not be able to be hidden
 And I scroll to the base of id "module.multirecipientnotification"
 Then I should not see "Hide" in the "multirecipientnotification" "Misc" property
 And I should not see "Show" in the "multirecipientnotification" "Misc" property
