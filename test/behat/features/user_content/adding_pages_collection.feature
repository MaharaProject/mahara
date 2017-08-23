@javascript @core @core_artefact @core_portfolio @core_collection
Feature: Adding pages to a collection
   In order to add pages to a collection
   As an admin I need pages
   So I can add them to the collection

Background:
     Given the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page admin_01 | Page 01 | user | admin |
     | Page admin_02 | Page 02 | user | admin |
     | Page admin_03 | Page 03 | user | admin |
     | AA Page admin_04 | Page 04 | user | admin |

Scenario: Creating a collection AND adding pages
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Create Test collection
    And I choose "Pages and collections" in "Portfolio" from main menu
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
    And I should see "Page admin_01"
    And I should see "Page admin_02"
    And I should see "Page admin_03"
    And I should see "AA Page admin_04"
    # Sort pages by Last modified
    Then I choose "Portfolio" from main menu
    And I select "Alphabetical" from "Sort by:"
    And I press the key "Enter" in the "Search:" field
    And "AA Page admin_04" "link" should appear before "Page admin_01" "link"
    And I select "Last modified" from "Sort by:"
    And I press the key "Enter" in the "Search:" field
    # Checking they are in the right order
    And "Page admin_01" "link" should appear before "Page admin_02" "link"
    And "Page admin_02" "link" should appear before "Page admin_03" "link"

    # Exporting pages and collections
    And I choose "Export" in "Portfolio" from main menu
    And I select the radio "Standalone HTML website"
    And I select the radio "Just some of my pages"
    And I should see "Pages to export"
    And I select the radio "Just some of my collections"
    And I should see "Collections to export"
    And I select the radio "All my data"
    And I should not see "Pages to export"
    And I should not see "Collections to export"

    # Sharing the collection then adding in a new page
    And I choose "Shared by me" in "Portfolio" from main menu
    And I follow "Edit access"
    And I select "Registered users" from "accesslist[0][searchtype]"
    And I press "Save"
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I follow "Add"
    And I click on "Page" in the dialog
    And I set the following fields to these values:
    | Page title | New page |
    | Page description | testing |
    And I press "Save"
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I click on "Test Collection" panel menu
    And I click on "Manage" in "Test Collection" panel menu
    And I follow "All"
    And I press "Add pages"
    And I should see "1 page added to collection"
    And I follow "Done"
