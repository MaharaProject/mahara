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
    When I follow "Drag to add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I set the field "Block title" to "Image Block"
    And I click on "Image" in the "Content types" property
    And I follow "Image"
    And I attach the file "Image1.jpg" to "File"
    And I press "Save"
    And I click on "Display page"
    And I click on "Details"
    And I should see "Add comment" in the ".block-header .commentlink" "css_element"
    And I should see "Details" in the ".commentlink" "css_element"
    # Open the modal and see metadata details and add comment form
    When I follow "Add comment"
    And I should see "Image1.jpg" in the "div#configureblock" "css_element"
    And I should see "Preview" in the "div#configureblock" "css_element"
    And I fill in "This is a comment in a modal" in editor "Comment"
    And I click on "Comment"
    And I should see "Comment submitted"
    And I should see "Comments (1) and details" in the ".commentlink" "css_element"
    # Delete comment from modal
    When I follow "Comments (1) and details"
    And I should see "This is a comment in a modal"
    And I delete the "This is a comment in a modal" row
    And I wait "1" seconds
    Then the "div#modal_messages" element should contain "Comment removed"
    And I close the dialog
    And I should see "Add comment"
    And I should see "Details" in the ".commentlink" "css_element"
    # Disable comments on the artefact and see modal reflect changes
    When I follow "Add comment"
    And I should not see "Comment removed"
    And I click on "Comment"
    Then the "div#modal_messages" element should contain "There was an error with submitting this form. Please check the marked fields and try again."
    And I close the dialog
    And I click on "Edit" in the ".pageactions" "css_element"
    And I configure the block "Image Block"
    And I wait "1" seconds
    And I click on "Image - Image1.jpg"
    And I click on "Edit file \"Image1.jpg\""
    And I scroll to the base of id "instconf_artefactid_edit_allowcomments"
    And I should see "Comments"
    When I disable the switch "Comments"
    And I press "Save changes"
    And I close the dialog
    And I click on "Display page" in the ".pageactions" "css_element"
    And I should see "Details" in the ".modal_link" "css_element"
    And I should not see "Add comment" in the ".modal_link" "css_element"
    # The image in the block is also a link to the modal
    And I follow "Details"
    And I should see "Preview" in the "div#configureblock" "css_element"
    And I should not see "Add comment" in the "div#configureblock" "css_element"

Scenario: Add a block with multiple artefacts and see the comments and details header change accordingly
    Given I log in as "UserA" with password "Kupuh1pa!"
    Given I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    When I follow "Drag to add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I set the field "Block title" to "File(s) to Download Block"
    And I click on "File(s) to download" in the "Content types" property
    And I expand "Files" node in the "#instconf_artefactfieldset_container" "css_element"
    And I attach the file "Image2.png" to "File"
    And I attach the file "mahara_about.pdf" to "File"
    And I click on "Edit file \"mahara_about.pdf\""
    When I disable the switch "Comments"
    And I press "Save changes"
    And I press "Save"
    And I click on "Display page" in the ".pageactions" "css_element"
    And I click on "Details" in the ".pageactions" "css_element"
    Then the "Image2.png" row should contain display button "Details"
    And the "Image2.png" row should contain display button "Add comment"
    And the "mahara_about.pdf" row should contain display button "Details"
    And the "mahara_about.pdf" row should not contain display button "Add comment"
    And I should not see "Add comment" in the ".block-header .modal_link" "css_element"
