@javascript @core @core_group
Feature: Switching switch on and off when editing a forum
 In order to automatically subscribe people via switchbox
 As an admin
 I need to be able to flick the switch on and off

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
And the following "groups" exist:
    | name    | owner | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | public |
    | Turtles | admin | standard | ON | ON | all | ON | ON | UserA | 1 |

Scenario: Turning on and off switches in the group forums tab (Bug 1431569)
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I follow "Turtles"
    And I follow "Forums"
    And I follow "General discussion"
    And I follow "Edit forum"
    # There are 2 settings links on the page and it needs to identify which one to follow
    And I follow "Forum settings"
    # Checking "Automatically subscribe group members" switchbox is on by default
    And the "edit_interaction_autosubscribe" checkbox should be checked
    # Checking it can be turned off
    And I disable the switch "Automatically subscribe group members"
    # Checking it can turn back on
    And I enable the switch "Automatically subscribe group members"
    # Verifying that it did turn back on
    And the "edit_interaction_autosubscribe" checkbox should be checked
    # Checking off is the default setting on the close new topics checkbox
    And the "edit_interaction_closetopics" checkbox should not be checked
    # Checking it turns on
    And I enable the switch "Close new topics"
    # Checking it turns back off
    And I disable the switch "Close new topics"
    And I press "Save"
    # And I add another forum
    And I follow "Forums"
    And I follow "New forum"
    And I set the following fields to these values:
    | Title | Freshwater turtles |
    | Description | All about freshwater turtles |
    And I follow "Forum settings"
    # Checking "Automatically subscribe people" switchbox is on by default
    And the "edit_interaction_autosubscribe" checkbox should be checked
    And I press "Save"
    And I log out

# Person joins group and unsubscribes from Freshwater turtles forum
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I follow "Turtles"
    And I follow "Forums (tab)"
    And I should see "Unsubscribe" in the "General discussion" row
    And I should see "Unsubscribe" in the "Freshwater turtles" row
    When I click on "Unsubscribe" in "Freshwater turtles" row
    Then I should see "Forum unsubscribed successfully"
    And I should see "Subscribe" in the "Freshwater turtles" row
