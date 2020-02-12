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
    And I choose "Pages and collections" in "Configure site" from administration menu
    # Check there is no access to "Share" on any of the four template cards BUG - # 1824767
    And I should not see "Manage access"
    # Check there is no access to "Share" on each of the four edit template pages BUG - # 1824767
    When I follow "Dashboard template"
    Then I should not see "Share" in the "Vertical button group" property
    When I go to "/view/accessurl.php?id=2"
    Then I should see "Access denied"
    And I should see "You do not have access to view this page."
    When I choose "Pages and collections" in "Configure site" from administration menu
    And I follow "Group homepage template"
    Then I should not see "Share" in the "Vertical button group" property
    When I go to "/view/accessurl.php?id=3"
    Then I should see "Access denied"
    And I should see "You do not have access to view this page."
    When I choose "Pages and collections" in "Configure site" from administration menu
    And I follow "Profile template"
    Then I should not see "Share" in the "Vertical button group" property
    When I go to "/view/accessurl.php?id=1"
    Then I should see "Access denied"
    And I should see "You do not have access to view this page."
    When I choose "Pages and collections" in "Configure site" from administration menu
    And I follow "Page template"
    Then I should not see "Share" in the "Vertical button group" property
    When I go to "/view/accessurl.php?id=4"
    Then I should see "Access denied"
    And I should see "You do not have access to view this page."

    # Add a text block into the site default portfolio page and create a new portfolio page (Bug 1488255)
    When I choose "Pages and collections" in "Configure site" from administration menu
    And I follow "Add"
    And I click on "Page" in the dialog
    And I set the field "Page title" to "Site page with block templates"
    And I press "Save"
    And I should see "Share"

    # Add a Profile information block
    When I follow "Drag to add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on blocktype "Profile information"
    And I set the following fields to these values:
    | First name     | 1 |
    | Last name      | 1 |
    | Student ID     | 1 |
    | Display name   | 1 |
    | Postal address | 1 |
    | Town           | 1 |
    | Country        | 1 |
    And I press "Save"
    And I wait "1" seconds
    And I should see "Postal address"
    And I follow "Share" in the "Toolbar buttons" property
    And I select "Registered people" from "General" in shared with select2 box
    And I expand "Advanced options" node
    And I enable the switch "Allow copying"
    And I press "Save"
    And I log out

    # Copy site portfolio page for UserA
    And I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I follow "Copy"
    And I press "Copy page"
    And I follow "Edit"
    And I follow "Display page"
    Then I should see "Town: Wellington"
    And I log out

    # Copy site portfolio page for UserB
    And I log in as "UserB" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I follow "Copy"
    And I press "Copy page"
    And I follow "Edit"
    And I follow "Display page"
    Then I should see "Town: Oslo"
    And I log out
