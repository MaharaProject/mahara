@javascript @core_artefact @core @core_portfolio
 Feature: Adding more links to the jump list of the paginator on "Shared with me" page
  In order to be able to see and follow hyperlinks to numbered page lists across paginator in regular intervals
  As a student
  So I can navigate more efficiently through a large amount of pages.

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

 Scenario: Checking the jump list of the paginator (Bug 1409370)
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I choose "Shared by me" in "Share" from main menu
  And I click on "Share" in "Page UserA_01" row
  And I set the select2 value "Page UserA_01, Page UserA_02, Page UserA_03, Page UserA_04, Page UserA_05, Page UserA_06, Page UserA_07, Page UserA_08, Page UserA_09, Page UserA_10, Page UserA_11, Page UserA_12, Page UserA_13, Page UserA_14, Page UserA_15, Page UserA_16, Page UserA_17, Page UserA_18, Page UserA_19, Page UserA_20, Page UserA_21" for "editaccess_views"
  And I select "Public" from "accesslist[0][searchtype]"
  And I click on "Save" in the "#editaccess_submit_container" "css_element"
  And I log out
  And I log in as "UserB" with password "Kupuh1pa!"
  And I choose "Shared with me" in "Share" from main menu
  And I check "Registered people"
  And I check "Public"
  And I select "title" from "sort"
  And I click on "Search" in the "#search_submit_container" "css_element"
  And I should see "Results per page:"
  And I select "1" from "limit"
  Then I click on "...21"
  And I click on "...8"
  And I should see "Page UserA_08"
