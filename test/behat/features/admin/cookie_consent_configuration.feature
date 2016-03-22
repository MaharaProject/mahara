@javascript @core @core_administration
Feature: Enabling cookie consent
In order to enable cookie consent
As an admin
I need to be able to enable it via a switch

Scenario: Turning the switches on and off on Cookie consent page (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 When I follow "Administration"
 And I choose "Cookie Consent" in "Configure site"
 And I set the following fields to these values:
 | Enable Cookie Consent | 1 |
 And I press "Save changes"
 And I should see "Cookie Consent enabled"
 And I should see "Got it!"
