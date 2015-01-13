 @javascript @core @core_messages @core_accessibility
 Feature: Visible required fields astrix in Messaging
  In order to see astrix for required fields when sending a message
  As an admin
  So I can know I am required to fill those fields in so I can send the message

 Scenario: Sending a message in order to see astrix for required fields
  Given I log in as "admin" with password "Password1"
  Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
  And I follow "Administration"
  And I follow "Users"
  And I follow "Pete"
  And I follow "Send message"
  And I should see "Recipients *"
  And I should see "Subject *"
  And I should see "Message *"
