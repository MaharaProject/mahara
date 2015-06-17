@javascript @core @core_artefact @core_portfolio
Feature: Button to reliably copy secret URLs
    In order to copy a secret URLs
    As an admin I need to create page
    So I can press secret URLs button and copy it

Scenario: Create a page and secret URLs to copy (Bug 1426983)
    # Log in as an Admin user
    Given I log in as "admin" with password "Password1"
    # Verifying log in was successful
    And I should see "Admin User"
    When I follow "Portfolio"
    And I press "Create page"
    And I fill in the following:
    | Page title    | Testing page 1 |
    And I press "Save"
    # Verifying page was created
    And I should see "Page saved successfully"
    And I follow "Display page"
    # Navigating to shared by be to click button
    And I choose "Shared by me" in "Portfolio"
    And I click on "Edit secret URL access"
    And I press "New secret URL"
    # Verifying Secret URLs was created
    And I choose "Shared by me" in "Portfolio"
    Then I should see "1"
