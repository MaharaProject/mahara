@javascript @core @core_messages @core_administration
Feature: Admins are allowed to see more types of messages than a regular person
In order to see what types are visible to me
As an admin/student
So I can filter messages

Background:
 Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |


Scenario: Selection options to filter messages (Bug 1433342)
 # Log in as "Admin"
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "People search" from administration menu
 And I click on "Angela"
 And I click on "Send message"
 And I fill in the following:
 | Subject   | Hi there |
 | Message   | This is a test message |
 And I click on "Send message"
 And I log out
 Given I log in as "UserA" with password "Kupuh1pa!"
 And I choose inbox
 And I click on "Hi there"
 And I click on "Reply"
 And I fill in the following:
 | Reply   | Hi yourself |
 And I click on "Reply"
 And I log out

 # First check what options an admin has
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose inbox
 And the "Activity type:" select box should contain all "Administration messages | Comment | Contact us | Feedback on annotations | Group message | Institution message | Message from other people | New forum post | New page access | Objectionable content | Objectionable content in forum | Repeat virus upload | System message | Virus flag release | Watchlist"
 And I log out

 # Then check what options a normal person has
 Given I log in as "UserA" with password "Kupuh1pa!"
 And I choose "Groups" in "Engage" from main menu
 And I click on "Create group"
 And I fill in "Group name" with "Jurassic Park"
 And I click on "Save group"
 And I choose inbox
 And the "Activity type:" select box should contain all "Comment | Feedback on annotations | Group message | Institution message | Message from other people | New forum post | New page access | Objectionable content in forum | System message | Watchlist"
