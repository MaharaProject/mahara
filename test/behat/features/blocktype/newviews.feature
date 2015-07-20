@javascript @core @blocktype @blocktype_newviews
Feature: Looking at the "Latest Pages" (newviews) block on my dashboard
    In order to see new pages across the site
    So I can know what people are up to

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
     | userB | Password1 | test02@example.com | Son | Nguyen | mahara | internal | member |
    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | Shared page | This is a page that is shared with me| user | userA |
      | Private page | This is a page shared with no one | user | userA |
      | C1P1 | Page 1 in Collection 1 | user | userA |
      | C1P2 | Page 2 in Collection 1 | user | userA |
      | C2P1 | Page 1 in Collection 2 | user | userA |
      | C2P2 | Page 2 in Collection 2 | user | userA |
    And the following "collections" exist:
      | title | description| ownertype | ownername | pages |
      | Shared Collection 01 | This is the collection 01 | user | userA | C1P1, C1P2 |
      | Private Collection 02 | This is the collection 02 | user | userA | C2P1, C2P2 |
    And the following "permissions" exist:
      | title | accesstype |
      | Shared page | public |
      | Shared Collection 01 | public |

Scenario: Share pages and collections to a group.
The list of shared pages must take into account of access date (Bug 1374163)
    And I log in as "userB" with password "Password1"
    Then I should see "Shared page" in the "div.bt-newviews" "css_element"
    And I should see "Shared Collection 01" in the "div.bt-newviews" "css_element"
    # I shouldn't see the pages I didn't share
    And I should not see "Private page" in the "div.bt-newviews" "css_element"
    And I should not see "Private Collection 02" in the "div.bt-newviews" "css_element"
    # I shouldn't see the individual pages in a collection
    And I should not see "C1P1" in the "div.bt-newviews" "css_element"
