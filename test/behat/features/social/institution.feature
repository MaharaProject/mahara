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
      | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
      | UserB | Kupuh1pa! | UserB@example.org | Bob | User | instone | internal | admin |
      | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | mahara | internal | member |

    And the following "pages" exist:
      | title | description | ownertype | ownername |
      | Page InstOne_01 | Page | institution | instone |

  Scenario: Register to an institution
    # Member can register to an institution
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Institution membership" in "Settings" from user menu
    Then I should see "Request membership of an institution"
    And I log out

  Scenario: Site admin vs institution admin when sharing institution page
    # Site admin can only share institution page with institution it belongs to
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Institutions" from administration menu
    And I follow "Page InstOne_01"
    And I follow "Edit"
    And I follow "Share" in the "Toolbar buttons" property
    Then the "accesslist[0][searchtype]" select box should contain "Institution One"
    And the "accesslist[0][searchtype]" select box should not contain "Institution Two"
    And I choose "User search" in "People" from administration menu
    And I follow "UserB"
    And I press "Add user to institution"
    Then I should see "User added to institution \"Institution Two\"."
    And I log out

    # Institution admin can share institution page with any of the institutions they belong to
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Institutions" from administration menu
    And I follow "Page InstOne_01"
    And I follow "Edit"
    And I follow "Share" in the "Toolbar buttons" property
    Then the "accesslist[0][searchtype]" select box should contain "Institution One"
    And the "accesslist[0][searchtype]" select box should contain "Institution Two"
    And I log out

    # Add new member to institution via Institution -> Member's page
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Members" in "Institutions" from administration menu
    And I select "People who have not requested institution membership yet" from "Users to display:"
    And I select "UserC" from "Non-members"
    And I press "Turn selected non-members into invited"
    And I press "Add members"
    And I should see "Users added"
