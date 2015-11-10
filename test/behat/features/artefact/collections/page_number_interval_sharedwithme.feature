@javascript @core_artefact @core @core_portfolio
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
     | A's Page 04 | UserA's page 04 | user | userA |
     | A's Page 05 | UserA's page 05 | user | userA |
     | A's Page 06 | UserA's page 06 | user | userA |
     | A's Page 07 | UserA's page 07 | user | userA |
     | A's Page 08 | UserA's page 08 | user | userA |
     | A's Page 09 | UserA's page 09 | user | userA |
     | A's Page 10 | UserA's page 10 | user | userA |
     | A's Page 11 | UserA's page 11 | user | userA |
     | A's Page 12 | UserA's page 12 | user | userA |
     | A's Page 13 | UserA's page 13 | user | userA |
     | A's Page 14 | UserA's page 14 | user | userA |
     | A's Page 15 | UserA's page 15 | user | userA |
     | A's Page 16 | UserA's page 16 | user | userA |
     | A's Page 17 | UserA's page 17 | user | userA |
     | A's Page 18 | UserA's page 18 | user | userA |
     | A's Page 19 | UserA's page 19 | user | userA |
     | A's Page 20 | UserA's page 20 | user | userA |
     | A's Page 21 | UserA's page 21 | user | userA |
     | A's Page 22 | UserA's page 22 | user | userA |
  And I follow "Portfolio"
  And I follow "A's Page 01"
  And I follow "Edit this page"
  And I follow "Share page"
  And I set the select2 value "A's Page 01, A's Page 02, A's Page 03, A's Page 04, A's Page 05, A's Page 06, A's Page 07, A's Page 08, A's Page 09, A's Page 10, A's Page 11, A's Page 12, A's Page 13, A's Page 14, A's Page 15, A's Page 16, A's Page 17, A's Page 18, A's Page 19, A's Page 20, A's Page 21" for "editaccess_views"
  And I select "Public" from "accesslist[0][searchtype]"
  And I press "editaccess_submit"
  And I follow "Logout"
  And I log in as "userB" with password "Kupuhipa1"
  And I choose "Shared with me" in "Portfolio"
  And I click on "Advanced options"
  And I click on "Select all"
  And I select "title" from "sort"
  And I press "search_submit"
  And I should see "Results per page:"
  And I select "1" from "limit"
  And I wait until the page is ready
  And I should see "1"
  And I should see "2"
  And I should see "3"
  And I follow "3"
  And I wait "1" seconds
  And I should see "4"
  And I should see "5"
