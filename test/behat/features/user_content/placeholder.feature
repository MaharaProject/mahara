@javascript @core @blocktype @blocktype_placeholder
Feature: Adding a placeholder block to a page
    As a student
    I need to be able to add a placeholder block to my portfolio
    and then change it to be a block of my choosing
    As an admin I need to be able to alter the order of blocks
    to make more popular ones list first

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |

Scenario: Adding a placeholder block to the page
    # Logging in as a user
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    # Add a placeholder block
    And I follow "Drag to add a new block" in the "blocktype sidebar" "Views" property
    And I press "Add"
    And I fill in the following:
    | Block title | Mahara placeholder block |
    And I press "Save"
    Then I should see "Please configure this block to choose its type."

    # Edit placeholder block and check we can see more options
    And I scroll to the top
    And I configure the block "Mahara placeholder block"
    And I click on "Show more"
    Then I should see "Image gallery"
    # Change placeholder block to a text block
    And I fill in the following:
    | Block title | Mahara text block title |
    And I click on blocktype "Text"
    And I set the field "Block content" to "Mahara text block content"
    And I press "Save"
    Then I should see "Mahara text block title"
    Then I should see "Mahara text block content"

Scenario: Adjusting the order of the placeholder blocks
    Given I log in as "Admin" with password "Kupuh1pa!"
    And I choose "Plugin administration" in "Extensions" from administration menu
    And I click on "Configuration for blocktype"
    Then I should see "Content types"
    And I move blocktype "Some HTML" to before "Text"
    And I move blocktype "Comments" to before "Text"
    And I move blocktype "External media" to before "Text"
    And I move blocktype "PDF" to before "Text"
    And I reload the page
# TODO - then go to add a placeholder block and check the order
