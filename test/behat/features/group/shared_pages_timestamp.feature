@javascript @core_group
Feature: Show created or updated time for shared pages to a group
In order to see shared pages
As as admin
To see that they display in the most recently updated order

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Tuesd@y1 | test01@example.com | Test | B | mahara | internal | member |
     | userB | Tuesd@y2 | test02@example.com | Test | C | mahara | internal | member |
     | userC | Tuesd@y3 | test03@example.com | Test | A | mahara | internal | member |

Given the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | testgroup | admin | This is group 01 | standard | ON | ON | all | ON | ON | userA, userB, userC |  |

Given the following "pages" exist:
     | title | description| ownertype | ownername |
     | test Page 01 | This is the page 1 | group | testgroup |
     | test Page 02 | This is the page 2 | group | testgroup |
     | test Page 03 | This is the page 3 | group | testgroup |

Scenario: Displaying shared pages in most recently updated order (Bug 1490569)
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Groups"
 And I follow "testgroup"
 And I follow "Edit this page"
 And I configure the block "Group pages"
 When I set the following fields to these values:
   | Sort shared pages and collections by | Most recently updated |
 And I click on "Save"
 Then "test Page 01" "text" should appear before "test Page 02" "text"
 And "test Page 03" "text" should appear after "test Page 02" "text"

