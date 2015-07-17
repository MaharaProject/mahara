@javascript @core @core_messages @core_administration
Feature: Admins are allowed to see more types of messages than a user
In order to see what types are visible to me
As an admin/student
So I can filter messages

Background:
 Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
 Given I log in as "admin" with password "Password1"
 And I follow "Administration"
 And I follow "Users"
 And I follow "Pete"
 And I follow "Send message"
 And I fill in the following:
 | Subject   | Hi there |
 | Message   | This is a test message |
 And I press "Send message"
 And I log out
 Given I log in as "userA" with password "Password1"
 When I follow "mail"
 And I follow "Hi there"
 And I wait "1" seconds
 And I follow "Reply"
 And I fill in the following:
 | Reply   | Hi yourself |
 And I press "Reply"
 And I log out

Scenario: Selection options to filter messages (Bug 1433342)
 # First check what options an admin has
 Given I log in as "admin" with password "Password1"
 When I follow "mail"
 And the "Activity type:" select box should contain all "Administration messages | Contact us | Feedback | Feedback on annotations | Group message | Institution message | Message from other users | New forum post | New page access | Objectionable content | Objectionable content in forum | Repeat virus upload | System message | Virus flag release | Watchlist"
 And I log out

 # Then check what options a normal user has
 Given I log in as "userA" with password "Password1"
 And I follow "Groups"
 And I follow "Create group"
 And I fill in "Group name" with "Jurassic Park"
 And I press "Save group"
 And I am on homepage
 When I follow "mail"
 And the "Activity type:" select box should contain all "Feedback | Feedback on annotations | Group message | Institution message | Message from other users | New forum post | New page access | Objectionable content in forum | System message | Watchlist"
