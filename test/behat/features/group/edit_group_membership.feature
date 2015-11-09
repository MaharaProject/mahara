@javascript @core core_messages
Feature: Edit group membership
   In order to edit group membership
   As an admin I can edit membership via the 'Find friends' page

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | User1 | Kupuhipa1 | test01@example.com | User | 1 | mahara | internal | member |
     | User2 | Kupuhipa1 | test02@example.com | User | 2 | mahara | internal | member |
     | User3 | Kupuhipa1 | test03@example.com | User | 3 | mahara | internal | member |
     | User4 | Kupuhipa1 | test04@example.com | User | 4 | mahara | internal | member |
     | User5 | Kupuhipa1 | test05@example.com | User | 5 | mahara | internal | member |
     | User6 | Kupuhipa1 | test06@example.com | User | 6 | mahara | internal | member |
     | User7 | Kupuhipa1 | test07@example.com | User | 7 | mahara | internal | member |
     | User8 | Kupuhipa1 | test08@example.com | User | 8 | mahara | internal | member |
     | User9 | Kupuhipa1 | test09@example.com | User | 9 | mahara | internal | member |
     | User10 | Kupuhipa1 | test10@example.com | User | 10 | mahara | internal | member |
     | User11 | Kupuhipa1 | test011@example.com | User | 11 | mahara | internal | member |
     | User12 | Kupuhipa1 | test012@example.com | User | 12 | mahara | internal | member |

And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | GroupA | admin | This is group A | standard | ON | ON | all | ON | ON | User1, User2 |  |
     | GroupB | admin | This is group B | standard | ON | ON | all | ON | ON | User3, User4 |  |


Scenario: Check modal is working for the "Edit group memebership" on find friends page (Bug 1513265)
   # Log in as "Admin" user
   Given I log in as "admin" with password "Kupuhipa1"
   And I choose "Find friends" in "Groups"
   And I follow "2" in the "div#friendslist_pagination" "css_element"
   And I follow "Edit group membership"
   Then I should see "Apply changes"
