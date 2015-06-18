@javascript @core @core_view
Feature: To go onto the Mahara website
In order to go to a deleted wall post
As an admin
So I can see if the correct message shows

Scenario: Error Message For Deleted Wall Post (Bug 1255222)
 Given I log in as "admin" with password "Password1"
 Given the following "users" exist:
| username | password | email | firstname | lastname | institution | authname | role |
| userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
And I follow "Logout"
And I log in as "userA" with password "Password1"
 When I go to "/blocktype/wall/wall.php?id=9999"
 Then I should not see "$[[blockinstancenotfound/error]]"


