@javascript @core @core_account
Feature: Sending friend requests
In order to send a friend request and be directed to the right page
As an admin/user
So I can be friends with other users

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |

Scenario: Sending friend requests from student to admin (Bug 1440908)
Given I log in as "admin" with password "Password1"
And I follow "Administration"
And I choose "User search" in "Users"
And I follow "Pete"
And I follow "Log in as userA"
And I follow "Admin User"
And I follow "Request friendship"
And I fill in "Would you like to be my friend?" for "Message"
And I press "Request friendship"
And I follow "Become Admin User again"
And I am on homepage
And I follow "New friend request"
And I follow "Requests"
And I press "Approve request"
And I should see "Accepted friend request"
And I follow "Log in as userA"
And I follow "Friend request accepted"
And I follow "More..."
And I should not see "Not Found"
And I should see "Remove from friends"
And I should see "Admin User's wall"



