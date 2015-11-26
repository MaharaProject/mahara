@javascript @core @core_group
Feature: Copied group page keeps title
In order to make sure group page title copies correctly
As a group member
So I can see group page and check the title

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Test | B | mahara | internal | member |
     | userB | Kupuhipa1 | test02@example.com | Test | C | mahara | internal | member |

Given the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | testgroup | admin | This is group 01 | standard | ON | ON | all | ON | ON | userA, userB |  |

Given the following "pages" exist:
     | title | description| ownertype | ownername |
     | Test Page | This is the page 1 | group | testgroup |

Scenario: Copying a group page retains title (Bug 1519374)
 # Make the group page copyable
 Given I log in as "userA" with password "Kupuhipa1"
 When I follow "Groups"
 And I follow "testgroup"
 And I follow "Share" in the "ul.nav-inpage" "css_element"
 And I click on "Edit access" in "Test Page" row
 And I follow "Advanced options"
 And I set the following fields to these values:
 | Allow copying | 1 |
 And I press "Save"
 And I log out

 Given I log in as "userB" with password "Kupuhipa1"
 And I am on homepage
 When I follow "Test Page"
 And I follow "Copy"
 And the following fields match these values:
 | Page title | Test Page |
 Then I press "Save"

