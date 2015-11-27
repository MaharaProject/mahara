@javascript @core @core_messages
Feature: Sendmessage notification test
In order to make sure dislayed message shows html special chars correctly 
As a user I need to send a message to another user
So I can confirm the message displays correctly in the inbox

Background:
Given the following "users" exist:
  | username | password | email | firstname | lastname | institution | authname | role |
  | userA | Kupuhipa1 | test01@example.com | Andrea | Andrews | mahara | internal | member |
  | userB | Kupuhipa1 | test02@example.com | Barry | Bishop | mahara | internal | member |

Scenario: Sending a message with html special characters in it (Bug #1520011)
  # Log in as userA
  Given I log in as "userA" with password "Kupuhipa1"
  And I choose "Find friends" in "Groups"
  And I follow "userB"
  # Send a message
  And I follow "Send message"
  And I set the following fields to these values:
  | Subject | Test message with < & > |
  | Message | This is a test with > & < |
  And I press "Send message"
  Then I should see "Message sent"

  # Checking message
  When I follow "mail"
  And I follow "Sent"
  And I follow "Test message"
  Then I should see "Test message with < & >"
  And I should see "This is a test with > & <"