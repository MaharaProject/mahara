@javascript @core @group
Feature: Mahara users can participate in groups
  As a mahara user
  I need to participate in groups

  Background:
    Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
      | UserB | Kupuhipa1 | UserB@example.org | Bob | User | mahara | internal | member |
      | UserC | Kupuhipa1 | UserC@example.org | Cecilia | User | mahara | internal | member |
      | UserD | Kupuhipa1 | UserD@example.org | Dmitri | User | mahara | internal | member |
      | UserE | Kupuhipa1 | UserE@example.org | Evonne | User | mahara | internal | member |
    And the following "groups" exist:
      | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
      | GroupA | UserA | GroupA owned by UserA | standard | ON | ON | all | ON | ON | UserB, UserC | UserD |
  Scenario: Join a group
    Given I log in as "UserE" with password "Kupuhipa1"
    And I choose "Find groups" in "Groups" from main menu
    Then I should see "GroupA"
    When I click on "GroupA"
    Then I should see "About"
    When I press "Join this group"
    Then I should see "You are now a group member."
