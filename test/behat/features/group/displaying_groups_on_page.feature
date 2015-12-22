@javascript @core @core_group
Feature: Displaying multiple groups on a page
    In order to better organize the groups
    As an admin create 10 groups
    So I can limit the number of groups showing in mygroup display on the profile page

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
    And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | Testing Group 1 | userA | This is group 01 | standard | ON | OFF | all | ON | ON | admin |  |
     | Testing Group 2 | userA | This is group 02 | standard | ON | OFF | all | ON | ON | admin |  |
     | Testing Group 3 | userA | This is group 03 | standard | ON | OFF | all | ON | ON | admin |  |
     | Testing Group 4 | userA | This is group 04 | standard | ON | OFF | all | ON | ON | admin |  |
     | Testing Group 5 | userA | This is group 05 | standard | ON | OFF | all | ON | ON | admin |  |
     | Testing Group 6 | userA | This is group 06 | standard | ON | OFF | all | ON | ON | admin |  |
     | Testing Group 7 | userA | This is group 07 | standard | ON | OFF | all | ON | ON | admin |  |
     | Testing Group 8 | userA | This is group 08 | standard | ON | OFF | all | ON | ON | admin |  |
     | Testing Group 9 | userA | This is group 09 | standard | ON | OFF | all | ON | ON | admin |  |
     | Testing Group 10 | userA | This is group 10 | standard | ON | OFF | all | ON | ON | admin |  |

Scenario: Create groups and limit display on profile page (Bug 1426983)
    # Log in as a normal user
    Given I log in as "userA" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Pete"
    # Creating several groups to test
    And I choose "Groups"
    And I follow "Create group"
    And I set the following fields to these values:
    | Group name | Testing Group 11 |
    And I fill in "This is group 11" in WYSIWYG editor "editgroup_description_ifr"
    And I press "Save group"
    # Changing the amount of groups seen in My groups block
    When I choose "Portfolio"
    And I follow "Profile page"
    And I follow "Edit this page"
    And I configure the block "My groups"
    And I fill in "Maximum number of groups to display" with "3"
    And I press "Save"
    And I choose "Portfolio"
    And I follow "Profile page"
    And I should see "11 groups"
