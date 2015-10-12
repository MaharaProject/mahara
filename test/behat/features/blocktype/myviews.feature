@javascript @blocktype @blocktype_myviews
Feature: The "My portfolios" block
In order to be able to see a list of my pages and collections on my dashboard
As as admin
So I have fast access to them

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | P1A | page P1A | user | userA |
      | P1B | page P1B | user | userA |
      | P2 | page P2 | user | userA |
    And the following "collections" exist:
      | title | description| ownertype | ownername | pages |
      | C1 | collection C1 | user | userA | P1A, P1B |

Scenario: Testing that views & collections are collated properly in the "My portfolios" block
    Given I log in as "userA" with password "Kupuhipa1"
    # I should see collections & individual pages
    And I should see "C1" in the "div.list-group-item" "css_element"
    And I should see "(2 pages)" in the "div.list-group-item" "css_element"
    And I should see "page P2" in the "div.bt-myviews" "css_element"
    Then I should not see "page P1A" in the "div.bt-myviews" "css_element"
