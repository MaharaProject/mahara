@javascript @core @core_view @core_portfolio
Feature: Displaying more pages.

In order to be able to display more pages and collections

As a user
So I can view only a limited amount of pages at one time and display
more via the 'Show more' button.

Background:

  Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
     | userB | Kupuhipa1 | test02@example.com | Simon | Mc | mahara | internal | member |

  And the following "pages" exist:
     | title | description| ownertype | ownername |
     | A's Page 01 | This is my page 01 | user | userA |
     | A's Page 02 | This is my page 02 | user | userA |
     | A's Page 03 | This is my page 03 | user | userA |
     | A's Page 04 | This is my page 04 | user | userA |
     | A's Page 05 | This is my page 05 | user | userA |
     | A's Page 06 | This is my page 06 | user | userA |
     | A's Page 07 | This is my page 07 | user | userA |
     | A's Page 08 | This is my page 08 | user | userA |
     | A's Page 09 | This is my page 09 | user | userA |
     | A's Page 10 | This is my page 10 | user | userA |
     | A's Page 11 | This is my page 11 | user | userA |
     | A's Page 12 | This is my page 12 | user | userA |
     | A's Page 13 | This is my page 13 | user | userA |
     | A's Page 14 | This is my page 14 | user | userA |
     | A's Page 15 | This is my page 15 | user | userA |
     | A's Page 16 | This is my page 16 | user | userA |
     | A's Page 17 | This is my page 17 | user | userA |
     | A's Page 18 | This is my page 18 | user | userA |
     | A's Page 19 | This is my page 19 | user | userA |
     | A's Page 20 | This is my page 20 | user | userA |
     | A's Page 21 | This is my page 21 | user | userA |
     | A's Page 22 | This is my page 22 | user | userA |
     | A's Page 23 | This is my page 23 | user | userA |
     | A's Page 24 | This is my page 24 | user | userA |
     | A's Page 25 | This is my page 25 | user | userA |
     | A's Page 26 | This is my page 26 | user | userA |
     | A's Page 27 | This is my page 27 | user | userA |
     | A's Page 28 | This is my page 28 | user | userA |
     | A's Page 29 | This is my page 29 | user | userA |
     | A's Page 30 | This is my page 30 | user | userA |
     | A's Page 31 | This is my page 31 | user | userA |
     | A's Page 32 | This is my page 32 | user | userA |
     | A's Page 33 | This is my page 33 | user | userA |
     | A's Page 34 | This is my page 34 | user | userA |
     | A's Page 35 | This is my page 35 | user | userA |
     | A's Page 36 | This is my page 36 | user | userA |
     | A's Page 37 | This is my page 37 | user | userA |
     | A's Page 38 | This is my page 38 | user | userA |
     | A's Page 39 | This is my page 39 | user | userA |
     | A's Page 40 | This is my page 40 | user | userA |
     | A's Page 41 | This is my page 41 | user | userA |
     | A's Page 42 | This is my page 42 | user | userA |
     | A's Page 43 | This is my page 43 | user | userA |
     | A's Page 44 | This is my page 44 | user | userA |
     | A's Page 45 | This is my page 45 | user | userA |
     | A's Page 46 | This is my page 46 | user | userA |
     | A's Page 47 | This is my page 47 | user | userA |
     | A's Page 48 | This is my page 48 | user | userA |
     | A's Page 49 | This is my page 49 | user | userA |
     | A's Page 50 | This is my page 50 | user | userA |
     | A's Page 51 | This is my page 51 | user | userA |

Scenario: Making sure that the max items per page drop down limits to correct amount of pages (Bug 1409369)
  Given I log in as "userA" with password "Kupuhipa1"
  And I choose "Pages and collections" in "Portfolio" from main menu
  And I select "atoz" from "orderby"
  And I scroll to the base of id "searchviews_submit"
  And I press "searchviews_submit"
  And I wait "2" seconds
  And I click on "A's Page 01" panel menu
  And I click on "Edit" in "A's Page 01" panel menu
  And I follow "Share page"
  And I set the select2 value "A's Page 01, A's Page 02, A's Page 03, A's Page 04, A's Page 05, A's Page 06, A's Page 07, A's Page 08, A's Page 09, A's Page 10, A's Page 11, A's Page 12, A's Page 13, A's Page 14, A's Page 15, A's Page 16, A's Page 17, A's Page 18, A's Page 19, A's Page 20, A's Page 21, A's Page 22, A's Page 23, A's Page 24, A's Page 25, A's Page 26, A's Page 27, A's Page 28, A's Page 29, A's Page 30, A's Page 31, A's Page 32, A's Page 33, A's Page 34, A's Page 35, A's Page 36, A's Page 37, A's Page 38, A's Page 39, A's Page 40, A's Page 41, A's Page 42, A's Page 43, A's Page 44, A's Page 45, A's Page 46, A's Page 47, A's Page 48, A's Page 49, A's Page 50, A's Page 51" for "editaccess_views"
  And I select "Public" from "accesslist[0][searchtype]"
  And I press "Save"
  And I log out
  And I log in as "userB" with password "Kupuhipa1"
  And I choose "Shared with me" in "Portfolio" from main menu
  And I check "Registered users"
  And I check "Public"
  And I select "title" from "sort"
  And I press "search_submit"
  Then I should see "Results per page:"
  And I select "1" from "limit"
  And I wait until the page is ready
  And I should see "A's Page 01"
  And I should not see "A's Page 02"
  And I select "10" from "limit"
  And I wait until the page is ready
  And I should see "A's Page 10"
  And I should not see "A's Page 11"
  And I select "20" from "limit"
  And I wait until the page is ready
  And I should see "A's Page 20"
  And I should not see "A's Page 21"
  And I select "50" from "limit"
  And I wait until the page is ready
  And I should see "A's Page 50"
  And I should not see "A's Page 51"
