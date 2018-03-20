@javascript @core
Feature: Add block with files for download
    As a user
    I want to add a download block to my page
    To make my files available for download

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

     And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01| user | UserA |

Scenario: Add Files to download block
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I click on "Page UserA_01" panel menu
    And I click on "Edit" in "Page UserA_01" panel menu
    And I expand "Media" node
    And I follow "File(s) to download"
    And I press "Add"
    And I expand "Files" node in the "#instconf_artefactfieldset_container" "css_element"

    And I attach the file "mahara_about.pdf" to "File"
    And I attach the file "Image1.jpg" to "File"
    And I press "Save"
    And I display the page
    # we don't want to actually download, so need to specify the inner link, not the download link
    And I click on "mahara_about.pdf" in the "File download heading 1" property
    Then I should see "PDF document"
    And I should see "Download"
    And I follow "Page UserA_01"
    And I click on "Image1.jpg" in the "File download heading 2" property
    Then I should see "JPEG Image"
    And I should see "Download"
