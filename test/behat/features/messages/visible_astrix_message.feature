 @javascript @core @core_messages
 Feature: message visible required fields astrix
  In order to see astrix on some fields when sending a message
  As an admin
  So I can know I am required to fill those fields in so i can send the message

 Scenario:
  Given I log in as "admin" with password "Password1"
  Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
  When I follow "Administration"
  And I follow "Users"
  And I follow "Pete"
  And I follow "Send message"
  Then I should see "Recipients *"
  And I should see "Subject *"
 And I should see "Message *"

