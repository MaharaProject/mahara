@javascript @core @core_group
Feature: List of shared pages to a group
    In order to see shared pages to a group
    As a group member
    So I can see shared pages to the group

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
     | userB | Kupuhipa1 | test02@example.com | Son | Nguyen | mahara | internal | member |
    And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | Group Y | userB | This is group Y | standard | ON | OFF | all | OFF | OFF | userA |  |
     | Group Z | userB | This is group Z | standard | ON | OFF | all | OFF | OFF | userA |  |
    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | Page 01 | This is the page 01 | user | userA |
      | Page 02 | This is the page 02 | user | userA |
      | Page 03 | This is the page 03 | user | userA |
      | Page 04 | This is the page 04 | user | userA |
      | Page 05 | This is the page 05 | user | userA |
      | Page 06 | This is the page 06 | user | userA |
      | Page 07 | This is the page 07 | user | userA |
      | Page 08 | This is the page 08 | user | userA |
      | Page 09 | This is the page 09 | user | userA |
      | Page 10 | This is the page 10 | user | userA |
      | Page 11 | This is the page 11 | user | userA |
      | Page 12 | This is the page 12 | user | userA |
    And the following "collections" exist:
      | title | description| ownertype | ownername | pages |
      | Collection 01 | This is the collection 01 | user | userA | Page 06, Page 12 |
      | Collection 02 | This is the collection 02 | user | userA | Page 07 |
      | Collection 03 | This is the collection 03 | user | userA | Page 08 |
      | Collection 04 | This is the collection 04 | user | userA | Page 09 |
      | Collection 05 | This is the collection 05 | user | userA | Page 10 |
      | Collection 06 | This is the collection 06 | user | userA | Page 11 |

Scenario: Share pages and collections to a group.
The list of shared pages must take into account of access date (Bug 1374163)
    # Log in as a normal user
    Given I log in as "userA" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Pete"
    And I should see "Group Z"
    # Edit access for Page 01
    And I choose "Shared by me" in "Portfolio"
    And I follow "Pages" in the "div#main-column-container" "css_element"
    And I click on "Edit access" in "Page 01" row
    And I select "Group Z" from "accesslist[0][searchtype]"
    And I set the field "accesslist[0][startdate]" to "2015/06/15 03:00"
    And I press "Save"
    # Edit access for Page 02
    And I choose "Shared by me" in "Portfolio"
    And I follow "Pages" in the "div#main-column-container" "css_element"
    And I click on "Edit access" in "Page 02" row
    And I select "Group Z" from "accesslist[0][searchtype]"
    And I set the field "accesslist[0][stopdate]" to "2015/04/15 02:50"
    And I press "Save"
    And I should see "The end date for 'group' access cannot be in the past."
    And I press "Cancel"
    # Edit access for Page 03
    And I choose "Shared by me" in "Portfolio"
    And I follow "Pages" in the "div#main-column-container" "css_element"
    And I click on "Edit access" in "Page 03" row
    And I follow "Advanced options"
    And I fill in the following:
      | Access start date/time | 2015/06/15 00:00 |
    And I press "Save"
    # Edit access for Page 05
    And I choose "Shared by me" in "Portfolio"
    And I follow "Pages" in the "div#main-column-container" "css_element"
    And I click on "Edit access" in "Page 05" row
    And I select "Group Z" from "accesslist[0][searchtype]"
    And I press "Save"
    # Edit access for Collection 01
    And I choose "Shared by me" in "Portfolio"
    And I click on "Edit access" in "Collection 01" row
    And I set the select2 field "editaccess_collections" to "Collection 01"
    And I select "Group Z" from "accesslist[0][searchtype]"
    And I set the field "accesslist[0][startdate]" to "2015/06/15 03:00"
    And I press "Save"
    # Edit access for Collection 03
    And I choose "Shared by me" in "Portfolio"
    And I click on "Edit access" in "Collection 03" row
    And I set the select2 field "editaccess_collections" to "Collection 03"
    And I follow "Advanced options"
    And I fill in the following:
      | Access start date/time | 2015/06/15 00:00 |
    And I press "Save"
    # Edit access for Collection 05
    And I choose "Shared by me" in "Portfolio"
    And I click on "Edit access" in "Collection 05" row
    And I set the select2 field "editaccess_collections" to "Collection 05"
    And I select "Group Z" from "accesslist[0][searchtype]"
    And I press "Save"
    # Check the list of shared pages to group "Group Z"
    And I choose "Groups"
    And I follow "Group Z"
    And I should see "Page 05"
    And I should see "Collection 05"
    And I should not see "Page 03"
    And I should not see "Collection 03"
