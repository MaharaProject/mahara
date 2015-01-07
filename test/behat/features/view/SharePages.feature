@javascript @core @page @share
Feature: Mahara users can share their pages
  As a mahara user/admin
  I can share my pages to others and see shared pages from others

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
      | D's Page 01 | UserD's page 01 | user | userD |
      | D's Page 02 | UserD's page 02 | user | userD |
      | A's Page 01 | UserA's page 01 | user | userA |
      | B's Page 02 | UserB's page 02 | user | userB |
  Scenario: Share pages
    Given I log in as "userD" with password "Password1"
    When I go to "view/share.php"
    Then I should see "D's Page 01"
    And I should see "D's Page 02"
    And I click on "Edit access" in "D's Page 01" row
    And I should see "Edit access"