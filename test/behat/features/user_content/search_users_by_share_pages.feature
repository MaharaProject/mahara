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

    And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01 | user | UserA |
     | Page UserA_02 | Page 02 | user | UserA |
     | Page UserB_01 | Page 03 | user | UserB |
     | Page UserB_02 | Page 04 | user | UserB |

Scenario: Create users and search for them (Bug 897586)
    # Log in as the student user
    Given I log in as "UserA" with password "Kupuhipa1"
    # Sharing both of the pages that have been created
    And I choose "Shared by me" in "Portfolio" from main menu
    And I click on "Edit access" in "Page UserA_01" row
    And I set the select2 value "Page UserA_01, Page UserA_02" for "editaccess_views"
    And I select "Public" from "accesslist[0][searchtype]"
    And I press "Save"
    # Verifying that both of the pages have been shared
    And I should see "Access rules were updated for 2 pages."
    And I log out
    # Log in as the second student user
    And I log in as "UserB" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Bob User"
    # Sharing both of the pages Bob created
    And I choose "Shared by me" in "Portfolio" from main menu
    And I click on "Edit access" in "Page UserB_01" row
    And I set the select2 value "Page UserB_01, Page UserB_02" for "editaccess_views"
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
    # Verifying I can see Angela's pages
    Then I should see "Page UserA_01"
    And I should see "Page UserA_02"
    # Verifying I cannot see Bob's pages
    And I should not see "Page UserB_03"
    And I should not see "Page UserB_04"
