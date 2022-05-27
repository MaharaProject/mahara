@javascript @core @core_artefact @core_portfolio
Feature: Check Secret URL functionality
    1) Log in and create seceret URL
    2) Verify copy icon button is displayed
    3) Verify that Secret URL was created

Background:
    Given the following "pages" exist:
    | title | description| ownertype | ownername |
    | Page admin A | Page | admin | admin |

Scenario: Create a page and secret URLs to copy (Bug 1426983)
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Shared by me" in "Share" from main menu
    And I click on "Edit secret URL access" in "Page admin A" row
    And I click on "New secret URL"
    And I should see "Copy secret URL to the clipboard"
    And I choose "Shared by me" in "Share" from main menu
    Then I should see "1" in the "Page admin A" row

 """
 ToDo
 This script still needs the following steps:
 1) user to click the "Copy secret URL to the clipboard" icon button
 2) paste the copied URL into an address bar
 3) verify the page title is "Page admin_01"

 NOTE - To write a function for the above will take a great deal of effort and is not feasible at this time
 """
