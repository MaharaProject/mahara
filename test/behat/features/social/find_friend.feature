@javascript @core @core_user @core_portfolio @friends
Feature: Searching for users in the "Find people" page
    In order to search by name on the "Find people" page
    So I can befriend them or send them messages

Background:
    Given the following "institutions" exist:
    | name | displayname |
    | instone | Institution One |
    | insttwo | Institution Two |
    
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname |role |
    | UserA | Kupuhipa1 | UserA@example.org  | Angela | User | instone | internal | member |
    | UserB | Kupuhipa1 | UserB@example.org  | Bob | User | instone | internal | member |
    | UserC | Kupuhipa1 | UserC@example.org | Cecilia | User | insttwo | internal | member |

Scenario: Find users in your institution and outside your institution
    # Log in as a user
    Given I log in as "UserA" with password "Kupuhipa1"
    And I choose "Find people" in "Groups" from main menu

    # Searching within my own institution, I should only find users in my institution
    And I select "My institutions" from "Filter"
    And I fill in "Search" with "User"
    And I scroll to the id "main-nav"
    And I press "Search"
    Then I should see "Bob User"
    And I should not see "Cecilia User"

    # Searching outside my own institution, I should find all users
    When I select "Everyone" from "Filter"
    And I fill in "Search" with "smith"
    And I scroll to the id "main-nav"
    And I press "search_submit"
    # I should find everyone
    Then I should see "Cecilia User"
    And I should see "Bob User"
