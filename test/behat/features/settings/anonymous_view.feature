@javascript @core @core_view @core_portfolio
Feature: The Anonymised pages option

In order to be able to see the (Author's name hidden) link

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
    And the following "pages" exist:
      | title | description | ownertype | ownername |
      | Page UserA_01 | Page 01| user | UserA |

Scenario: Testing that views & collections are collated properly
    # Turn on anonymous pages
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to "admin/site/options.php"
    And I follow "Account settings"
    And I set the following fields to these values:
    | Allow anonymous pages | 1 |
    And I press "Update site options"
    And I log out

    # Make page anonymous
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I follow "Page UserA_01"
    And I follow "Edit"
    And I follow "Settings" in the "Toolbar buttons" "Nav" property
    And I set the following fields to these values:
    | Anonymise | 1 |
    And I press "Save"
    And I follow "Share" in the "Toolbar buttons" "Nav" property
    And I select "Registered people" from "accesslist[0][searchtype]"
    And I press "Save"
    And I log out

    Given I log in as "UserB" with password "Kupuh1pa!"
    And I am on homepage
    And I scroll to the id 'bottom-pane'
    Then I should see "(Author's name hidden)"
    And I log out

    Given I log in as "admin" with password "Kupuh1pa!"
    And I am on homepage
    Then should see "(Author's name hidden)"
    And I follow "(Author's name hidden)"
    Then I should see "UserA"
