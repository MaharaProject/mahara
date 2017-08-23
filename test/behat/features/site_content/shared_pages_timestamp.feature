@javascript @core @core_group
Feature: Show created or updated time for shared pages to a group
In order to see shared pages
As as admin
To see that they display in the most recently updated order

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuhipa1 | UserB@example.org | Bob | User | mahara | internal | member |
     | UserC | Kupuhipa1 | UserC@example.org | Cecilia | User | mahara | internal | member |

Given the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | GroupA | admin | GroupA owned by admin | standard | ON | ON | all | ON | ON | UserA, UserB, UserC |  |

Given the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page GroupA_01 | Page 01 | group | GroupA |
     | Page GroupA_02 | Page 02 | group | GroupA |
     | Page GroupA_03 | Page 03 | group | GroupA |

Scenario: Displaying shared pages in most recently updated order (Bug 1490569)
 Given I log in as "admin" with password "Kupuhipa1"
 And I choose "Groups" from main menu
 And I follow "GroupA"
 And I follow "Edit this page"
 And I scroll to the id "column-container"
 And I configure the block "Group portfolios"
 When I set the following fields to these values:
   | Sort shared pages and collections by | Most recently updated |
 And I click on "Save"
 Then "Page GroupA_01" "text" should appear before "Page GroupA_02" "text"
 And "Page GroupA_03" "text" should appear after "Page GroupA_02" "text"
