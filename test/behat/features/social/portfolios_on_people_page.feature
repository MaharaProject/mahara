@javascript @core
Feature: Go to People page
    As a user
    I want to see a list of my friends and pending requests and their portfolio if they exist

Background:
    Given the following "users" exist:
    | username | password | email             | firstname | lastname | institution | authname | role  |
    | UserA    | Kupuh1pa!| userA@example.org | Anglela   | Ava      | mahara      | internal | member|
    | UserB    | Kupuh1pa!| userB@example.org | Benjamin  | Bobby    | mahara      | internal | member|

    And the following "pages" exist:
    | title        | description   | ownertype | ownername |
    | PageA        | Page A desc   | user      | UserA     |
    | PageB        | Page B desc   | user      | UserB     |
    | PageA single | Page A s desc | user      | UserA     |
    | PageB single | Page B s desc | user      | UserB     |


    And the following "collections" exist:
    | title       | description            | ownertype | ownername | pages |
    | CollectionA | Angela's Collection    | user      | UserA      |PageA |
    | CollectionB | Benjamin's Collections | user      | UserB      |PageB |

Scenario: Create friends and portfolios for each to see portfolios on People page
    # Log in as a UserA and create portfolio and set sharing access to friends
    Given I log in as "UserA" with password "Kupuh1pa!"
    When I choose "Portfolios" in "Create" from main menu
    And I click on "Manage sharing" in "CollectionA" card access menu
    And I select "friends" from "General" in shared with select2 box
    And I click on "Save"

    Then I choose "Portfolios" in "Create" from main menu
    And I click on "Manage sharing" in "PageA single" card access menu
    And I select "friends" from "General" in shared with select2 box
    And I click on "Save"

    # Let UserA add UserB as a friend
    Then I choose "People" in "Engage" from main menu
    And I click on "Send friend request" in "Benjamin Bobby" row
    And I set the field "Message" to "Love me, love me, say you do!"
    And I click on "Request friendship"
    And I log out

    # Log in as UserB, accept UserA's friend request
    Then I log in as "UserB" with password "Kupuh1pa!"
    And I click on "pending friend"
    And I click on "Approve"

    # Change sharing permissions
    When I choose "Portfolios" in "Create" from main menu
    And I click on "Manage sharing" in "CollectionB" card access menu
    And I select "friends" from "General" in shared with select2 box
    And I click on "Save"

    Then I choose "Portfolios" in "Create" from main menu
    And I click on "Manage sharing" in "PageB single" card access menu
    And I select "friends" from "General" in shared with select2 box
    Then I click on "Save"

    # UserB should now be able to see UserA's Portfolio on the People page
    When I choose "People" in "Engage" from main menu
    And I select "Friends" from "filter"
    Then I should see "CollectionA, PageA single"
    Then I log out

    # Log in as UserA (who is now friends with UserB), to see UserB's Portfolio on the People page
    Given I log in as "UserA" with password "Kupuh1pa!"
    And  I choose "People" in "Engage" from main menu
    And I select "Friends" from "filter"
    And I click on "Search"
    Then I should see "CollectionB, PageB single"
