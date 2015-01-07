@javascript @core @institution
Feature: Mahara users can be a member of an institution
  As a mahara user
  I can be a member of at least one institution

  Background:
    Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
      | userB | Password1 | test02@example.com | Son | Nguyen | mahara | internal | member |
      | userC | Password1 | test03@example.com | Jack | Smith | mahara | internal | member |
      | userD | Password1 | test04@example.com | Eden | Wilson | mahara | internal | member |
    And the following "institutions" exist:
      | name | displayname | registerallowed | registerconfirm |
      | instone | Institution One | ON | OFF |
      | insttwo | Institution Two | ON | OFF |
  Scenario: Register to an institution
    Given I log in as "userB" with password "Password1"
    When I go to "account/institutions.php"
    Then I should see "Request membership of an institution"