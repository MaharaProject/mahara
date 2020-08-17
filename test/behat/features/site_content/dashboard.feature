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
    Then I should see "Pages and collections" in the "h1 heading" property
    And I am on homepage
    And I click on "Control your privacy"
    Then I should see "Share" in the "h1 heading" property
    And I am on homepage
    And I click on "Find people and join groups"
    Then I should see "Groups" in the "h1 heading" property
    And I choose "Preferences" in "Settings" from account menu
    And I disable the switch "Dashboard information"
    And I press "Save"
    And I am on homepage
    And I should not see "Find people and join groups"
    And I choose "Preferences" in "Settings" from account menu
    And I enable the switch "Dashboard information"
    And I press "Save"
    And I am on homepage
    Then I should see "Find people and join groups"

    # Check for the presence of the 5 default blocks
    # check for bug 1493199 name changed from “Latest pages” to “Latest changes I can view”
    And I should see "Latest changes I can view"
    And I should not see "Latest pages"
    And I should see "My portfolios"
    And I should see "Inbox"
    And I should see "Topics I am following"
    And I should see "Watched pages"

    When I click on "Edit dashboard"
    # Confirm that the blocks each contain a "Remove block" option and
    # except My portfolios each contains a "Configure block" option.
    #Latest changes I can view
    #We check configure below
    Then the ".bt-newviews-editor" element should contain "Remove block"
    #My portfolios
    And the ".bt-myviews-editor" element should not contain "Configure block"
    And the ".bt-myviews-editor" element should contain "Remove block"
    #Inbox
    And the ".bt-inbox-editor" element should contain "Configure block"
    # We test remove for "Topics I am following", which is a second instance of "Inbox", so we can ignore the rest.
    #Watched pages
    And the ".bt-watchlist-editor" element should contain "Configure block"
    And the ".bt-watchlist-editor" element should contain "Remove block"

    When I configure the block "Latest changes I can view"
    And I click on "Set a block title"
    And I set the field "Block title" to "Latest change: Cats are cool!"
    And I press "Save"
    And I delete the block "Topics I am following"
    # Check that a different block can be added
    When I follow "Drag to add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on blocktype "Text"
    And I set the field "Block title" to "Favourite quote"
    And I set the field "Block content" to "A four word quote.  A.Anon"
    And I press "Save"
    # Check that the edited changes persist
    And I choose "Dashboard" from main menu
    Then I should not see "Latest changes I can view"
    And I should see "Latest change: Cats are cool!"
    And I should see "My portfolios"
    And I should see "Inbox"
    And I should not see "Topics I am following"
    And I should see "Watched pages"
    And I should see "Favourite quote"