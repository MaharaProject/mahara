@javascript @core core_mesages
Feature: Send messages to other users
   In order to send a message to another user
   As an admin I need to create an user
   So I can send it messages


Scenario: Sending messages between user and admin (Bug 1426983)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Password1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Creating user 1
    And I follow "Administration"
    And I choose "Add user" in "Users"
    And I fill in the following:
   | firstname   | Bob  |
   | lastname    | bobby    |
   | email       | bob@example.com |
   | username    | bob  |
   | password    | mahara1  |
   And I press "Create user"
   When I follow "Return to site"
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
   Then I log in as "bob" with password "mahara1"
   And I fill in the following:
    | New password   | mahara2   |
    | Confirm password  | mahara2   |
    And I press "Submit"
    # Verifying password was changed successfully
    And I should see "Your new password has been saved"
    # Confirming all messages has been received
    And I follow "Inbox"
    And I should see "Hi there"
    And I should see "Hi there2"
    And I should see "Hi there3"
    And I should not see "Call stack"
