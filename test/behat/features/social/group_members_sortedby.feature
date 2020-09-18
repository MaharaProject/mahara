@javascript @core @core_group
Feature: Members of a group should be listed based on their role
    In order to see the order of members in a group
    As an admin create users
    So I can create a group and add them to it

Background:
    Given the following "users" exist:
    | username  | password  | email | firstname | lastname  | institution   | authname  |role   |
    | UserA   | Kupuh1pa!   | UserA@example.org   | Angela   | User | mahara    | internal  | member    |
    | UserB   | Kupuh1pa!   | UserB@example.org   | Bob   | User | mahara    | internal  | member    |
 
    And the following "groups" exist:
    | name             | owner | grouptype | editroles |
    | Testing Group 1  | admin | course    | all       |

Scenario: Creating a group and adding members to it (Bug 1426983)
    # Log in as user 1
    And I log in as "UserA" with password "Kupuh1pa!"
    # Joining Testing group 1
    And I choose "Groups" in "Engage" from main menu
    And I select "All groups" from "filter"
    And I press "Search"
    And I wait "1" seconds
    And I press "Join this group"
    # Log out as user 1
    And I log out
    # Log in as user 2
    And I log in as "UserB" with password "Kupuh1pa!"
    # Joining Testing group 1
    And I choose "Groups" in "Engage" from main menu
    And I select "All groups" from "filter"
    And I press "Search"
    And I wait "1" seconds
    And I press "Join this group"
    # Log out as user 2
    And I log out
    # Log in as "Admin" user
    And I log in as "admin" with password "Kupuh1pa!"
    # Going to Groups and setting it to sort by
    And I choose "Groups" in "Engage" from main menu
    And I follow "Testing Group 1"
    And I follow "Members" in the "Arrow-bar nav" property
    # Verifying they are out of order first
    And "Angela User (UserA)" "link" should appear before "Bob User (UserB)" "link"
    And I select "Name Z to A" from "sorted by:"
    And I press the key "Enter" in the "Search:" field
    # Verifying I see them in order
    And "Bob User (UserB)" "link" should appear before "Angela User (UserA)" "link"
