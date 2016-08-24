@javascript @core @core_artefact @core_portfolio @core_collection
Feature: Adding pages to a collection
   In order to add pages to a collection
   As an admin I need pages
   So I can add them to the collection

Background:
     Given the following "pages" exist:
     | title | description| ownertype | ownername |
     | Testing page 1 | UserD's page 01 | user | admin  |
     | Testing page 2 | UserD's page 02 | user | admin |
     | Testing page 3 | UserA's page 01 | user | admin |

Scenario: Creating a collection AND adding pages
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Create Test collection
    And I choose "Collections" in "Portfolio"
    And I follow "New collection"
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
      # Sort pages by Last modified
    Then I follow "Portfolio"
    And I select "Last modified" from "Sort by:"
    And I press the key "Enter" in the "Search:" field
    # Checking they are in the right order
    And I wait "1" seconds
    And "Testing page 1" "link" should appear before "Testing page 2" "link"
    And "Testing page 2" "link" should appear before "Testing page 3" "link"

    # Sharing the collection then adding in a new page
    And I choose "Shared by me" in "Portfolio"
    And I follow "Edit access"
    And I select "Registered users" from "accesslist[0][searchtype]"
    And I press "Save"
    And I choose "Pages" in "Portfolio"
    And I press "Create page"
    And I set the following fields to these values:
    | Page title | New page |
    | Page description | testing |
    And I press "Save"
    And I choose "Collections" in "Portfolio"
    And I follow "Manage pages"
    And I follow "All"
    And I wait "1" seconds
    And I press "Add pages"
    And I follow "Done"
    Then I should see "New page"
