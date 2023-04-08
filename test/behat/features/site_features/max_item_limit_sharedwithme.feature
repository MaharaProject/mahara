@javascript @core @core_view @core_portfolio
Feature: Displaying more pages.

In order to be able to display more pages and collections

As a user
So I can view only a limited amount of pages at one time and display
more via the 'Show more' button.

Background:

  Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |

  And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01 | user | UserA |
     | Page UserA_02 | Page 02 | user | UserA |
     | Page UserA_03 | Page 03 | user | UserA |
     | Page UserA_04 | Page 04 | user | UserA |
     | Page UserA_05 | Page 05 | user | UserA |
     | Page UserA_06 | Page 06 | user | UserA |
     | Page UserA_07 | Page 07 | user | UserA |
     | Page UserA_08 | Page 08 | user | UserA |
     | Page UserA_09 | Page 09 | user | UserA |
     | Page UserA_10 | Page 10 | user | UserA |
     | Page UserA_11 | Page 11 | user | UserA |
     | Page UserA_12 | Page 12 | user | UserA |
     | Page UserA_13 | Page 13 | user | UserA |
     | Page UserA_14 | Page 14 | user | UserA |
     | Page UserA_15 | Page 15 | user | UserA |
     | Page UserA_16 | Page 16 | user | UserA |
     | Page UserA_17 | Page 17 | user | UserA |
     | Page UserA_18 | Page 18 | user | UserA |
     | Page UserA_19 | Page 19 | user | UserA |
     | Page UserA_20 | Page 20 | user | UserA |
     | Page UserA_21 | Page 21 | user | UserA |
     | Page UserA_22 | Page 22 | user | UserA |
     | Page UserA_23 | Page 23 | user | UserA |
     | Page UserA_24 | Page 24 | user | UserA |
     | Page UserA_25 | Page 25 | user | UserA |
     | Page UserA_26 | Page 26 | user | UserA |
     | Page UserA_27 | Page 27 | user | UserA |
     | Page UserA_28 | Page 28 | user | UserA |
     | Page UserA_29 | Page 29 | user | UserA |
     | Page UserA_30 | Page 30 | user | UserA |
     | Page UserA_31 | Page 31 | user | UserA |
     | Page UserA_32 | Page 32 | user | UserA |
     | Page UserA_33 | Page 33 | user | UserA |
     | Page UserA_34 | Page 34 | user | UserA |
     | Page UserA_35 | Page 35 | user | UserA |
     | Page UserA_36 | Page 36 | user | UserA |
     | Page UserA_37 | Page 37 | user | UserA |
     | Page UserA_38 | Page 38 | user | UserA |
     | Page UserA_39 | Page 39 | user | UserA |
     | Page UserA_40 | Page 40 | user | UserA |
     | Page UserA_41 | Page 41 | user | UserA |
     | Page UserA_42 | Page 42 | user | UserA |
     | Page UserA_43 | Page 43 | user | UserA |
     | Page UserA_44 | Page 44 | user | UserA |
     | Page UserA_45 | Page 45 | user | UserA |
     | Page UserA_46 | Page 46 | user | UserA |
     | Page UserA_47 | Page 47 | user | UserA |
     | Page UserA_48 | Page 48 | user | UserA |
     | Page UserA_49 | Page 49 | user | UserA |
     | Page UserA_50 | Page 50 | user | UserA |
     | Page UserA_51 | Page 51 | user | UserA |

Scenario: Making sure that the max items per page drop down limits to correct amount of pages (Bug 1409369)
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I choose "Portfolios" in "Create" from main menu
  And I select "atoz" from "orderby"
  And I click on "Search" in the "#searchviews_submit_container" "css_element"
  And I choose "Shared by me" in "Share" from main menu
  And I click on "Share" in "Page UserA_01" row
  And I set the select2 value "Page UserA_01, Page UserA_02, Page UserA_03, Page UserA_04, Page UserA_05, Page UserA_06, Page UserA_07, Page UserA_08, Page UserA_09, Page UserA_10, Page UserA_11, Page UserA_12, Page UserA_13, Page UserA_14, Page UserA_15, Page UserA_16, Page UserA_17, Page UserA_18, Page UserA_19, Page UserA_20, Page UserA_21, Page UserA_22, Page UserA_23, Page UserA_24, Page UserA_25, Page UserA_26, Page UserA_27, Page UserA_28, Page UserA_29, Page UserA_30, Page UserA_31, Page UserA_32, Page UserA_33, Page UserA_34, Page UserA_35, Page UserA_36, Page UserA_37, Page UserA_38, Page UserA_39, Page UserA_40, Page UserA_41, Page UserA_42, Page UserA_43, Page UserA_44, Page UserA_45, Page UserA_46, Page UserA_47, Page UserA_48, Page UserA_49, Page UserA_50, Page UserA_51" for "editaccess_views"
  And I select "Public" from "accesslist[0][searchtype]"
  And I click on "Save"
  And I log out
  And I log in as "UserB" with password "Kupuh1pa!"
  And I choose "Shared with me" in "Share" from main menu
  And I check "Registered people"
  And I check "Public"
  And I select "title" from "sort"
  And I click on "Search" in the "#search_submit_container" "css_element"
  Then I should see "Results per page:"
  And I select "1" from "limit"
  And I should see "Page UserA_01"
  And I should not see "Page UserA_02"
  And I select "10" from "limit"
  And I should see "Page UserA_10"
  And I should not see "Page UserA_11"
  And I select "20" from "limit"
  And I should see "Page UserA_20"
  And I should not see "Page UserA_21"
  And I select "50" from "limit"
  And I should see "Page UserA_50"
  And I should not see "Page UserA_51"
