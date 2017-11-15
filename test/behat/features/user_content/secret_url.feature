@javascript @core @core_artefact @core_portfolio
Feature: Button to reliably copy secret URLs
    In order to copy a secret URLs
    As an admin I need to create page
    So I can press secret URLs button and copy it

Background:
    Given the following "pages" exist:
    | title | description| ownertype | ownername |
    | Page admin_01 | Page | admin | admin |

Scenario: Create a page and secret URLs to copy (Bug 1426983)
    # Log in as an Admin user
    Given I log in as "admin" with password "Kupuhipa1"
    # Navigating to shared by be to click button
    And I choose "Shared by me" in "Portfolio" from main menu
    And I click on "Edit secret URL access" in "Page admin_01" row
    And I press "New secret URL"
    # Verifying Secret URLs was created
    And I choose "Shared by me" in "Portfolio" from main menu
    Then I should see "1" in the "Secret urls - table row 1" property
