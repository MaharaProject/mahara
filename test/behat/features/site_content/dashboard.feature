@javascript @core @mainflow
Feature: Dashboard content check
    As a Mahra account holder
    I want to check that all the correct elements are on the dashboard

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

Scenario: Check default blocks are displayed
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Check the big button navigation
    And I click on "Develop your portfolio"
    Then I should see "Portfolios" in the "H1 heading" "Common" property
    And I am on homepage
    And I click on "Control your privacy"
    Then I should see "Share" in the "H1 heading" "Common" property
    And I am on homepage
    And I click on "Find people and join groups"
    Then I should see "Groups" in the "H1 heading" "Common" property
    And I choose "Preferences" in "Settings" from account menu
    And I disable the switch "Dashboard information"
    And I click on "Save"
    And I am on homepage
    And I should not see "Find people and join groups"
    And I choose "Preferences" in "Settings" from account menu
    And I enable the switch "Dashboard information"
    And I click on "Save"
    And I am on homepage
    Then I should see "Find people and join groups"

    # Check for the presence of the 5 default blocks
    # check for bug 1493199 name changed from “Latest pages” to “Portfolios shared with me”
    And I should see "Portfolios shared with me"
    And I should not see "Latest pages"
    And I should see "My portfolios"
    And I should see "Inbox"
    And I should see "Pages I am watching"

    When I click on "Edit dashboard"
    # Confirm that the blocks each contain a "Remove block" option and
    # except My portfolios each contains a "Edit block" option.
    Then "Remove block" should be in the "Portfolios shared with me" "Blocks" property
    And "Edit block" should not be in the "My portfolios" "Blocks" property
    And "Remove block" should be in the "My portfolios" "Blocks" property
    And "Edit block" should be in the "Inbox" "Blocks" property
    And "Edit block" should be in the "Pages I am watching" "Blocks" property
    And "Remove block" should be in the "Pages I am watching" "Blocks" property

    When I configure the block "Portfolios shared with me"
    And I click on "Set a block title"
    And I set the field "Block title" to "Latest change: Cats are cool!"
    And I click on "Save"
    # Check that a different block can be added
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property
    And I click on blocktype "Text"
    And I set the field "Block title" to "Favourite quote"
    And I set the field "Block content" to "A four word quote.  A.Anon"
    And I click on "Save"
    # Check that the edited changes persist
    And I choose "Dashboard" from main menu
    Then I should not see "Portfolios shared with me"
    And I should see "Latest change: Cats are cool!"
    And I should see "My portfolios"
    And I should see "Inbox"
    And I should see "Pages I am watching"
    And I should see "Favourite quote"
