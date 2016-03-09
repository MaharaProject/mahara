@javascript @core @core_institution
Feature: Mahara users can be a member of an institution
  As a mahara user
  I can be a member of at least one institution

  Background:
    Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

     And the following "institutions" exist:
      | name | displayname | registerallowed | registerconfirm |
      | instone | Institution One | ON | OFF |

  Scenario: Register to an institution
    Given I log in as "userA" with password "Kupuhipa1"
    When I go to "account/institutions.php"
    Then I should see "Request membership of an institution"
