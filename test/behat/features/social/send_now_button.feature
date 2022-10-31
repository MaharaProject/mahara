@javascript @core
Feature: Disabled the "Send Message Now" option when the time expired to edit forum posts
As a student
So I can edit messages later without notifying all the subscribed users

Background:
 Given the following "users" exist:
 | username | password | email | firstname | lastname | institution | authname | role |
 | UserA | Kupuh1pa! | UserA@example.org | Angela | User |  | admin | staff |
 | UserB | Kupuh1pa! | UserB@example.org | Bob | User |  | internal | member |
 | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User |  | internal | member |

 And the following "groups" exist:
 | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
 | GroupA | UserA | GroupA owned by UserA | standard | ON | OFF | all | ON | OFF | UserB, UserA | UserA |
 | GroupC | UserC | GroupC owned by UserC | standard | ON | OFF | all | OFF | OFF | UserC | UserC |

 # Admin user change 30 minute time expiry to 1 minutes
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Plugin administration" in "Extensions" from administration menu
 And I scroll to the center of id "interaction.installed"
 And I click on "Configuration for interaction Forum"
 And I should see "Post delay"
 And I fill in "Post delay" with "1"
 And I press "Save"
 Then I log out

Scenario:  Checking the "Send Message Now" forum post option is disabled after time expired to send it (Bug 1396897)
 Given I log in as "UserA" with password "Kupuh1pa!"
 # Navigate to the Forums page
 And I choose "Groups" in "Engage" from main menu
 And I should see "GroupA"
 And I follow "GroupA"
 And I follow "Forums"

 # Create new topic
 When I follow "General discussion"
 And I follow "New topic"
 And I set the following fields to these values:
 | Subject | Testing subject 1 |
 | Message | message for testing subject 1 |
 # Checking "Send message now" switchbox is off by default
 And the "edittopic_sendnow" checkbox should not be checked
 # Turning the checkbox to send now
 And I enable the switch "Send message now"
 Then I press "Post"
 # Verifying post has been created
 And I follow "Forums"
 And I follow "General discussion"

 # Checking the send message is disabled once a message has sent
 Then I follow "Edit topic"
 Then I should not see "Send message now"
