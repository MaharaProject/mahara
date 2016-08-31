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
   And I choose "Find people" in "Groups" from main menu
   And I follow "Bob"
   And I follow "Send message"
   And I fill in the following:
   | Subject   | Hi there |
   | Message   | This is a test message   |
   And I press "Send message"
   # Sending message 2
   And I choose "Find people" in "Groups" from main menu
   And I follow "Bob"
   And I follow "Send message"
   And I fill in the following:
   | Subject   | Hi there2 |
   | Message   | This is a test message2   |
   And I press "Send message"
   # Sending message 3
   And I choose "Find people" in "Groups" from main menu
   And I follow "Bob"
   And I follow "Send message"
   And I fill in the following:
   | Subject   | Hi there3 |
   | Message   | This is a test message3   |
   And I press "Send message"
   And I choose "Find people" in "Groups" from main menu
   And I follow "Bob"
   And I follow "Request friendship"
   And I fill in the following:
   | Message   | I shot an arrow into the air, it fell to earth, I knew not where; For, so swiftly it flew, the sight could not follow it in its flight. Long, long afterward, in an oak I found the arrow, still unbroke; And the song, from beginning to end, I found again in the heart of a friend. |
   Then I should see "This field must be at most 255 characters long"
   And I fill in the following:
   | Message   | Written with a pen, sealed with a kiss, if you are my friend, please answer me this |
   And I press "Request friendship"
   # Log out as "Admin" user
   And I log out
   # Log in as user 1
   Then I log in as "Bob" with password "Kupuhipa1"
   # Confirming all messages has been received
   And I am on homepage
   And I should see "Hi there"
   And I choose "mail" from user menu by id
   And I should see "Hi there"
   And I should see "Hi there2"
   And I should see "Hi there3"
   And I should not see "Call stack"
