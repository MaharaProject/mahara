@javascript @core
Feature: Go to People page
    As a user
    I want to see a list of my contacts and pending requests and their portfolio if they exist

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

Scenario: Create contacts and portfolios for each to see portfolios on People page
    # Log in as a UserA and create portfolio and set sharing access to contacts
    Given I log in as "UserA" with password "Kupuh1pa!"
    # PCNZ customisation: you can only share your portfolio with one reviewer, no friends.
    # When I choose "Pages and collections" in "Create" from main menu
    # And I click on "Manage access" in "CollectionA" card access menu
    # And I select "friends" from "General" in shared with select2 box
    # And I press "Save"

    # PCNZ customisation: you can only share your portfolio with one reviewer, no friends.
    # Then I choose "Pages and collections" in "Create" from main menu
    # And I click on "Manage access" in "PageA single" card access menu
    # And I select "friends" from "General" in shared with select2 box
    # And I press "Save"

    # Let UserA add UserB as a contact
    Then I choose "People" in "Engage" from main menu
    And I click on "Send contact request" in "Benjamin Bobby" row
    And I set the field "Message" to "Love me, love me, say you do!"
    And I press "Make a contact request"
    And I log out

    # Log in as UserB, accept UserA's contact request
    Then I log in as "UserB" with password "Kupuh1pa!"
    And I follow "pending contact"
    And I click on "Approve"

    # Change sharing permissions
    When I choose "Pages and collections" in "Create" from main menu
    And I click on "Manage access" in "CollectionB" card access menu
    And I select "friends" from "General" in shared with select2 box
    And I press "Save"

    Then I choose "Pages and collections" in "Create" from main menu
    And I click on "Manage access" in "PageB single" card access menu
    And I select "friends" from "General" in shared with select2 box
    Then I press "Save"

    # UserB should now be able to see UserA's Portfolio on the People page
    When I choose "People" in "Engage" from main menu
    And I select "Contacts" from "filter"
    Then I should see "CollectionA, PageA single"
    Then I log out

    # Log in as UserA (who is now a contact with UserB), to see UserB's Portfolio on the People page
    Given I log in as "UserA" with password "Kupuh1pa!"
    And  I choose "People" in "Engage" from main menu
    And I select "Contacts" from "filter"
    And I press "Search"
    Then I should see "CollectionB, PageB single"
