@javascript @core @core_group
Feature: Switching switches on the Edit group page
In order to edit a group
As an admin
I need to be able to turn the switches on and off and save the page

Scenario: Turning on and off switches on Group Edit page (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Groups"
 And I follow "Create group"
 And I set the following fields to these values:
 | Group name | The Avengers |
 # Checking all the switches are set to their correct default
 And the following fields match these values:
 | Open | 1 |
 | Controlled | 0 |
 | Request | 0 |
 | Friend invitations | 0 |
 | Recommendations | 0 |
 | Roles | Standard: Member, Admin |
 | Create and edit | All group members |
 | Allow submissions | 0 |
 | Allow archiving of submissions | 0 |
 | Publicly viewable group | 0 |
 | Hide group | 0 |
 | Hide membership | 0 |
 | Hide membership from members | 0 |
 | Participation report | 0 |
 | Auto-add users | 0 |
 | Shared page notifications | All group members |
 | Feedback notifications | All group members |
 | Send forum posts immediately | 0 |
 And I press "Save group"
 And I follow "Edit \"The Avengers\" Settings"
 # Checking all the switches can all be changed
 And I set the following fields to these values:
 | Open | 0 |
 | Controlled | 1 |
 | Request | 1 |
 | Friend invitations | 1 |
 | Recommendations | 0 |
 | Allow submissions | 1 |
 | Allow archiving of submissions | 1 |
 | Publicly viewable group | 1 |
 | Hide group | 1 |
 | Hide membership | 1 |
 | Hide membership from members | 1 |
 | Participation report | 1 |
 | Auto-add users | 1 |
 | Send forum posts immediately | 1 |
 And I press "Save group"
 And I follow "Edit \"The Avengers\" Settings"
 # Checking all the switches can all be changed back
 And I set the following fields to these values:
 | Open | 1 |
 | Controlled | 0 |
 | Request | 0 |
 | Friend invitations | 0 |
 | Recommendations | 0 |
 | Allow submissions | 0 |
 | Allow archiving of submissions | 0 |
 | Publicly viewable group | 0 |
 | Hide group | 0 |
 | Hide membership | 0 |
 | Hide membership from members | 0 |
 | Participation report | 0 |
 | Auto-add users | 0 |
 # Checking Friend Invitation and Recommendations cant both be on
 And I enable the switch "Friend invitations"
 And the "Recommendations" checkbox should not be checked
 And I enable the switch "Recommendations"
 And the "Friend invitations" checkbox should not be checked
 And I press "Save group"

