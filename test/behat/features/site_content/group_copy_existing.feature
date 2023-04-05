@javascript @core @core_group
Feature: Group admin can push Group portfolio pages and collections to existing Group members

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |
    | instone | Institution One | ON | OFF |
    | insttwo | Institution Two | ON | OFF |

    And the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | instone | internal | staff |
    | UserB | Kupuh1pa! | UserB@example.org | Bob | User | instone | internal | member |
    | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | instone | internal | member |

    And the following "groups" exist:
    | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
    | GroupA | UserA | GroupA owned by UserA | standard | ON | OFF | all | ON | OFF | UserB, UserA | UserA |
    | GroupC | UserC | GroupC owned by UserC | standard | ON | OFF | all | OFF | OFF | UserC | UserC |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page GroupA_01 | Group page 01 | group | GroupA |
    | Page GroupA_02 | Group page 02 | group | GroupA |
    | Page GroupA_03 | Group page 03 | group | GroupA |

    And the following "collections" exist:
    | title | description | ownertype | ownername | pages |
    | Collection GroupA_01 | Collection 01 | group | GroupA | Page GroupA_02, Page GroupA_03 |

Scenario: Group admin pushes a Group page and collection to existing group members (Bug 1763163)
    # Log in as GroupA admin (UserA)
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Verifying log in was successful
    And I should see "Angela"
    # Browse to the Share > Advanced options
    When I choose "Groups" in "Engage" from main menu
    And I click on "GroupA"
    And I click on "Share" in the "Navigation" "Groups" property
    And I click on "Share" in "Collection GroupA_01" row
    And I click on "Advanced options"
    # verify field lalel is displayed on page
    Then I should see "Copy for existing group members"
    # enable the "Copy for existing group members" toggle
    And I enable the switch "Copy for existing group members"
    And I click on "Save"
    And I click on "Pages" in the "Share tabs" "Misc" property
    And I click on "Share" in "Page GroupA_01" row
    And I click on "Advanced options"
    # verify field lalel is displayed on page
    Then I should see "Copy for existing group members"
    # enable the "Copy for existing group members" toggle
    And I enable the switch "Copy for existing group members"
    And I click on "Save"
    And I log out
    # Group members (UserB) should have their Portfolios updated
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    # verify a group member has their Portfolio updated with the Group pages
    Then I should see "Page GroupA_01"
    And I should see "Collection GroupA_01"
    And I log out
    # Non group members (UserC) should not have their Protfolios updated
    Given I log in as "UserC" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    # verify a group member has their Pportfolio updated with the Group pages
    Then I should not see "Page GroupA_01"
    And I should not see "Collection GroupA_01"
