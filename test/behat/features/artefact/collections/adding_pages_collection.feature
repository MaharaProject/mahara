@javascript @core @core_portfolio @core_collection
Feature: Adding pages to a collection
   In order to add pages to a collection
   As an admin I need pages
   So I can add them to the collection

Scenario: Creating a collection AND adding pages
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Create page 1
    When I follow "Portfolio"
    And I press "Create page"
    And I fill in the following:
        | Page title | Testing page 1 |
    And I press "Save"
    # Create page 2
    And I follow "Portfolio"
    And I press "Create page"
    And I fill in the following:
        | Page title | Testing page 2 |
    And I press "Save"
    # Create page 3
    And I follow "Portfolio"
    And I press "Create page"
    And I fill in the following:
        | Page title | Testing page 3 |
    And I press "Save"
    # Create Test collection
    And I choose "Collections" in "Portfolio"
    And I follow "New collection"
    And I fill in the following:
        | Collection name | Test Collection	|
        | Collection description | Test	|
    # Adding page 1, 2 & 3 to the collection
    And I press "Next: Edit collection pages"
    And I follow "All"
    Then I press "Add pages"
    # Verifying that the pages were added
    And I should see "Testing page 1"
    And I should see "Testing page 2"
    And I should see "Testing page 3"
