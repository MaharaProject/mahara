@javascript @core @group
Feature: Private pages are kept confidential to non-members
  As a mahara user in a private group
  I need to make sure those who are not members cannot access group items such as pages/files, etc.

Background:
    Given the following "institutions" exist:
    | name           | displayname                | registerallowed |
    | berryberrynice | berryberrynice institution | OFF             |

    And the following "users" exist:
    | username | password  | email             | firstname | lastname | institution    | authname | role   |
    | UserA    | Kupuh1pa! | UserA@example.org | Angela    | User     | berryberrynice | internal | admin  |
    | UserB    | Kupuh1pa! | UserB@example.org | Bob       | User     | mahara         | internal | member |
    | UserC    | Kupuh1pa! | UserC@example.org | Cecilia   | User     | mahara         | internal | admin  |

    And the following "groups" exist:
    | name          | owner | description   | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members        | public |staff |
    | Private group | UserB | UserB's group | standard  | ON   | ON            | all       | ON            | ON            | UserA, UserB   | OFF    |UserA |

    And the following "pages" exist:
    | title        | description   | ownertype  | ownername      |
    | Group page   | Group page    | group      | Private group  |
    | Institution page | Instit. page  | institution| berryberrynice |
    | Site page    | Site page     | institution| mahara         |

Scenario: Verify that those who are not members of Private group cannot see its pages
    Given I go to portfolio page "Group page"
    And I should see "Log in to Mahara"
    When I set the following fields to these values:
    | Username | UserB |
    | Password | Kupuh1pa! |
    When I press "Login"
    Then I should see "Group page"

Scenario: Verify that those without shared access to an institution's pages cannot see them
    Given I go to portfolio page "Institution page"
    Then I should see "Log in to Mahara"
    When I set the following fields to these values:
    | Username | UserA |
    | Password | Kupuh1pa! |
    When I press "Login"
    Then I should see "Institution page"

Scenario: Verify that those without access cannot see site portfolios
    Given I go to portfolio page "Site page"
    Then I should see "Log in to Mahara"
    When I set the following fields to these values:
    | Username | UserC |
    | Password | Kupuh1pa! |
    When I press "Login"
    Then I should see "Site page"