@javascript @core @blocktype @blocktype_newtextblock
Feature: Creating/deleting a text block
    As a user
    I want to add and remove text blocks from my page
    So I can control the content

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

     And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01| user | UserA |

Scenario: Create and delete text block
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    When I follow "Drag to add a new block" in the "blocktype sidebar" "Views" property
    When I press "Add"
    And I click on blocktype "Text"
    And I set the field "Block title" to "Text block 1"
    And I set the field "Block content" to "Here is a new block."
    And I press "Save"
    And I should see "Text block 1" in the "Main content" "Views" property
    And I display the page
    Then I should see "Here is a new block"
    # delete block
    And I press "Edit"
    And I delete the block "Text block 1"
    And I display the page
    Then I should not see "Text block 1"
