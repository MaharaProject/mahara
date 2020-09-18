@javascript @core @core_group
Feature: Switching switches on the Edit group page
In order to edit a group
As an admin
I need to be able to turn the switches on and off and save the page

Background:
    Given the following "institutions" exist:
    | name | displayname |
    | instone | Institution One |
    | insttwo | Institution Two |

    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname |role |
    | UserA | Kupuh1pa! | UserA@example.org  | Angela | UserA | instone | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org  | Bob | UserB | instone | internal | member |
    | UserC | Kupuh1pa! | UserC@example.org | Cecilia | UserC | instone | internal | member |
    | UserD | Kupuh1pa! | UserD@example.org | Dave | UserD | insttwo | internal | member |
    | UserE | Kupuh1pa! | UserE@example.org | Earl | UserE | insttwo | internal | member |

    And the following "groups" exist:
    | name          | owner | grouptype | editroles |
    | The Avengers  | admin | standard  | all       |

    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "People" in "Engage" from main menu
    When I click on "Send friend request" in "Bob UserB" row
    Then I should see "Send Bob UserB a friendship request"
    When I fill in "Would you like to be my friend?" for "Message"
    And I press "Request friendship"
    Then I should see "Sent a friendship request to Bob UserB"
    When I click on "Send friend request" in "Cecilia UserC" row
    Then I should see "Send Cecilia UserC a friendship request"
    When I fill in "Would you like to be my friend Cecilia?" for "Message"
    And I press "Request friendship"
    Then I should see "Sent a friendship request to Cecilia UserC"
    When I select "Everyone" from "Filter"
    And I press "Search"
    And I click on "Send friend request" in "Dave UserD" row
    Then I should see "Send Dave UserD a friendship request"
    When I fill in "Would you like to be my friend Dave?" for "Message"
    And I press "Request friendship"
    Then I should see "Sent a friendship request to Dave UserD"
    And I log out

    # User B accepts the friendship request
    Given I log in as "UserB" with password "Kupuh1pa!"
    When  I follow "pending friend"
    Then I should see "Angela UserA (UserA)"
    When I press "Approve"
    Then I should see "Accepted friend request"
    And I log out
    Given I log in as "UserC" with password "Kupuh1pa!"
    When  I follow "pending friend"
    Then I should see "Angela UserA (UserA)"
    When I press "Approve"
    Then I should see "Accepted friend request"
    And I log out
    Given I log in as "UserD" with password "Kupuh1pa!"
    When  I follow "pending friend"
    Then I should see "Angela UserA (UserA)"
    When I press "Approve"
    Then I should see "Accepted friend request"
    And I log out

Scenario: Turning on and off switches on Group Edit page (Bug 1431569)
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I follow "Edit \"The Avengers\" Settings"
    # Checking all the switches can all be changed
    And I set the following fields to these values:
    | Open | 0 |
    | Controlled | 1 |
    | Request | 1 |
    | Friend invitations | 1 |
    | Recommendations | 0 |
    | Allow submissions | 1 |
    | Allow archiving of submissions | 1 |
    | Publicly viewable group | 1 |
    | Hide group | 1 |
    | Hide membership | 1 |
    | Hide membership from members | 1 |
    | Participation report | 1 |
    | Auto-add people | 1 |
    | Send forum posts immediately | 1 |
    And I press "Save group"
    And I follow "Edit \"The Avengers\" Settings"
    # Checking all the switches can all be changed back
    And I set the following fields to these values:
    | Open | 1 |
    | Controlled | 0 |
    | Request | 0 |
    | Friend invitations | 0 |
    | Recommendations | 0 |
    | Allow submissions | 0 |
    | Allow archiving of submissions | 0 |
    | Publicly viewable group | 0 |
    | Hide group | 0 |
    | Hide membership | 0 |
    | Hide membership from members | 0 |
    | Participation report | 0 |
    | Auto-add people | 0 |
    # Checking Friend Invitation and Recommendations can't both be on
    And I enable the switch "Friend invitations"
    And the "Recommendations" checkbox should not be checked
    And I enable the switch "Recommendations"
    And the "Friend invitations" checkbox should not be checked
    And I press "Save group"
    And I log out
    # group user recommend group to another user
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I select "All groups" from "filter"
    And I press "Search"
    And I wait "1" seconds
    When I click on "Join this group" in "The Avengers" row
    Then I should see "You are now a group member."
    When I follow "Recommend to friends"
    Then I should see "Bob UserB"
    And I should see "Cecilia UserC"
    And I should see "Dave UserD"
    When I select "Bob UserB" from "Potential members"
    And I press "rightarrow"
    And I select "Cecilia UserC" from "Potential members"
    And I press "rightarrow"
    And I select "Dave UserD" from "Potential members"
    And I press "rightarrow"
    And I press "Submit"
    Then I should see "3 recommendations sent"
    And I log out
    # Friend logs in and should see notification to join group
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I wait "2" seconds
    Then I should see "Angela UserA suggested you join a group"
    When I follow "Angela UserA suggested you join a group"
    Then I should see "Angela UserA suggested that you join the group \"The Avengers\" on Mahara - Acceptance test site"
    When I follow "The Avengers"
    Then I should see "About | The Avengers"
    When I press "Join this group"
    Then I should see "You are now a group member."
