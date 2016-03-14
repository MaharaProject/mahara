@javascript @core @core_administration
Feature: Required plugins
In order to utilize default Mahara certain required plugins need to be active for the site
As an admin
I check that the plugins are active

Scenario: Checking that the Multirecipient notifications plugin is active (Bug 1497065)
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 And I choose "Plugin administration" in "Extensions"
 # Currently only tests for multirecipientnotifications plugin which
 # should be active and not be able to be hidden
 And I scroll to the base of id "module.multirecipientnotification"
 Then I should not see "Hide" in the "//li[@id='module.multirecipientnotification']" "xpath_element"
 And I should not see "Show" in the "//li[@id='module.multirecipientnotification']" "xpath_element"
