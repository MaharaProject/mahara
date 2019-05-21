@javascript @core @blocktype @blocktype_placeholder
Feature: Adding a placeholder block to a page
    As a student
    I need to be able to add a placeholder block to my portfolio

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |

Scenario:
    # Logging in as a user
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    # Add a placeholder block
    And I follow "Placeholder" in the "blocktype sidebar" property
    And I press "Add"
    And I fill in the following:
    | Block title | Mahara placeholder block |
    And I press "Save"
    And I display the page
    Then I should see "Please configure the block to choose what type of block this should be"
