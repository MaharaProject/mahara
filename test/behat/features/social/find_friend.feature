@javascript @core @core_user @core_portfolio @friends
Feature: Searching for users in the "People" page
    In order to search by name on the "People" page
    So I can befriend them or send them messages

Background:
    Given the following "institutions" exist:
    | name | displayname |
    | instone | Institution One |
    | insttwo | Institution Two |

    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname |role |
    | UserA | Kupuh1pa! | UserA@example.org  | Angela | User | instone | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org  | Bob | User | instone | internal | member |
    | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | insttwo | internal | member |

Scenario: Find users in your institution and outside your institution
    # Log in as a user
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "People" in "Engage" from main menu

    # Searching within my own institution, I should only find users in my institution
    And I select "My institutions" from "Filter"
    And I fill in "Search" with "User"
    And I scroll to the id "main-nav"
    And I click on "Search"
    Then I should see "Bob User"
    And I should not see "Cecilia User"

    # Searching outside my own institution, I should find all users
    When I select "Everyone" from "Filter"
    And I fill in "Search" with "smith"
    And I scroll to the id "main-nav"
    And I click on "Search"
    # I should find everyone
    Then I should see "Cecilia User"
    And I should see "Bob User"
