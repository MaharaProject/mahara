@javascript @core @blocktype @blocktype_retractable
Feature: Blocks are Retractable
    In order to control page layout
    Users should be able to
    make blocks retractable if they choose to

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |

Scenario: Make Text Block Retractable
    Given I log in as "UserA" with password "Kupuhipa1"
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I click on "Edit" in "Page UserA_01" panel menu
    And I follow "Text"
    And I press "Add"
    And I set the field "Block title" to "Text Block 1"
    And I set the field "Block content" to "Here is a new block."
    Given I select "Yes" from "Retractable"
    And I press "Save"
    And I scroll to the id "main-nav"
    Then I click on "Display page"
    Then I should see "Here is a new block"
    Then I should see "Text Block 1"
    And I collapse "Text Block 1" node
    And I should not see "Here is a new block"
