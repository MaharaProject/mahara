@javascript @core @core_artefact @core_portfolio @core_collection
Feature: Adding pages to a collection
   In order to add pages to a collection
   As an admin I need pages
   So I can add them to the collection

Background:
     Given the following "pages" exist:
     | title | description| ownertype | ownername |
     | Testing page 1 | Admin's page 01 | user | admin |
     | Testing page 2 | Admin's page 02 | user | admin |
     | Testing page 3 | Admin's page 03 | user | admin |
     | A page 4 | Admin's page 04 | user | admin |

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
    And I wait "1" seconds
    Then I press "Add pages"
    # Verifying that the pages were added
    And I should see "Testing page 1"
    And I should see "Testing page 2"
    And I should see "Testing page 3"
    And I should see "A page 4"
    # Sort pages by Last modified
    Then I choose "Portfolio" from main menu
    And I select "Alphabetical" from "Sort by:"
    And I press the key "Enter" in the "Search:" field
    And "A page 4" "link" should appear before "Testing page 1" "link"
    And I select "Last modified" from "Sort by:"
    And I press the key "Enter" in the "Search:" field
    # Checking they are in the right order
    And I wait "1" seconds
    And "Testing page 1" "link" should appear before "Testing page 2" "link"
    And "Testing page 2" "link" should appear before "Testing page 3" "link"

    # Exporting pages and collections
    And I choose "Export" in "Portfolio" from main menu
    And I select the radio "Standalone HTML website"
    And I select the radio "Just some of my pages"
    And I scroll to the base of id "whatviewsselection"
    And I should see "Pages to export"
    And I select the radio "Just some of my collections"
    #give time to display the section
    And I wait "1" seconds
    And I scroll to the id "whatcollections"

    And I should see "Collections to export"
    And I select the radio "All my data"
    #give time to hide the sections
    And I wait "1" seconds
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
    And I wait "1" seconds
    And I press "Add pages"
    And I should see "1 page added to collection"
    And I follow "Done"
