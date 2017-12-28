@javascript @core @portfolio
Feature: Creating a page with stuff in it
   In order to have a portfolio
   As a user I need navigate to to portfolio
   So I can create a page and add content to it

Scenario: Creating a page with content in it (Bug 1426983)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Navigating to Portfolio to create a page
    # This is the test for manually creating a page
    And I choose "Portfolio" from main menu
    And I follow "Add"
    And I click on "Page" in the dialog
    And I fill in the following:
    | Page title | Test view |
    And I press "Save"
    # Adding media block
    And I expand "Media" node
    And I follow "File(s) to download"
    And I press "Add"
    And I press "Save"
    # Adding Journal block
    And I expand "Journals" node in the "blocktype sidebar" property
    And I follow "Recent journal entries"
    And I press "Add"
    And I press "Save"
    # Adding profile info block
    And I expand "Personal info" node in the "blocktype sidebar" property
    And I follow "Profile information"
    And I press "Add"
    And I press "Save"
    # Adding external media block - but cancel out
    And I expand "External" node in the "blocktype sidebar" property
    And I follow "External media"
    And I press "Add"
    And I press "Remove"
    And I display the page
    # Verifying the page saved and is clickable
    Then I should see "Test view"
