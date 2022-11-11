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
    And the following "blocks" exist:
     | title                     | type     | page                   | retractable | updateonly | data                                                |
     | Portfolios shared with me | newviews | Dashboard page: admin  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |
     | Portfolios shared with me | newviews | Dashboard page: UserA  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |
     | Portfolios shared with me | newviews | Dashboard page: UserB  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |

Scenario: Testing that views & collections are collated properly
    # Turn on anonymous pages
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to "admin/site/options.php"
    And I click on "Account settings"
    And I set the following fields to these values:
    | Allow anonymous pages | 1 |
    And I click on "Update site options"
    And I log out

    # Make page anonymous
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Page UserA_01"
    And I click on "Edit"
    And I click on "Configure" in the "Toolbar buttons" "Nav" property
    And I set the following fields to these values:
    | Anonymise | 1 |
    And I click on "Save"
    And I click on "Share" in the "Toolbar buttons" "Nav" property
    And I select "Registered people" from "accesslist[0][searchtype]"
    And I click on "Save"
    And I log out

    Given I log in as "UserB" with password "Kupuh1pa!"
    And I am on homepage
    And I scroll to the id 'bottom-pane'
    Then I should see "(Author's name hidden)"
    And I log out

    Given I log in as "admin" with password "Kupuh1pa!"
    And I am on homepage
    Then should see "(Author's name hidden)"
    And I click on "(Author's name hidden)"
    Then I should see "UserA"
