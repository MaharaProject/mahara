@javascript @core @core_messages
Feature: Send messages to other users
   In order to send a message to another user
   As an admin I need to create an user
   So I can send it messages

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | Bob | Kupuhipa1 | test01@example.com | Bob | Mc Bobby | mahara | internal | member |


Scenario: Sending messages between user and admin (Bug 1426983)
   # Log in as "Admin" user
   Given I log in as "admin" with password "Kupuhipa1"
   # Verifying log in was successful
   And I should see "Admin User"
   # Sending message 1
   And I choose "Find friends" in "Groups"
   And I follow "Bob"
   And I follow "Send message"
   And I fill in the following:
   | Subject   | Hi there |
   | Message   | This is a test message   |
   And I press "Send message"
   # Sending message 2
   And I choose "Find friends" in "Groups"
   And I follow "Bob"
   And I follow "Send message"
   And I fill in the following:
   | Subject   | Hi there2 |
   | Message   | This is a test message2   |
   And I press "Send message"
   # Sending message 3
   And I choose "Find friends" in "Groups"
   And I follow "Bob"
   And I follow "Send message"
   And I fill in the following:
   | Subject   | Hi there3 |
   | Message   | This is a test message3   |
   And I press "Send message"
   # Log out as "Admin" user
   And I follow "Logout"
   # Log in as user 1
   Then I log in as "Bob" with password "Kupuhipa1"
   # Confirming all messages has been received
   And I am on homepage
   And I wait "1" seconds
   And I should see "Hi there"
   And I follow "mail"
   And I should see "Hi there"
   And I should see "Hi there2"
   And I should see "Hi there3"
   And I should not see "Call stack"
