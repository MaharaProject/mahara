@javascript @core @core_portfolio
 Feature: Adding more links to the jump list of the paginator on "Shared with me" page
  In order to be able to see and follow hyperlinks to numbered page lists across paginator in regular intervals
  As a student
  So I can navigate more efficiently through a large amount of pages.

 Scenario: Checking the jump list of the paginagtor (Bug 1409370)
  Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
     | userB | Kupuhipa1 | test02@example.com | Simon | Mc | mahara | internal | member |
  And I log in as "userA" with password "Kupuhipa1"
  And the following "pages" exist:
     | title | description| ownertype | ownername |
     | A's Page 01 | UserA's page 01 | user | userA |
     | A's Page 02 | UserA's page 02 | user | userA |
     | A's Page 03 | UserA's page 03 | user | userA |
     | A's Page 04 | UserB's page 04 | user | userA |
     | A's Page 05 | UserA's page 05 | user | userA |
     | A's Page 06 | UserA's page 06 | user | userA |
     | A's Page 07 | UserA's page 07 | user | userA |
     | A's Page 08 | UserA's page 08 | user | userA |
     | A's Page 09 | UserA's page 06 | user | userA |
     | A's Page 10 | UserA's page 07 | user | userA |
     | A's Page 11 | UserA's page 08 | user | userA |
  And I follow "Portfolio"
  And I follow "A's Page 01"
  And I follow "Edit this page"
  And I follow "Share page"
  And I follow "Select all"
  And I press "Public"
  And I press "editaccess_submit"
  And I follow "Logout"
  And I log in as "userB" with password "Kupuhipa1"
  And I choose "Shared with me" in "Portfolio"
  And I click on "Advanced options"
  And I click on "Select all"
  And I select "title" from "sort"
  And I press "search_search"
  And I should see "Maximum items per page:"
  And I select "1" from "limit"
  And I wait until the page is ready
  And I should see "1"
  And I should see "2"
  And I should see "3"
  And I follow "3"
  And I wait "1" seconds
  And I should see "4"
  And I should see "5"
