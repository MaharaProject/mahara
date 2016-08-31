@javascript @core @blocktype @blocktype_notes
Feature: Adding a Note to a page
    In order to be able to write notes on my portfolio
    As a student
    I need to be able to add a Note block to my portfolio

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

    And the following "pages" exist:
    | title | description| ownertype | ownername |
    | Page 1 | page P1 | user | userA |

    # Logging in as a user
    Given I log in as "userA" with password "Kupuhipa1"
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I click on "Page 1" panel menu
    And I click on "Edit" in "Page 1" panel menu
    # Configuring the block
    And I expand "General" node
    And I follow "Note" in the "div#general" "css_element"
    And I press "Add"
    And I fill in the following:
    | Block title | Note block 1 |

Scenario: Adding and deleting a Note block (Bug 1424512)
    And I press "Save"
    And I should see "Note block 1"
    # Verifying the Note block saved
    And I display the page
    And I choose "Notes" in "Content" from main menu
    And I should see "Note block 1"
    # Verifying the Note block can be deleted
    And I delete the "Note block 1" row
    Then I should see "Note deleted"

Scenario: Adding an attachment to a note
    # Attaching a file to the note
    And I follow "Attachments" in the "div#instconf_artefactfieldset_container" "css_element"
    And I attach the file "Image2.png" to "userfile[]"
    And I should see "Upload of Image2.png complete"
    And I press "Save"
    # Verifying the attachment saved
    And I choose "Notes" in "Content" from main menu
    And I follow "Note block 1"
    And I should see "Image2.png"
