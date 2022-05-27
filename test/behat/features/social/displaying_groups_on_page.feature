@javascript @core @core_group
Feature: Displaying multiple groups on a page
    In order to better organize the groups
    As an admin create 10 groups
    So I can limit the number of groups showing in mygroup display on the profile page

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
    And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | GroupA | UserA | GroupA owned by UserA | standard | ON | OFF | all | ON | ON | admin |  |
     | GroupB | UserA | GroupB owned by UserA | standard | ON | OFF | all | ON | ON | admin |  |
     | GroupC | UserA | GroupC owned by UserA | standard | ON | OFF | all | ON | ON | admin |  |
     | GroupD | UserA | GroupD owned by UserA | standard | ON | OFF | all | ON | ON | admin |  |
     | GroupE | UserA | GroupE owned by UserA | standard | ON | OFF | all | ON | ON | admin |  |
     | GroupF | UserA | GroupF owned by UserA | standard | ON | OFF | all | ON | ON | admin |  |
     | GroupG | UserA | GroupG owned by UserA | standard | ON | OFF | all | ON | ON | admin |  |
     | GroupH | UserA | GroupH owned by UserA | standard | ON | OFF | all | ON | ON | admin |  |
     | GroupI | UserA | GroupI owned by UserA | standard | ON | OFF | all | ON | ON | admin |  |
     | GroupJ | UserA | GroupJ owned by UserA | standard | ON | OFF | all | ON | ON | admin |  |

Scenario: Create groups and limit display on profile page (Bug 1426983)
    # Log in as a normal user
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Creating several groups to test
    And I choose "Groups" in "Engage" from main menu
    And I click on "Create group"
    And I set the following fields to these values:
    | Group name | GroupK |
    | Group description | GroupA owned by UserA |
    And I click on "Save group"
    # Changing the amount of groups seen in My groups block
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Profile page"
    And I click on "Edit"
    And I configure the block "My groups"
    And I set the following fields to these values:
    | Maximum number of groups to display | 3 |
    And I click on "Save"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Profile page"
    And I should see "11 groups"
