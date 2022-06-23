@javascript @core @core_messages
Feature: Site admin can send messages to anyone regardless of setting "Messages from other people" to "Do not allow anyone to send me messages"

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |
    | instone | Institution One | ON | OFF |

    And the following "users" exist:
    | username | password  | email             | firstname | lastname | institution | authname | role   |
    | UserA    | Kupuh1pa! | UserA@example.org | Angela    | User     | instone      | internal | admin  |
    | UserB    | Kupuh1pa! | UserB@example.org | Bob       | User     | instone      | internal | staff  |
    | UserC    | Kupuh1pa! | UserC@example.org | Carol     | User     | instone      | internal | member |
    | UserD    | Kupuh1pa! | UserD@example.org | Dave      | User     | instone      | internal | member |

    And the following site settings are set:
    | field                | value |
    | isolatedinstitutions | 1     |

    # Person sets profile setting set to "Do not allow anyone to send me messages"
    Given I log in as "UserD" with password "Kupuh1pa!"
    And I choose "Preferences" in "Settings" from account menu
    And I set the following fields to these values:
    | Do not allow anyone to send me messages | 1 |
    And I press "Save"
    Then I should see "Preferences saved"
    And I log out

Scenario: Site admin can send messages to anyone even if
 a person "Does not allow anyone to send me messages"
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "People search" in "People" from administration menu
    When I follow "Dave"
    Then I should see "Send message"
    When I choose "People" in "Engage" from main menu
    Then I should see "Send message" in the "Dave User" row
    And I log out

    # Mahara member with no roles canot send messages to a user
    # who "Does not allow anyone to send me messages"
    Given I log in as "UserC" with password "Kupuh1pa!"
    When I choose "People" in "Engage" from main menu
    #Then I should not see "Send message" in the "Dave User" row
    When I follow "Dave"
    Then I should not see "Send message"
    And I log out
