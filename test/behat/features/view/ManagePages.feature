@javascript @core @page
Feature: Mahara users can manage their pages
  As a mahara user/admin
  I can see/edit/delete one of my page
  I can see/edit/delete a page in my groups
  I can see/edit/delete a page in my institution or site

  Background:
    Given the following "institutions" exist:
      | name | displayname | registerallowed | registerconfirm |
      | instone | Institution One | ON | OFF |
      | insttwo | Institution Two | ON | OFF |
    And the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | admin |
      | userB | Password1 | test02@example.com | Son | Nguyen | instone | internal | admin |
      | userC | Password1 | test03@example.com | Jack | Smith | insttwo | internal | staff |
      | userD | Password1 | test04@example.com | Eden | Wilson | mahara | internal | member |
    And the following "groups" exist:
      | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
      | group 01 | userA | This is group 01 | standard | ON | ON | all | ON | ON | userB, userC | userD |
    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | Page 01 | This is the page 01 | user | userD |
      | Page 02 | This is the page 02 | user | userD |
      | Site Page 01 | This is the page 01 of the site | institution | mahara |
      | Institution Page 01 | This is the page 01 of the Institution One| institution | instone |
      | Group Page 01 | This is the page 01 of the group 01 | group | group 01 |
  Scenario: List my portfolio pages
    Given I log in as "userD" with password "Password1"
    When I go to "view/index.php"
    And I wait "1" seconds
    Then I should see "Page 01"
    And I should see "Page 02"