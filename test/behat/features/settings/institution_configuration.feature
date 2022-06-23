@javascript @core @core_institution @core_administration
Feature: Changing the configuration when adding an institution.
In order to change the way an institution is configured
As an admin
So I can have different settings for each institution

Background:
  Given the following "institutions" exist:
    | name    | displayname     | registerallowed | registerconfirm | theme  |
    | instone | Institution One | ON              | OFF             | maroon |
  And the following "users" exist:
    | username | password  | email             | firstname | lastname | institution | authname | role |
    | UserA    | Kupuh1pa! | UserA@example.com | Angela    | User     | instone      | internal | member |

Scenario: Turning switches on and off on Edit Institution page (Bug 1431569)
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Settings" in "Institutions" from administration menu
 And I press "Add institution"
 And I set the following fields to these values:
 # PCNZ customisation: Display name -> Preferred name, Student ID -> Registration number
|  Institution name *      |  Team Awesome |
|  First name              |  1 |
|  Last name               |  1 |
|  Registration number     |  1 |
|  Preferred name          |  1 |
|  Introduction            |  1 |
|  Email address           |  1 |
|  Official website address|  1 |
|  Personal website address|  1 |
|  Blog address            |  1 |
|  Postal address          |  1 |
|  Town                    |  1 |
|  City/region             |  1 |
|  Country                 |  1 |
|  Home phone              |  1 |
|  Business phone          |  1 |
|  Mobile phone            |  1 |
|  Fax number              |  1 |
|  Occupation              |  1 |
|  Industry                |  1 |
|  Email disabled          |  1 |
|  Social media            |  1 |
And I press "Submit"

Scenario: Set an institution theme
 Given I log in as "UserA" with password "Kupuh1pa!"
 Then "/theme/maroon/images/site-logo" should be in the "Logo" "Header" property