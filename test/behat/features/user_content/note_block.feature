@javascript @core @blocktype @blocktype_notes
Feature: Adding a Note to a page
    In order to be able to write notes on my portfolio
    As a student
    I need to be able to add a Note block to my portfolio

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |

Scenario: Adding and deleting a Note block (Bug 1424512)
    # Logging in as a user
    Given I log in as "UserA" with password "Kupuhipa1"
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I click on "Page UserA_01" panel menu
    And I click on "Edit" in "Page UserA_01" panel menu
    # Configuring the block
    And I expand "General" node
    And I follow "Note" in the "div#general" "css_element"
    And I press "Add"
    And I set the following fields to these values:
    | Block title | Note block 1 |
    | Block content | This is a test |
    #Adding an attachment to a note and attaching a file to it.
    And I follow "Attachments" in the "div#instconf_artefactfieldset_container" "css_element"
    And I attach the file "Image2.png" to "userfile[]"
    And I press "Save"
    #Add a second note to the page
    And I follow "Note" in the "div#general" "css_element"
    And I press "Add"
    And I follow "Use content from another note"
    And I select the radio "Note block 1"
    # Set title after selection as selection updates the title with original one
    And I set the following fields to these values:
    | Block title | Note block 2 |
    And I press "Save"
    And I should see "This is a test" in the block "Note block 2"
    # Verifying the attachment saved
    And I choose "Notes" in "Content" from main menu
    And I follow "Note block 1"
    And I should see "Image2.png"
    # Verifying the Note block saved
    And I follow "Page UserA_01"
    And I choose "Notes" in "Content" from main menu
    And I should see "Note block 1"
    # Verifying the Note block can be deleted
    And I delete the "Note block 1" row
    Then I should see "Note deleted"
