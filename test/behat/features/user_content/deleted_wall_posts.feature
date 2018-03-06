@javascript @core @core_view
Feature: To go onto the Mahara website
In order to go to a deleted wall post
As an admin
So I can see if the correct message shows

Scenario: Error Message For Deleted Wall Post (Bug 1255222)
 Given I log in as "admin" with password "Kupuh1pa!"
 Given the following "users" exist:
| username | password | email | firstname | lastname | institution | authname | role |
| UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
And I log out
And I log in as "UserA" with password "Kupuh1pa!"
 When I go to "/blocktype/wall/wall.php?id=9999"
 Then I should not see "$[[blockinstancenotfound/error]]"
