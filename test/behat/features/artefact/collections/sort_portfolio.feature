@javascript @core @core_portfolio
Feature: Sort portfolio pages on portfolio page overview
    In order to sort by pages
    As an admin create pages
    So I can sort them by their modified date

Scenario: Sort portfolio pages on portfolio page overview (Bug 1426983)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Creating page 1
    When I follow "Portfolio"
    And I press "Create page"
    And I fill in the following:
        | Page title    | A1    |
    And I press "Save"
    And I follow "Portfolio"
    # Creating page 2
    And I press "Create page"
    And I fill in the following:
        | Page title    | B2    |
    And I press "Save"
    # Creating page 3
    And I follow "Portfolio"
    And I press "Create page"
    And I fill in the following:
        | Page title    | C3PO  |
    And I press "Save"
    # Sort pages by Last modified
    Then I follow "Portfolio"
    And I select "Last modified" from "Sort by:"
    And I press "Search"
    # Checking they are all there and saved
    And I should see "A1"
    And I should see "B2"
    And I should see "C3PO"
    And I should see "Dashboard page"
    And I should see "Profile page"
    # Checking they are in the right order
    And "C3PO" "link" should appear before "B2" "link"
    And "B2" "link" should appear before "A1" "link"
