@javascript @core @core_administration
Feature: Enabling cookie consent
In order to enable cookie consent
As an admin
I need to be able to use the switches and change the configuration

Scenario: Turning the switches on and off on Cookie consent page (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 And I go to "admin/site/cookieconsent.php"
 Then the following fields match these values:
 | Enable Cookie Consent | 0 |
 | Hide privacy settings tab | 0 |
 | Page refresh | 0 |
 | Ignore "do not track" | 0 |
 | Use SSL | 0 |
 When I go to "admin/site/cookieconsent.php"
 When I set the field "Enable Cookie Consent" to "1"
 And I expand the section "Feature options"
 And I set the field "Page refresh" to "1"
 And I set the field "Ignore \"do not track\"" to "1"
 And I press "Save changes"
 Then I should see "Cookie Consent enabled and settings saved"
 And I should see "If you want Cookie Control to take full effect, you will have to modify or update your theme header files or the $cfg->additionalhtmlhead config setting."
