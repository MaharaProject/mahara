@javascript @core @core_institution @core_administration
Feature: Changing the configuration when adding an institution.
In order to change the way an institution is configured
As an admin
So I can have different settings for each institution

Scenario: Turning switches on and off on Edit Institution page (Bug 1431569)
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Settings" in "Institutions" from administration menu
 And I press "Add institution"
 And I set the following fields to these values:
 | Institution name * | Team Awesome |
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
