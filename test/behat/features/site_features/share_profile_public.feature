@javascript @core
Feature: Profile page can be shared publicly
As a user
I want to sahre my Portfolio pages on my public profile page
So that visitors can quickly browse to see them

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
    | title | description| ownertype | ownername |
    | Page UserA_01 | Page 01 | user | UserA |
    | Page UserA_02 | Page 01 | user | UserA |
    | Page UserA_03 | Page 01 | user | UserA |
    | Page UserA_04 | Page 01 | user | UserA |
    | Page UserA_05 | Page 01 | user | UserA |
    | Page UserA_06 | Page 01 | user | UserA |
    | Page UserA_07 | Page 01 | user | UserA |
    | Page UserA_08 | Page 01 | user | UserA |
    | Page UserA_09 | Page 01 | user | UserA |
    | Page UserA_10 | Page 01 | user | UserA |
    | Page UserA_11 | Page 01 | user | UserA |
    | Page UserA_12 | Page 01 | user | UserA |

Scenario: User checks pagination on profile page for shared portfolio pages
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Shared by me" in "Share" from main menu
    # Edit sharing permissions for Page UserA_01
    And I click on "Share" in "Page UserA_01" row
    And I set the select2 value "Page UserA_01, Page UserA_02, Page UserA_03, Page UserA_04, Page UserA_05, Page UserA_06, Page UserA_07, Page UserA_08, Page UserA_09, Page UserA_10, Page UserA_11, Page UserA_12" for "editaccess_views"
    And I select "Public" from "accesslist[0][searchtype]"
    And I click on "Save"
    And I click on "Share" in "Profile page" row
    And I select "Public" from "accesslist[1][searchtype]"
    And I click on "Save"
    And I log out

    # Go to UserA's prrofile page when logged out
    When I go to the profile page of "UserA"
    Then I should see "Page UserA_01"
    And I should not see "Page UserA_12"
    When I jump to page "2" of the list "myviews_pagination"
    Then I should see "Page UserA_11"
    And I should not see "Page UserA_07"
