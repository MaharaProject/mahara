@javascript @core @core_group
Feature: Displaying multiple groups on a page
    In order to better organize the groups
    As an admin create 10 groups
    So I can limit the number of groups showing in mygroup display on the profile page


Scenario: Create groups and limit display on profile page (Bug 1426983)
    # Log in as "Admin" User
    Given I log in as "admin" with password "Password1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Creating several groups to test
    When I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Testing Group 1"
    And I press "Save group"
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Testing Group 2"
    And I press "Save group"
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Testing Group 3"
    And I press "Save group"
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Testing Group 4"
    And I press "Save group"
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Testing Group 5"
    And I press "Save group"
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Testing Group 6"
    And I press "Save group"
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Testing Group 7"
    And I press "Save group"
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Testing Group 8"
    And I press "Save group"
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Testing Group 9"
    And I press "Save group"
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Testing Group 10"
    And I press "Save group"
    # Changing the amount of groups seen in My groups block
    Then I follow "Portfolio"
    And I follow "Profile page"
    And I follow "Edit this page"
    And I press "Configure 'My groups' block (ID 16)"
    And I fill in "Maximum number of groups to display" with "3"
    And I press "Save"
    And I press "Done"
    And I follow "Profile page"
    And I should see "10 groups"