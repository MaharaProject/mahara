@javascript @core @group
Feature: Mahara users can participate in groups
  As a mahara user
  I need to participate in groups

  Background:
    Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
      | userB | Password1 | test02@example.com | Son | Nguyen | mahara | internal | member |
      | userC | Password1 | test03@example.com | Jack | Smith | mahara | internal | member |
      | userD | Password1 | test04@example.com | Eden | Wilson | mahara | internal | member |
      | userE | Password1 | test05@example.com | Emily | Pham | mahara | internal | member |
    And the following "groups" exist:
      | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
      | group 01 | userA | This is group 01 | standard | ON | ON | all | ON | ON | userB, userC | userD |
  Scenario: Join a group
    Given I log in as "userE" with password "Password1"
    When I go to "group/find.php"
    And I wait "1" seconds
    Then I should see "group 01"
    When I click on "group 01"
    Then I should see "About"
    When I press "Join this group"
    And I wait "1" seconds
    Then I should see "You are now a group member."
