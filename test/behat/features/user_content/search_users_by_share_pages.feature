@javascript @core @core_user @core_portfolio
Feature: Searching by user in Shared Pages
    In order to search by name on the shared with me page
    As an admin I need to create users
    So I can search for them by name

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname |role |
    | UserA | Kupuhipa1 | UserA@example.org  | Angela | User | mahara | internal | member |
    | UserB | Kupuhipa1 | UserB@example.org  | Bob | User | mahara | internal | member |

Scenario: Create users and search for them (Bug 897586)
    # Log in as the student user
    Given I log in as "UserA" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Angela User"
    # Creating page one
    And I choose "Portfolio" from main menu
    And I follow "Add"
    And I click on "Page" in the dialog
    And I fill in the following:
    | Page title | Testing page 1 |
    And I press "Save"
    # Verifying the page has been created
    And I should see "Page saved successfully"
    # Creating page two
    And I choose "Portfolio" from main menu
    And I follow "Add"
    And I click on "Page" in the dialog
    And I fill in the following:
    | Page title | Testing page 2 |
    And I press "Save"
    # Verifying the page has been created
    And I should see "Page saved successfully"
    # Sharing both of the pages that have been created
    And I choose "Shared by me" in "Portfolio" from main menu
    And I click on "Edit access" in "Testing page 1" row
    And I set the select2 value "Testing page 1, Testing page 2" for "editaccess_views"
    And I select "Public" from "accesslist[0][searchtype]"
    And I press "Save"
    # Verifying that both of the pages have been shared
    And I should see "Access rules were updated for 2 pages."
    And I log out
    # Log in as the second student user
    And I log in as "UserB" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Bob User"
    # Creating page three
    And I choose "Portfolio" from main menu
    And I follow "Add"
    And I click on "Page" in the dialog
    And I fill in the following:
    | Page title | Testing page 3 |
    And I press "Save"
    # Verifying the page has been created
    And I should see "Page saved successfully"
    # Creating page 4
    And I choose "Portfolio" from main menu
    And I follow "Add"
    And I click on "Page" in the dialog
    And I fill in the following:
    | Page title | Testing page 4 |
    And I press "Save"
    # Verifying the page has been created
    And I should see "Page saved successfully"
    # Sharing both of the pages Bob created
    And I choose "Shared by me" in "Portfolio" from main menu
    And I click on "Edit access" in "Testing page 3" row
    And I set the select2 value "Testing page 3, Testing page 4" for "editaccess_views"
    And I select "Public" from "accesslist[0][searchtype]"
    And I press "Save"
    # Verifying that both of the pages have been shared
    And I should see "Access rules were updated for 2 pages."
    And I log out
    # Logging back in as admin to search for users on the shared with me page
    And I log in as "admin" with password "Kupuhipa1"
    And I choose "Shared with me" in "Portfolio" from main menu
    # Entering Angela name in the search box
    And I fill in the following:
    | Search: | Angela |
    And I check "Public"
    And I press "search_submit"
    # Veryfing I have seen Angela's pages
    Then I should see "page 1"
    And I should see "page 2"
    # Veryfing I have not seen Bob's pages
    And I should not see "page 3"
    And I should not see "page 4"
