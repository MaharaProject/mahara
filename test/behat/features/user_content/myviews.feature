@javascript @core @blocktype @blocktype_myviews
Feature: The "My portfolios" block
In order to be able to see a list of my pages and collections on my dashboard
As as admin
So I have fast access to them

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
    And the following "pages" exist:
      | title | description | ownertype | ownername |
      | Page UserA_01 | page P1A | user | UserA |
      | Page UserA_02 | page P1B | user | UserA |
      | Page UserA_03 | page P2 | user | UserA |
    And the following "collections" exist:
      | title | description | ownertype | ownername | pages |
      | Collection UserA_01 | Collection 01 | user | UserA | Page UserA_01, Page UserA_02 |

Scenario: Testing that views & collections are collated properly in the "My portfolios" block
    Given I log in as "UserA" with password "Kupuh1pa!"
    # I should see collections & individual pages
    And I scroll to the id "column-container"
    And I should see "Collection UserA_01" in the "My portfolios" "Blocks" property
    And I should see "(2 pages)" in the "My portfolios" "Blocks" property
    And I should see "Page UserA_03" in the "My portfolios" "Blocks" property
    Then I should not see "Page UserA_01" in the "My portfolios" "Blocks" property
