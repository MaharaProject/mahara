 @javascript @core @core_group
 Feature: Friend request character limit
  In order to check the length limit of a message for a new friend request
  As an student
  So I can not send a long message in the friend request form.

 Scenario: Sending a friend request with more than 255 characters (Bug 1373670)
  Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
     | userB | Kupuhipa1 | test02@example.com | Paul | Mc | mahara | internal | member |
  Given I log in as "userB" with password "Kupuhipa1"
  When I follow "Groups"
  And I choose "Find friends" in "Groups"
  And I follow "Pete Mc (userA)"
  And I follow "Request friendship"
  And I fill in "Message" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent sed aliquet mauris. Nam et semper velit. Vestibulum porta dictum aliquet. Curabitur venenatis gravida nibh, ac consectetur risus pellentesque quis. Vivamus vitae erat sit amet augue interdum fermentum id ut arcu. Aliquam est lectus, iaculis a vulputate sed, tristique et nunc."
  And I press "Request friendship"
  Then I should see "This field must be at most 255 characters long."
