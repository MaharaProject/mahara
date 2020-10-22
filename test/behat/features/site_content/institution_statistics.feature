@javascript @core @core_administration
Feature: Institution statistics are displayed correctly
In order to view information about an institution
As an admin
So I can benefit from seeing the current user detail state of an institution

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |defaultquota|
    | instone | Institution One | ON | ON | 20MB |
    | insttwo | Institution Two | ON | ON | 30MB |
    | instthree | Institution Three | ON | ON | 50MB |

    And the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | instone | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org | Bob | User | insttwo | internal | member |
    | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | instthree | internal | member |

    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Files" in "Create" from main menu
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I log out

    Given I log in as "UserB" with password "Kupuh1pa!"
    And I choose "Files" in "Create" from main menu
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I log out

    Given I log in as "UserC" with password "Kupuh1pa!"
    And I choose "Files" in "Create" from main menu
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I log out

Scenario: Viewing account details statistics
    Given I log in as "admin" with password "Kupuh1pa!"
    # Users without an institution
    When I choose "Reports" from administration menu
    And I press "Configure report"
    And I set the select2 value "All institutions" for "reportconfigform_institution"
    And I wait "1" seconds
    And I set the select2 value "Account details" for "reportconfigform_typesubtype"
    And I fill in "To:" with "tomorrow" date in the format "Y/m/d"
    And I expand the section "Columns"
    And I check "Quota used"
    And I press "Submit"
    Then I should see "Account details | All institutions"
    And I should see "42%" in the "Angela" row
    And I should see "57%" in the "Bob" row
    And I should see "68%" in the "Cecilia" row
    #sort descending by clicking the column header
    When I click on "Quota used"
    Then I should see "68%" in the "Account details row 1" "Report" property
