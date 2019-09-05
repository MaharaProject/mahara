@javascript @core @blocktype @blocktype_notes
Feature: Adding a Note to a page
    In order to be able to write notes on my portfolio
    As a student
    I need to be able to add a Note block to my portfolio

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |

Scenario: Adding and deleting a Note block (Bug 1424512)
    # Logging in as a user
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    # Configuring the block
    When I follow "Drag to add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on "Show more"
    And I click on "Show more"
    And I click on "Show more"
    And I click on "Note" in the "Content types" property
    And I set the following fields to these values:
    | Block title | Note block 1 |
    | Block content | This is a test |
    # Adding an attachment to a note and attaching a file to it.
    And I attach the file "Image2.png" to "userfile[]"
    And I press "Save"
    # Add a second note to the page
    When I follow "Drag to add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on "Show more"
    And I click on "Show more"
    And I click on "Show more"
    And I click on "Note" in the "Content types" property
    And I follow "Use content from another note"
    And I select the radio "Note block 1"
    # Set title after selection as selection updates the title with original one
    And I set the following fields to these values:
    | Block title | Note block 2 |
    And I press "Save"
    And I should see "This is a test" in the block "Note block 2"
    # Verifying the attachment saved
    And I choose "Notes" in "Create" from main menu
    And I follow "Note block 1"
    And I should see "Image2.png"
    # Verifying the Note block saved
    And I follow "Page UserA_01"
    And I choose "Notes" in "Create" from main menu
    And I should see "Note block 1"
    # Verifying the Note block can be deleted
    And I delete the "Note block 1" row
    Then I should see "Note deleted"
