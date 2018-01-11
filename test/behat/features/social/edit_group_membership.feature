@javascript @core @core_messages
Feature: Edit group membership
   In order to edit group membership
   As an admin I can edit membership via the 'Find people' page

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuhipa1 | UserB@example.org | Bob | User | mahara | internal | member |
     | UserC | Kupuhipa1 | UserC@example.org |Cecilia | User | mahara | internal | member |
     | UserD | Kupuhipa1 | UserD@example.org | Dmitri | User | mahara | internal | member |
     | UserE | Kupuhipa1 | UserE@example.org | Evonne | User | mahara | internal | member |
     | UserF | Kupuhipa1 | UserF@example.org | Fergus | User | mahara | internal | member |
     | UserG | Kupuhipa1 | UserG@example.org | Gabi | User | mahara | internal | member |
     | UserH | Kupuhipa1 | UserH@example.org | Hugo |User | mahara | internal | member |
     | UserI | Kupuhipa1 | UserI@example.org | Iria | User | mahara | internal | member |
     | UserJ | Kupuhipa1 | UserJ@example.org | Julius |User | mahara | internal | member |
     | UserK | Kupuhipa1 | UserK@example.org | Kristina | User | mahara | internal | member |
     | UserL | Kupuhipa1 | UserL@example.org | Liam | User | mahara | internal | member |

And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | GroupA | admin | GroupA owned by admin | standard | ON | ON | all | ON | ON | UserA, UserB |  |
     | GroupB | admin | GroupB owned by admin | standard | ON | ON | all | ON | ON | UserC, UserD |  |


Scenario: Check modal is working for the "Edit group memebership" on find people page (Bug 1513265)
   # Log in as "Admin" user
   Given I log in as "admin" with password "Kupuhipa1"
   And I choose "Find people" in "Groups" from main menu
   And I follow "2" in the "Find people results" property
   And I click on "Edit group membership" in "Liam User" row
   # allow the modal to open
   And I wait "1" seconds
   And I check "GroupA"
   And I follow "Apply changes"
   And I scroll to the top
   Then I should see "Invite sent"
