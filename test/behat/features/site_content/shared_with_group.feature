@javascript @core @core_group
Feature: List of shared pages to a group
    In order to see shared pages to a group
    As a group member
    So I can see shared pages to the group

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
     | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | mahara | internal | member |
    And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | GroupA | UserB | GroupA owned by UserB | standard | ON | OFF | all | OFF | OFF | UserA |  |
     | GroupB | admin | GroupB owned by admin | standard | ON | ON | all | ON | ON | UserA, UserB, UserC |  |
    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | Page UserA_01 | Page 01 | user | UserA |
      | Page UserA_02 | Page 02 | user | UserA |
      | Page UserA_03 | Page 03 | user | UserA |
      | Page UserA_05 | Page 05 | user | UserA |
      | Page UserA_06 | Page 06 | user | UserA |
      | Page UserA_07 | Page 07 to be used as solo page | user | UserA |
      | Page UserA_08 | Page 08 | user | UserA |
      | Page UserA_10 | Page 10 | user | UserA |
      | Page UserA_12 | Page 12 | user | UserA |
      | Page GroupB_01 | Group page 01 | group | GroupB |
      | Page GroupB_02 | Group page 02 | group | GroupB |
      | Page GroupB_03 | Group page 03 | group | GroupB |
    And the following "collections" exist:
      | title | description| ownertype | ownername | pages |
      | Collection UserA_01 | Collection 01 | user | UserA | Page UserA_06, Page UserA_12 |
      | Collection UserA_03 | Collection 03 | user | UserA | Page UserA_08 |
      | Collection UserA_05 | Collection 05 | user | UserA | Page UserA_10 |

Scenario: Share pages and collections to a group.
The list of shared pages must take into account of access date (Bug 1374163)
    # Log in as a normal user
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Edit sharing permissions for Page 01
    And I choose "Shared by me" in "Share" from main menu
    And I click on "Pages" in the "Share tabs" "Misc" property
    And I click on "Share" in "Page UserA_01" row
    And I select "GroupA" from "accesslist[0][searchtype]"
    And I fill in "accesslist[0][startdate]" with "2015/06/15 03:00"
    And I click on "Save"
    # Edit sharing permissions for Page UserA_02
    And I choose "Shared by me" in "Share" from main menu
    And I click on "Pages" in the "Share tabs" "Misc" property
    And I click on "Share" in "Page UserA_02" row
    And I select "GroupA" from "accesslist[0][searchtype]"
    And I fill in "accesslist[0][stopdate]" with "2015/04/15 02:50"
    And I click on "Save"
    And I should see "The end date for 'group' access cannot be in the past."
    And I click on "Cancel"
    # Edit sharing permissions for Page UserA_03
    And I choose "Shared by me" in "Share" from main menu
    And I click on "Pages" in the "Share tabs" "Misc" property
    And I click on "Share" in "Page UserA_03" row
    And I click on "Advanced options"
    And I fill in the following:
      | Access start date/time | 2015/06/15 00:00 |
    And I click on "Save"
    # Edit sharing permissions for Page UserA_05
    And I choose "Shared by me" in "Share" from main menu
    And I click on "Pages" in the "Share tabs" "Misc" property
    And I click on "Share" in "Page UserA_05" row
    And I select "GroupA" from "accesslist[0][searchtype]"
    And I click on "Save"

    #Checking the last modified date on a collection shared to a group.
    And I choose "Shared by me" in "Share" from main menu
    And I click on "Collections" in the "Share tabs" "Misc" property
    And I click on "Share" in "Collection UserA_01" row
    And I select "GroupA" from "accesslist[0][searchtype]"
    And I click on "Save"
    Then I should see "Access rules were updated for 2 pages"
    And I choose "Groups" in "Engage" from main menu
    And I click on "GroupA"
    # the formats "strftimedate" and "j F Y" both resolve to dd Month YYYY, which is wanted here.
    And I wait "1" seconds
    And I should see the date "today" in the "Collections shared with this group" "Groups" property with the format "d F Y"

    # Edit sharing permissions for Collection 01
    And I choose "Shared by me" in "Share" from main menu
    And I click on "Share" in "Collection UserA_01" row
    And I set the select2 value "Collection UserA_01" for "editaccess_collections"
    And I select "GroupA" from "accesslist[0][searchtype]"
    And I fill in "accesslist[0][startdate]" with "2015/06/15 03:00"
    And I click on "Save"
    # Edit sharing permissions for Collection 03
    And I click on "Share" in "Collection UserA_03" row
    And I set the select2 value "Collection UserA_03" for "editaccess_collections"
    And I click on "Advanced options"
    And I fill in the following:
      | Access start date/time | 2015/06/15 00:00 |
    And I click on "Save"
    # Edit sharing permissions for Collection 05
    And I click on "Share" in "Collection UserA_05" row
    And I set the select2 value "Collection UserA_05" for "editaccess_collections"
    And I select "GroupA" from "accesslist[0][searchtype]"
    And I click on "Save"
    # Check the list of shared pages to group "GroupA"
    And I choose "Groups" in "Engage" from main menu
    And I click on "GroupA"
    And I wait "1" seconds
    And I should see "Page UserA_05"
    And I should see "Collection UserA_05"
    And I should not see "Page UserA_03"
    And I should not see "Collection UserA_03"
    #Testing that view access for views in collections are editable properly
    And I choose "Shared by me" in "Share" from main menu
    And I click on "Collections" in the "Share tabs" "Misc" property
    Then I should see "Collection UserA_01"
    And I click on "Share" in "Collection UserA_01" row
    Then I should see "Collection UserA_01"
    And I should not see "Page UserA_01"

    And I choose "Shared by me" in "Share" from main menu
    And I click on "Pages" in the "Share tabs" "Misc" property
    Then I click on "Share" in "Page UserA_07" row
    Then I should see "Page UserA_07"
    And I should not see "Collection UserA_01"
    And I log out
    #Displaying shared pages in most recently updated order (Bug 1490569)
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I click on "GroupB"
    And I click on "Edit"
    And I scroll to the id "column-container"
    And I configure the block "Group portfolios"
    When I set the following fields to these values:
      | Sort shared portfolios by | Most recently updated |
    And I click on "Save"
    Then "GroupB_01" "text" should appear before "GroupB_02" "text"
    And "GroupB_03" "text" should appear after "GroupB_02" "text"
