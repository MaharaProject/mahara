@javascript @core @core_group
Feature: Members of a group should be listed based on their role
    In order to see the order of members in a group
    As an admin create users
    So I can create a group and add them to it

Background:
    Given the following "users" exist:
    | username  | password  | email | firstname | lastname  | institution   | authname  |role   |
    | bob   | Kupuhipa1   | bob@example.com   | Bob   | Bobby | mahara    | internal  | member    |
    | jen   | Kupuhipa1   | jen@example.com   | Jen   | Jenny | mahara    | internal  | member    |

Scenario: Creating a group and adding members to it (Bug 1426983)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Creating Testing group 1
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Testing Group 1"
    And I select "Course: Member, Tutor, Admin" from "Roles"
    And I press "Save group"
    # Log out as "Admin user"
    And I follow "Logout"
    # Log in as user 1
    And I log in as "bob" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Bob Bobby"
    # Joining Testing group 1
    And I choose "Find groups" in "Groups"
    And I press "Join this group"
    # Log out as user 1
    And I follow "Logout"
    # Log in as user 2
    And I log in as "jen" with password "Kupuhipa1"
    #Verifying log in was successful
    And I should see "Jen Jenny"
    # Joining Testing group 1
    And I choose "Find groups" in "Groups"
    And I press "Join this group"
    # Log out as user 2
    And I follow "Logout"
    # Log in as "Admin" user
    And I log in as "admin" with password "Kupuhipa1"
    # Going to Groups and setting it to sort by
    And I follow "Groups"
    And I follow "Testing Group 1"
    And I follow "Members"
    # Verifying they are out of order first
    And "Bob Bobby (bob)" "link" should appear before "Jen Jenny (jen)" "link"
    And I select "Name Z to A" from "sorted by:"
    And I press the key "Enter" in the "Search:" field
    # Verifying I see them in order
    And "Jen Jenny (jen)" "link" should appear before "Bob Bobby (bob)" "link"
