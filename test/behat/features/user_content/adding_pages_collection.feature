@javascript @core @core_artefact @core_portfolio @core_collection
Feature: Adding pages to a collection
   In order to add pages to a collection
   As an normal member I need pages
   So I can add them to the collection

Background:

     Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname |role |
     | UserA | Kupuh1pa! | UserA@example.org  | Angela | User | mahara | internal | member |

     Given the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01 | user | UserA |
     | Page UserA_02 | Page 02 | user | UserA |
     | Page UserA_03 | Page 03 | user | UserA |
     | Page UserA_04 | Page 04 | user | UserA |

Scenario: Creating a collection AND adding pages
    # Log in as "Normal" user
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Create Test collection
    # This is the test for manually creating a collection
    And I choose "Pages and collections" in "Create" from main menu
    And I follow "Add"
    And I click on "Collection" in the dialog
    And I fill in the following:
        | Collection name | Test Collection |
        | Collection description | Test |
    # Adding page 1, 2 & 3 to the collection
    And I press "Next: Edit collection pages"
    And I follow "All"
    Then I press "Add pages"
    # Verifying that the pages were added
    And I should see "Page UserA_01"
    And I should see "Page UserA_04"

    # Exporting pages and collections
    And I choose "Export" in "Manage" from main menu
    And I select the radio "Just some of my pages"
    And I should see "Pages to export"
    And I select the radio "Just some of my collections"
    And I should see "Collections to export"
    And I select the radio "All my data"
    And I should not see "Pages to export"
    And I should not see "Collections to export"

    # Sharing the collection then adding in a new page
    And I choose "Shared by me" in "Share" from main menu
    # Check that the Collection tab opens by default etc. (Bug 1890971)
    And I should see "Collection name"
    And I should not see "Page title"
    And I follow "Pages"
    And I should not see "Collection name"
    And I should see "Page title"
    And I follow "Collections"
    # Continue initial test
    And I follow "Edit access"
    And I select "Registered people" from "accesslist[0][searchtype]"
    And I press "Save"
    And I choose "Pages and collections" in "Create" from main menu
    And I follow "Add"
    And I click on "Page" in the dialog
    And I set the following fields to these values:
    | Page title | New page |
    | Page description | testing |
    And I press "Save"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Manage" in "Test Collection" card menu
    And I follow "All"
    And I press "Add pages"
    And I should see "1 page added to collection"
