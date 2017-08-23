@javascript @core @core_group
Feature: Hiding group members options
In order to hide group members from either general public or other members
As an admin
I adjust the 'Hide members' and 'Hide members from members' group config options

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
    | groupadmin | Kupuhipa1 | randomteacher@example.org | Super | Teacher | mahara | internal | admin |
    | grouptutor | Kupuhipa1 | randomtutor@example.org | Diligent | Tutor | mahara | internal | member |
    | randomuser | Kupuhipa1 | randomuser2@example.org | Random | Person | mahara | internal | member |

Given the following "groups" exist:
    | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
    | GroupA | groupadmin | GroupA owned by groupadmin | course | ON | ON | all | ON | ON | UserA | grouptutor |

Scenario: Check if we can hide members of a group based on the 'Hide members' setting
    Given I log in as "groupadmin" with password "Kupuhipa1"
    And I choose "My groups" in "Groups" from main menu
    And I follow "Edit \"GroupA\" Settings"
    And I select "Hide tutors" from "editgroup_hidemembers"
    And I press "Save group"
    Then I should see "Group saved successfully"
    And I log out
    When I log in as "randomuser" with password "Kupuhipa1"
    And I choose "Find groups" in "Groups" from main menu
    And I follow "GroupA"
    Then I should not see "Diligent Tutor"
    And I should see "Angela User"
    And I log out
