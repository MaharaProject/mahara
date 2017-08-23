@javascript @core @core_view @core_portfolio
Feature: The 'Share page' link on the "Edit content" screen

In order to be able to see the right view access selections I need to
add a page to a collection.

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
    And the following "pages" exist:
      | title | description | ownertype | ownername |
      | Page UserA_01 | Page 01 | user | UserA |
      | Page UserA_02 | Page 02 | user | UserA |
    And the following "collections" exist:
      | title | description | ownertype | ownername | pages |
      | Collection UserA_01 | Collection 01 | user | UserA | Page UserA_01 |

Scenario: Testing that view access for views in collections are editable properly
    # Checking the right selected options display on view access
    Given I log in as "UserA" with password "Kupuhipa1"
    And I choose "Shared by me" in "Portfolio" from main menu
    Then I should see "Collection UserA_01"
    And I follow "Edit access"
    Then I should see "Collection UserA_01"
    And I should not see "Page UserA_01"
    And I choose "Shared by me" in "Portfolio" from main menu
    And I follow "Pages"
    Then I click on "Edit access" in "Page UserA_02" row
    Then I should see "Page UserA_02"
    And I should not see "Collection UserA_01"
