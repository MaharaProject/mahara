@javascript @core @core_user @core_portfolio
Feature: Searching by user in Shared Pages
    In order to search by name on the shared with me page
    As an admin I need to create users
    So I can search for them by name

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname |role |
    | bob | mahara1 | bob@example.com  | Bob | Bobby | mahara | internal | member |
    | jen | mahara1 | jen@example.com  | Jen | Jenny | mahara | internal | member |

Scenario: Create users and search for them (Bug 897586)
    # Log in as the student user
    Given I log in as "bob" with password "mahara1"
    # Verifying log in was successful
    And I should see "Bob Bobby"
    # Creating page one
    And I follow "Portfolio"
    And I press "Create page"
    And I fill in the following:
    | Page title | Testing page 1 |
    And I press "Save"
    # Verifying the page has been created
    And I should see "Page saved successfully"
    # Creating page two
    And I follow "Portfolio"
    And I press "Create page"
    And I fill in the following:
    | Page title | Testing page 2 |
    And I press "Save"
    # Verifying the page has been created
    And I should see "Page saved successfully"
    # Sharing both of the pages that have been created
    And I follow "Share page"
    And I set the select2 field "editaccess_views" to "Testing page 1, Testing page 2"
    And I select "Public" from "accesslist[0][searchtype]"
    And I press "Save"
    # Verifying that both of the pages have been shared
    And I should see "Access rules were updated for 2 page(s)"
    And I follow "Logout"
    # Log in as the second student user
    And I log in as "jen" with password "mahara1"
    # Verifying log in was successful
    And I should see "Jen Jenny"
    # Creating page three
    And I follow "Portfolio"
    And I press "Create page"
    And I fill in the following:
    | Page title | Testing page 3 |
    And I press "Save"
    # Verifying the page has been created
    And I should see "Page saved successfully"
    # Creating page 4
    And I follow "Portfolio"
    And I press "Create page"
    And I fill in the following:
    | Page title | Testing page 4 |
    And I press "Save"
    # Verifying the page has been created
    And I should see "Page saved successfully"
    # Sharing both of the pages Jen created
    And I follow "Share page"
    And I set the select2 field "editaccess_views" to "Testing page 3, Testing page 4"
    And I select "Public" from "accesslist[0][searchtype]"
    And I press "Save"
    # Verifying that both of the pages have been shared
    And I should see "Access rules were updated for 2 page(s)"
    And I follow "Logout"
    # Logging back in as admin to search for users on the shared with me page
    And I log in as "admin" with password "Kupuhipa1"
    And I follow "Portfolio"
    And I choose "Shared with me" in "Portfolio"
    # Entering Bob name in the search box
    And I fill in the following:
    | Search: | Bob |
    And I follow "Advanced options"
    # Searching for public pages
    And I fill in the following:
    | Public | 1 |
    And I press "search_submit"
    # Veryfing I have seen Bobs pages
    Then I should see "page 1"
    And I should see "page 2"
    # Veryfing I have not seen Jens pages
    And I should not see "page 3"
    And I should not see "page 4"
