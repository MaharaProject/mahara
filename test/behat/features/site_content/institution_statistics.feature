@javascript @core @core_administration
Feature: Institution statistics are displayed correctly
In order to view information about an institution
As an admin
So I can benefit from seeing the current user detail state of an institution

Background:
 Given the following "institutions" exist:
 | name | displayname | registerallowed | registerconfirm |
 | instone | Institution One | ON | ON |

 And the following "users" exist:
 | username | password | email | firstname | lastname | institution | authname | role |
 | UserA | Kupuh1pa! | UserA@example.org | Angela | User | instone | internal | member |
 | UserB | Kupuh1pa! | UserB@example.org | Bob | User | instone | internal | member |
 | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | instone | internal | member |

Scenario: Viewing user details statistics
 Given I log in as "UserA" with password "Kupuh1pa!"
 And I choose "Files" in "Create" from main menu
 And I attach the file "Image1.jpg" to "File"
 And I attach the file "Image1.jpg" to "File"
 And I attach the file "Image1.jpg" to "File"
 And I log out

 Given I log in as "admin" with password "Kupuh1pa!"
 # Users without an institution
 When I choose "Reports" from administration menu
 And I press "Configure report"
 And I set the select2 value "Institution One" for "reportconfigform_institution"
 And I set the select2 value "User details" for "reportconfigform_typesubtype"
 And I fill in "To:" with "+1 day" date in the format "Y/m/d"
 And I expand the section "Columns"
 And I check "Quota used"
 And I press "Submit"
 Then I should see "User details | Institution One"
 And I should see "8%" in the "Angela" row
