@javascript @core @core_group
Feature: List of shared pages to a group
    In order to see shared pages to a group
    As a group member
    So I can see shared pages to the group

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuhipa1 | UserB@example.org | Bob | User | mahara | internal | member |
    And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | GroupA | UserB | GroupA owned by UserB | standard | ON | OFF | all | OFF | OFF | UserA |  |
     | GroupB | UserB | GroupB owned by UserB | standard | ON | OFF | all | OFF | OFF | UserA |  |
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
    And the following "collections" exist:
      | title | description | ownertype | ownername | pages |
      | Collection UserA_01 | Collection 01 | user | UserA | Page UserA_06, Page UserA_12 |
      | Collection UserA_02 | Collection 02 | user | UserA | Page UserA_07 |
      | Collection UserA_03 | Collection 03 | user | UserA | Page UserA_08 |
      | Collection UserA_04 | Collection 04 | user | UserA | Page UserA_09 |
      | Collection UserA_05 | Collection 05 | user | UserA | Page UserA_10 |
      | Collection UserA_06 | Collection 06 | user | UserA | Page UserA_11 |

Scenario: Share pages and collections to a group.
The list of shared pages must take into account of access date (Bug 1374163)
    # Log in as a normal user
    Given I log in as "UserA" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Angela"
    And I should see "GroupB"
    # Edit access for Page UserA_01
    And I choose "Shared by me" in "Portfolio" from main menu
    And I follow "Pages" in the "div#main-column-container" "css_element"
    And I click on "Edit access" in "Page UserA_01" row
    And I select "GroupB" from "accesslist[0][searchtype]"
    And I fill in "accesslist[0][startdate]" with "2015/06/15 03:00"
    And I press "Save"
    # Edit access for Page UserA_02
    And I choose "Shared by me" in "Portfolio" from main menu
    And I follow "Pages" in the "div#main-column-container" "css_element"
    And I click on "Edit access" in "Page UserA_02" row
    And I select "GroupB" from "accesslist[0][searchtype]"
    And I fill in "accesslist[0][stopdate]" with "2015/04/15 02:50"
    And I press "Save"
    And I should see "The end date for 'group' access cannot be in the past."
    And I press "Cancel"
    # Edit access for Page UserA_03
    And I choose "Shared by me" in "Portfolio" from main menu
    And I follow "Pages" in the "div#main-column-container" "css_element"
    And I click on "Edit access" in "Page UserA_03" row
    And I follow "Advanced options"
    And I fill in the following:
      | Access start date/time | 2015/06/15 00:00 |
    And I press "Save"
    # Edit access for Page UserA_05
    And I choose "Shared by me" in "Portfolio" from main menu
    And I follow "Pages" in the "div#main-column-container" "css_element"
    And I click on "Edit access" in "Page UserA_05" row
    And I select "GroupB" from "accesslist[0][searchtype]"
    And I press "Save"
    # Edit access for Collection UserA_01
    And I choose "Shared by me" in "Portfolio" from main menu
    And I click on "Edit access" in "Collection UserA_01" row
    And I set the select2 value "Collection UserA_01" for "editaccess_collections"
    And I select "GroupB" from "accesslist[0][searchtype]"
    And I fill in "accesslist[0][startdate]" with "2015/06/15 03:00"
    And I press "Save"
    # Edit access for Collection UserA_03
    And I choose "Shared by me" in "Portfolio" from main menu
    And I click on "Edit access" in "Collection UserA_03" row
    And I set the select2 value "Collection UserA_03" for "editaccess_collections"
    And I follow "Advanced options"
    And I fill in the following:
      | Access start date/time | 2015/06/15 00:00 |
    And I press "Save"
    # Edit access for Collection UserA_05
    And I choose "Shared by me" in "Portfolio" from main menu
    And I click on "Edit access" in "Collection UserA_05" row
    And I set the select2 value "Collection UserA_05" for "editaccess_collections"
    And I select "GroupB" from "accesslist[0][searchtype]"
    And I press "Save"
    # Check the list of shared pages to group "GroupB"
    And I choose "Groups" from main menu
    And I follow "GroupB"
    And I should see "Page UserA_05"
    And I should see "Collection UserA_05"
    And I should not see "Page UserA_03"
    And I should not see "Collection UserA_03"
