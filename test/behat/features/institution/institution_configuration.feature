@javascript @core_institution @core_administration
Feature: Changing the configuration when adding an institution.
In order to change the way an institution is configured
As an admin
So I can have different settings for each institution

Scenario: Turning switches on and off on Edit Institution page (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 And I go to "admin/users/institutions.php"
 And I press "Add institution"
 # Checking the default settings are correct
 And the following fields match these values:
 | Institution name * |  |
 | Institution display name * |  |
 | Registration allowed | 0 |
 | Confirm registration | 1 |
 | Drop-down navigation  | 0 |
 | Update user quotas | 0 |
 | Allow institution public pages | 1 |
 | Maximum user accounts allowed | |
 # Turning the switches on and off
 And I set the following fields to these values:
 | Institution display name * | The Avenger |
 | Institution display name * | Team Awesome |
 | Registration allowed | 1 |
 | Confirm registration | 0 |
 | Drop-down navigation  | 1 |
 | Update user quotas | 1 |
 | Allow institution public pages | 0 |
 | Maximum user accounts allowed | |
# Setting the switches back to default settings
 And I set the following fields to these values:
 | Registration allowed | 0 |
 | Confirm registration | 1 |
 | Drop-down navigation  | 0 |
 | Update user quotas | 0 |
 | Allow institution public pages | 1 |
 | Maximum user accounts allowed | |
 And I follow "Locked fields"
 # Checking the default settings are correct
 And the following fields match these values:
 | First name | 0 |
 | Last name | 0 |
 | Student ID | 0 |
 | Display name | 0 |
 | Introduction | 0 |
 | Email address | 0 |
 | Official website address | 0 |
 | Personal website address | 0 |
 | Blog address | 0 |
 | Postal address| 0 |
 | Town | 0 |
 | City/region | 0 |
 | Country | 0 |
 | Home phone | 0 |
 | Business phone | 0 |
 | Mobile phone | 0 |
 | Fax number | 0 |
 | Occupation | 0 |
 | Industry | 0 |
 | Email disabled | 0 |
 | Social media | 0 |
 # Turning the switches on and off
 And I set the following fields to these values:
 | First name | 1 |
 | Last name | 1 |
 | Student ID | 1 |
 | Display name | 1 |
 | Introduction | 1 |
 | Email address | 1 |
 | Official website address | 1 |
 | Personal website address | 1 |
 | Blog address | 1 |
 | Postal address| 1 |
 | Town | 1 |
 | City/region | 1 |
 | Country | 1 |
 | Home phone | 1 |
 | Business phone | 1 |
 | Mobile phone | 1 |
 | Fax number | 1 |
 | Occupation | 1 |
 | Industry | 1 |
 | Email disabled | 1 |
 | Social media | 1 |
And I press "Submit"

