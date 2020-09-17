@javascript @core @core_user @core_portfolio
Feature: Searching by person in "Shared with me"
    In order to search by name on the shared with me page
    As an admin I need to create accounts
    So I can search for them by name

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname |role |
    | UserA | Kupuh1pa! | UserA@example.org  | Angela | User | mahara | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org  | Bob | User | mahara | internal | member |

    And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01 | user | UserA |
     | Page UserA_02 | Page 02 | user | UserA |
     | Page UserB_01 | Page 03 | user | UserB |
     | Page UserB_02 | Page 04 | user | UserB |
     | Page UserB_03 | Page 05 | user | UserB |
     | Page UserB_04 | Page 06 | user | UserB |

Scenario: Create accounts and search for them (Bug 897586)
    # Log in as the student user
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Sharing both of the pages that have been created
    And I choose "Shared by me" in "Share" from main menu
    And I click on "Edit access" in "Page UserA_01" row
    And I set the select2 value "Page UserA_01, Page UserA_02" for "editaccess_views"
    And I select "Public" from "accesslist[0][searchtype]"
    And I press "Save"
    # Verifying that both of the pages have been shared
    And I should see "Access rules were updated for 2 pages."
    And I log out
    # Log in as the second student account
    And I log in as "UserB" with password "Kupuh1pa!"
    # Verifying log in was successful
    And I should see "Bob User"
    # Sharing 2 of the pages Bob created to public
    And I choose "Shared by me" in "Share" from main menu
    And I click on "Edit access" in "Page UserB_01" row
    And I set the select2 value "Page UserB_01, Page UserB_02" for "editaccess_views"
    And I select "Public" from "accesslist[0][searchtype]"
    And I press "Save"
    # Verifying that both of the pages have been shared
    And I should see "Access rules were updated for 2 pages."
    # Sharing 1 of the pages Bob created with "Admin"
    And I choose "Shared by me" in "Share" from main menu
    And I click on "Edit access" in "Page UserB_01" row
    And I set the select2 value "Page UserB_03" for "editaccess_views"
    And I select "Person" from "accesslist[0][searchtype]"
    And I select "Admin Account" from select2 nested search box in row number "1"
    And I press "Save"
    # Verifying that the page has been shared
    And I should see "Access rules were updated for 1 page."
    And I log out
    # Logging back in as admin to search for people on the shared with me page
    And I log in as "admin" with password "Kupuh1pa!"
    And I choose "Shared with me" in "Share" from main menu
    # Entering Angela name in the search box
    And I fill in the following:
    | Search: | Angela |
    And I check "Public"
    And I press "search_submit"
    # Verifying I can see an Angela's page
    Then I should see "Page UserA_01"
    # Verifying I can see some of Bob's pages
    # Entering Bob name in the search box
    And I fill in the following:
    | Search: | Bob |
    And I check "Public"
    And I press "search_submit"
    And I should see "Page UserB_02"
    And I should see "Page UserB_03"
    # Verifying I cannot see Bob's page not shared
    And I should not see "Page UserB_04"
    And I should not see "Page UserA_01"
