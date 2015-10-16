@javascript @core @core_institution
Feature: Mahara user permissions in institutions
  As a mahara user
  I can be a member of at least one institution
  As an administrator
  I can share institution pages

  Background:
    Given the following "institutions" exist:
      | name | displayname | registerallowed | registerconfirm |
      | instone | Institution One | ON | OFF |
      | insttwo | Institution Two | ON | OFF |

    And the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
      | userB | Kupuhipa2 | test02@example.com | Son | Nguyen | instone | internal | admin |

    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | Inst One page | This is the page | institution | instone |

  Scenario: Register to an institution
    # Member can register to an institution
    Given I log in as "userA" with password "Kupuhipa1"
    When I go to "account/institutions.php"
    Then I should see "Request membership of an institution"
    And I log out

  Scenario: Site admin vs institution admin when sharing institution page
    # Site admin can only share institution page with institution it belongs to
    Given I log in as "admin" with password "Kupuhipa1"
    And I follow "Administration"
    And I choose "Pages" in "Institutions"
    And I follow "Inst One page"
    And I follow "Edit this page"
    And I follow "Share page"
    Then the "accesslist[0][searchtype]" select box should contain "Institution One"
    And the "accesslist[0][searchtype]" select box should not contain "Institution Two"
    And I choose "User search" in "Users"
    And I follow "userB"
    And I press "Add user to institution"
    Then I should see "User added to institution \"Institution Two\"."
    And I log out

    # Institution admin can share institution page with any of the institutions they belong to
    Given I log in as "userB" with password "Kupuhipa2"
    And I follow "Administration"
    And I choose "Pages" in "Institutions"
    And I follow "Inst One page"
    And I follow "Edit this page"
    And I follow "Share page"
    Then the "accesslist[0][searchtype]" select box should contain "Institution One"
    And the "accesslist[0][searchtype]" select box should contain "Institution Two"
