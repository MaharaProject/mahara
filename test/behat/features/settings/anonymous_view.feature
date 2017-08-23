@javascript @core @core_view @core_portfolio
Feature: The Anonymised pages option

In order to be able to see the (Author's name hidden) link

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuhipa1 | UserB@example.org | Bob | User | mahara | internal | member |
    And the following "pages" exist:
      | title | description | ownertype | ownername |
      | Page UserA_01 | Page 01| user | UserA |

Scenario: Testing that views & collections are collated properly
    # Turn on anonymous pages
    Given I log in as "admin" with password "Kupuhipa1"
    And I go to "admin/site/options.php"
    And I follow "General settings"
    And I set the following fields to these values:
    | Allow anonymous pages | 1 |
    And I press "Update site options"
    And I log out

    # Make page anonymous
    Given I log in as "UserA" with password "Kupuhipa1"
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I follow "Page UserA_01"
    And I follow "Edit this page"
    And I follow "Settings" in the "#toolbar-buttons" "css_element"
    And I set the following fields to these values:
    | Anonymise | 1 |
    And I press "Save"
    And I follow "Share" in the "#toolbar-buttons" "css_element"
    And I select "Registered users" from "accesslist[0][searchtype]"
    And I press "Save"
    And I log out

    Given I log in as "UserB" with password "Kupuhipa1"
    And I am on homepage
    Then I should see "(Author's name hidden)"
    And I log out

    Given I log in as "admin" with password "Kupuhipa1"
    And I am on homepage
    Then should see "(Author's name hidden)"
    And I follow "(Author's name hidden)"
    Then I should see "UserA"
