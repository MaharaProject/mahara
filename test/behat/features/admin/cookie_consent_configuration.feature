@javascript @core @core_administration
Feature: Enabling cookie consent
In order to enable cookie consent
As an admin
I need to be able to use the switches and change the configuration

Scenario: Turning the switches on and off on Cookie consent page (Bug 1431569)
 Given I log in as "admin" with password "Password1"
 And I go to "admin/site/cookieconsent.php"
 And I follow "Styling options"
 And I follow "Feature options"
 And the following fields match these values:
 | Enable Cookie Consent | 0 |
 | Hide privacy settings tab | 0 |
 | Page refresh | 0 |
 | Ignore "do not track" | 0 |
 | Use SSL | 0 |
 # Turning the switches on and off
 And I set the following fields to these values:
 | Enable Cookie Consent | 1 |
 | Hide privacy settings tab | 1 |
 | Page refresh | 1 |
 | Ignore "do not track" | 1 |
 | Use SSL | 1 |
 And I press "Save changes"
 And I should see "Cookie Consent enabled and settings saved"



