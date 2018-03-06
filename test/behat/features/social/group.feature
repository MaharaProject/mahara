@javascript @core @group
Feature: Mahara users can participate in groups
  As a mahara user
  I need to participate in groups

  Background:
    Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
      | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
      | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | mahara | internal | member |
      | UserD | Kupuh1pa! | UserD@example.org | Dmitri | User | mahara | internal | member |
      | UserE | Kupuh1pa! | UserE@example.org | Evonne | User | mahara | internal | member |
    And the following "groups" exist:
      | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
      | GroupA | UserA | GroupA owned by UserA | standard | ON | ON | all | ON | ON | UserB, UserC | UserD |
  Scenario: Join a group
    Given I log in as "UserE" with password "Kupuh1pa!"
    And I choose "Find groups" in "Groups" from main menu
    When I click on "GroupA"
    Then I should see "About"
    When I press "Join this group"
    Then I should see "You are now a group member."
