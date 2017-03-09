@javascript @core @core_group
Feature: Hiding group members options
In order to hide group members from either general public or other members
As an admin
I adjust the 'Hide members' and 'Hide members from members' group config options

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | groupmember | randomuser | randomuser@random.com | Group | Member | mahara | internal | member |
    | groupadmin | randomteacher | randomteacher@random.com | Super | Teacher | mahara | internal | admin |
    | grouptutor | randomtutor | randomtutor@random.com | Diligent | Tutor | mahara | internal | member |
    | randomuser | randomuser2 | randomuser2@random.com | Random | Person | mahara | internal | member |

Given the following "groups" exist:
    | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
    | Test group | groupadmin | My test group | course | ON | ON | all | ON | ON | groupmember | grouptutor |

Scenario: Check if we can hide members of a group based on the 'Hide members' setting
    Given I log in as "groupadmin" with password "randomteacher"
    And I choose "My groups" in "Groups" from Main menu
    And I follow "Edit \"Test group\" Settings"
    And I select "Hide tutors" from "editgroup_hidemembers"
    And I press "Save group"
    Then I should see "Group saved successfully"
    And I log out
    When I log in as "randomuser" with password "randomuser2"
    And I choose "Find groups" in "Groups" from Main menu
    And I follow "Test group"
    Then I should not see "Diligent Tutor"
    And I should see "Group Member"
    And I log out
