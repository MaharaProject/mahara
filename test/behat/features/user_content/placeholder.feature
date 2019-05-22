@javascript @core @blocktype @blocktype_placeholder
Feature: Adding a placeholder block to a page
    As a student
    I need to be able to add a placeholder block to my portfolio
    and then change it to be a block of my choosing

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
    Then I should see "Please configure the block to choose what type of block this should be"

    # Edit placeholder block and check we can see more options
    And I configure the block "Mahara placeholder block"
    And I click on "Show more"
    Then I should see "Image gallery"
    # Change placeholder block to a text block
    And I fill in the following:
    | Block title | Mahara text block title |
    And I click on "Text" in the "Content types" property
    And I set the field "Block content" to "Mahara text block content"
    And I press "Save"
    Then I should see "Mahara text block title"
    Then I should see "Mahara text block content"
