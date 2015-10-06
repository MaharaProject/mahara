@javascript @core @meta_test
Feature: Testing Behat's functionality
    In order to be able to use every functionality in Mahara is working correct
    As an admin I need to log in
    And test every link/file/button/checkbox etc. possible

Scenario: Meta test testing Behat's functionality (Bug #1387836)
    Given I log in as "admin" with password "Kupuhipa1"
    And I am on homepage
    And I follow "Content"
    And I should be on "artefact/internal/index.php"
    And I set the following fields to these values:
    | First name | test first name |
    | Last name | test last name |
    And I press "Save profile"
    And I follow "Portfolio"
    # Creating a page
    And I press "Create page"
    And I set the field "Page title" to "test page name 1"
    And I press "Save"
    # Verifying it saved
    And I should see "Page saved successfully"
    # Creating a collection
    And I choose "Collections" in "Portfolio"
    And I follow "New collection"
    And I set the field "Collection name" to "test collection name"
    And the "Page navigation bar" checkbox should be checked
    And I press "Next: Edit collection pages"
    # Adding pages to the collection
    And I press "Add pages"
    # Verifying it added
    And I should see "You need to select a page to add to the collection."
    And the checkbox "test page name 1" should be unchecked
    And I check "test page name 1"
    And the "test page name 1" checkbox should be checked
    And I press "Add pages"
    And I follow "Done"
    And I hover "Delete collection" "link"
    And I go to the homepage
    And I go to "Export"
    And I move backward one page
    And I wait "3" seconds
    And I move forward one page
    And I follow "Content"
    And the "First name" field should not contain "Jinelle"
    And the "First name" field should contain "test first name"

