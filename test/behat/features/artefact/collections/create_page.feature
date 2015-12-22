@javascript @portfolio @failed
Feature: Creating a page with stuff in it
   In order to have a portfolio
   As a user I need navigate to to portfolio
   So I can create a page and add content to it

Scenario: Creating a page with content in it (Bug 1426983)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Navigating to Portfolio to create a page
    And I follow "Portfolio"
    And I press "Create page"
    And I fill in the following:
    | Page title | Test view |
    And I press "Save"
    # Adding media block
    And I expand "Media" node
    And I wait until the page is ready
    And I follow "File(s) to download"
    And I press "Add"
    And I press "Save"
    # Adding Journal block
    And I expand "Journals" node in the "div#content-editor-foldable" "css_element"
    And I wait until the page is ready
    And I follow "Recent journal entries"
    And I press "Add"
    And I press "Save"
    # Adding profile info block
    And I expand "Personal info" node
    And I wait until the page is ready
    And I follow "Profile information"
    And I press "Add"
    And I press "Save"
    # Adding external media block - but cancel out
    And I expand "External" node
    And I wait until the page is ready
    And I follow "External media"
    And I press "Add"
    And I press "Remove"
    And I display the page
    # Verifying the page saved and is clickable
    Then I should see "Test view"