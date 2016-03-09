 @javascript @core @core_messages
 Feature: Asterix (*) on required fields on Send Message page
  In order to see an asterix on the required fields when sending a message
  As an admin
  So I can know I am required to fill those fields in so I can send the message

 Scenario:
  Given I log in as "admin" with password "Kupuhipa1"
  Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
  When I follow "Administration"
  And I follow "Users"
  And I follow "Pete"
  And I follow "Send message"
  Then I should see "Recipients *"
  And I should see "Subject *"
 And I should see "Message *"

