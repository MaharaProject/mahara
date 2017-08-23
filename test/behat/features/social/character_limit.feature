 @javascript @core @core_group
 Feature: Friend request character limit
  In order to check the length limit of a message for a new friend request
  As an student
  So I can not send a long message in the friend request form.

 Scenario: Sending a friend request with more than 255 characters (Bug 1373670)
  Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuhipa1 | UserB@example.org | Bob | User | mahara | internal | member |
  Given I log in as "UserB" with password "Kupuhipa1"
  And I choose "Find people" in "Groups" from main menu
  And I follow "Angela User (UserA)"
  And I follow "Request friendship"
  And I fill in "Message" with "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent sed aliquet mauris. Nam et semper velit. Vestibulum porta dictum aliquet. Curabitur venenatis gravida nibh, ac consectetur risus pellentesque quis. Vivamus vitae erat sit amet augue interdum fermentum id ut arcu. Aliquam est lectus, iaculis a vulputate sed, tristique et nunc."
  And I press "Request friendship"
  Then I should see "This field must be at most 255 characters long."
