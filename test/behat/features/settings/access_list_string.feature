@javascript @core @core_administration

Feature: User reports have been moved to Reports section
    In order to make sure admin can still access reports
    As an admin
    I need to check they correct report is shown

Background:
    Given the following "institutions" exist:
     | name | displayname | registerallowed | registerconfirm |
     | instone | Institution One | ON | OFF |
     | insttwo | Institution Two | ON | OFF |

    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | instone | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org | Bob | User | instone | internal | member |

Scenario: Verifying user's Authentication Method only displays Institutions they are associated with
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "User search" in "People" from administration menu
    When I follow "Angela"
    And I follow "Account settings"
    Then I should see "Authentication method"
    And the "Authentication method" select box should contain "Institution One: internal"
    And the "Authentication method" select box should contain "No Institution: Internal"
    And the "Authentication method" select box should not contain "Institution Two: internal"

Scenario: Accessing user reports
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "User search" in "People" from administration menu
    And I check "selectusers_2"
    And I check "selectusers_3"
    And I press "Get reports"
    Then I should see "User details"
    And I should see "2 users selected"
