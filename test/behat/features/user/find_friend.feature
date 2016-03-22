@javascript @core @core_user @core_portfolio @friends
Feature: Searching for users in the "Find friends" page
    In order to search by name on the "Find friends" page
    So I can befriend them or send them messages

Background:
    Given the following "institutions" exist:
    | name | displayname |
    | one | Institution 1 |
    | two | Institution 2 |
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname |role |
    | bob | mahara1 | bob@example.com  | Bob | Smith | one | internal | member |
    | jen | mahara1 | jen@example.com  | Jen | Smith | one | internal | member |
    | aaron | mahara1 | aaron@example.com | Aaron | Smith | two | internal | member |

Scenario: Find users in your institution and outside your institution
    # Log in as a user
    Given I log in as "bob" with password "mahara1"
    And I choose "Find friends" in "Groups"
    And I wait "1" seconds

    # Searching within my own institution, I should only find users in my institution
    And I select "My institutions" from "Filter"
    And I set the field "Search" to "smith"
    And I scroll to the id "main-nav"
    And I press "Search"
    Then I should see "Jen Smith"
    And I should not see "Aaron Smith"

    # Searching outside my own institution, I should find all users
    When I select "Everyone" from "Filter"
    And I set the field "Search" to "smith"
    And I press "search_submit"
    # I should find everyone
    Then I should see "Jen Smith"
    And I should see "Aaron Smith"
