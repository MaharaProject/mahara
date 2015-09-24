@javascript @core @core_view @core_portfolio
Feature: The Anonymised pages option

In order to be able to see the (Author's name hidden) link

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
     | userB | Password1 | test02@example.com | Son | Nguyen | mahara | internal | member |
    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | Page 1 | page P1 | user | userA |

Scenario: Testing that views & collections are collated properly
    # Turn on anonymous pages
    Given I log in as "admin" with password "Password1"
    And I go to "admin/site/options.php"
    And I follow "General settings"
    And I set the following fields to these values:
    | Allow anonymous pages | 1 |
    And I press "Update site options"
    And I log out

    # Make page anonymous
    Given I log in as "userA" with password "Password1"
    And I choose "Pages" in "Portfolio"
    And I follow "Page 1"
    And I follow "Edit this page"
    And I follow "Edit title and description"
    And I set the following fields to these values:
    | Anonymise | 1 |
    And I press "Save"
    And I follow "Share page"
    And I select "Registered users" from "accesslist[0][searchtype]"
    And I press "Save"
    And I log out

    Given I log in as "userB" with password "Password1"
    And I am on homepage
    Then I should see "(Author's name hidden)"
    And I log out

    Given I log in as "admin" with password "Password1"
    And I am on homepage
    Then should see "(Author's name hidden)"
    And I follow "(Author's name hidden)"
    Then I should see "userA"
