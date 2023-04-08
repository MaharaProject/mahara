@javascript @core @core_administration
Feature: Create a site portfolio page with template blocks
In order to create a site portfolio page
As an admin
I check the templates cannot be shared then create a site page from the Page template with an added profile block
As a user
I can copy the site page and have the block pre-populated with information.

Background:
    Given the following "users" exist:
    | username | password  | email             | firstname | lastname | institution | authname | role   | studentid | town       | country | occupation |
    | UserA    | Kupuh1pa! | UserA@example.org | Angela    | User     | mahara      | internal | member | superA1   | Wellington | nz      | Plumber    |
    | UserB    | Kupuh1pa! | UserB@example.org | Bob       | User     | mahara      | internal | member | normalB2  | Oslo       | Norway  | Welder     |

Scenario: Check templates not editable then create and use the Page template with a profile block.
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Configure site" from administration menu
    # Check there is no access to "Share" on any of the four template cards BUG - # 1824767
    And I should not see "Manage sharing"
    # Check there is no access to "Share" on each of the four edit template pages BUG - # 1824767
    When I click on "Dashboard template"
    Then I should not see "Share" in the "Vertical button group" "Views" property
    When I go to "/view/accessurl.php?id=2"
    Then I should see "Access denied"
    And I should see "You do not have access to view this page."
    When I choose "Portfolios" in "Configure site" from administration menu
    And I click on "Group homepage template"
    Then I should not see "Share" in the "Vertical button group" "Views" property
    When I go to "/view/accessurl.php?id=3"
    Then I should see "Access denied"
    And I should see "You do not have access to view this page."
    When I choose "Portfolios" in "Configure site" from administration menu
    And I click on "Profile template"
    Then I should not see "Share" in the "Vertical button group" "Views" property
    When I go to "/view/accessurl.php?id=1"
    Then I should see "Access denied"
    And I should see "You do not have access to view this page."
    When I choose "Portfolios" in "Configure site" from administration menu
    And I click on "Page template"
    Then I should not see "Share" in the "Vertical button group" "Views" property
    When I go to "/view/accessurl.php?id=4"
    Then I should see "Access denied"
    And I should see "You do not have access to view this page."

    # Add a text block into the site default portfolio page and create a new portfolio page (Bug 1488255)
    When I choose "Portfolios" in "Configure site" from administration menu
    And I click on "Create" in the "Create" "Views" property
    And I click on "Page" in the dialog
    And I set the field "Page title" to "Site page with block templates"
    And I click on "Save"
    And I should see "Share"

    # Add a Profile information block
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property
    And I click on blocktype "Profile information"
    And I set the following fields to these values:
    | First name     | 1 |
    | Last name      | 1 |
    | Student ID     | 1 |
    | Display name   | 1 |
    | Postal address | 1 |
    | Town           | 1 |
    | Country        | 1 |
    And I click on "Save"
    And I wait "1" seconds
    And I should see "Postal address"
    And I click on "Share" in the "Toolbar buttons" "Nav" property
    And I select "Registered people" from "General" in shared with select2 box
    And I expand "Advanced options" node
    And I enable the switch "Allow copying"
    And I click on "Save"
    And I log out

    # Copy site portfolio page for UserA
    And I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Copy"
    And I click on "Copy page"
    And I click on "Edit"
    And I click on "Display page"
    Then I should see "Town: Wellington"
    And I log out

    # Copy site portfolio page for UserB
    And I log in as "UserB" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Copy"
    And I click on "Copy page"
    And I click on "Edit"
    And I click on "Display page"
    Then I should see "Town: Oslo"
    And I log out
