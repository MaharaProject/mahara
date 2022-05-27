@javascript @core @core_artefact
Feature: Mahara people can view comments and details of their content on pages
As a person
I want to view details and comments of my content on pages

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Betty | User | mahara | internal | member |

    And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01| user | UserA |

Scenario: Create a block and see comments and details modal and headers update accordingly
    Given I log in as "UserA" with password "Kupuh1pa!"
    Given I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property
    And I set the field "Block title" to "Image Block"
    And I click on blocktype "Image"
    And I expand the section "Image"
    And I attach the file "Image1.jpg" to "File"
    And I click on "Save" in the "Submission" "Modal" property
    And I display the page
    And I click on "Details"
    And I should see "Add comment" in the "Block header" "Blocks" property
    And I should see "Details" in the "Block header" "Blocks" property
    # Open the modal and see metadata details and add comment form
    When I click on "Add comment"
    And I should see "Image1.jpg" in the "Modal header" "Modal" property
    And I should see "Preview" in the "Modal content" "Modal" property
    And I fill in "This is a comment in a modal" in editor "Comment"
    And I click on "Comment"
    And I should see "Comment submitted"
    And I should see "Comments (1) and details" in the "Block header" "Blocks" property
    # Delete comment from modal
    When I click on "Comments (1) and details"
    And I should see "This is a comment in a modal"
    And I delete the "This is a comment in a modal" row
    And I wait "1" seconds
    Then I should see "Comment removed" in the "Message" "Modal" property
    And I close the dialog
    And I should see "Add comment"
    And I should see "Details" in the "Block header" "Blocks" property
    # Disable comments on the artefact and see modal reflect changes
    When I click on "Add comment"
    And I should not see "Comment removed"
    And I click on "Comment"
    Then I should see "There was an error with submitting this form. Please check the marked fields and try again." in the "Message" "Modal" property
    And I close the dialog
    And I click on "Edit" in the "Page action buttons" "Views" property
    And I configure the block "Image Block"
    And I wait "1" seconds
    And I expand the section "Image1.jpg"
    And I click on "Edit file \"Image1.jpg\""
    And I scroll to the base of id "instconf_artefactid_edit_allowcomments"
    And I should see "Comments"
    When I disable the switch "Comments"
    And I click on "Save changes"
    And I close the dialog
    And I click on "Display page" in the "Page action buttons" "Views" property
    And I should see "Details" in the "Block header" "Blocks" property
    And I should not see "Add comment" in the "Block header" "Blocks" property
    # The image in the block is also a link to the modal
    And I click on "Details" in the "Block header" "Blocks" property
    And I should see "Preview" in the "Modal content" "Modal" property
    And I should not see "Add comment" in the "Block header" "Blocks" property

Scenario: Add a block with multiple artefacts and see the comments and details header change accordingly
    Given I log in as "UserA" with password "Kupuh1pa!"
    Given I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property
    And I set the field "Block title" to "File(s) to Download Block"
    And I click on blocktype "File(s) to download"
    And I expand the section "Files"
    And I attach the file "Image2.png" to "File"
    And I attach the file "mahara_about.pdf" to "File"
    And I click on "Edit file \"mahara_about.pdf\""
    When I disable the switch "Comments"
    And I click on "Save changes"
    And I click on "Save" in the "Submission" "Modal" property
    And I click on "Display page" in the "Page action buttons" "Views" property
    And I click on "Details" in the "Page action buttons" "Views" property
    Then the "Image2.png" row should contain display button "Details"
    And the "Image2.png" row should contain display button "Add comment"
    And the "mahara_about.pdf" row should contain display button "Details"
    And the "mahara_about.pdf" row should not contain display button "Add comment"