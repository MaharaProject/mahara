@javascript @core @core_group
Feature: Switching switch on and off when editing a forum
 In order to automatically subscribe users via switchbox
 As an admin
 I need to be able to flick the switch on and off

Scenario: Turning on and off switches in the group forums tab (Bug 1431569)
 Given I log in as "admin" with password "Password1"
 And I choose "My groups" in "Groups"
 And I follow "Create group"
 And I set the following fields to these values:
 | Group name | Turtles |
 When I press "Save group"
 And I follow "Forums"
 And I follow "General discussion"
 And I follow "Edit forum"
 # There are 2 settings links on the page and it needs to identify which one to follow
 And I follow "Forum settings"
 # Checking "Automatically subscribe users" swtichbox is on by default
 And the "edit_interaction_autosubscribe" checkbox should be checked
 # Checking it can be turned off
 When I uncheck "edit_interaction_autosubscribe"
 # Checking it can turn back on
 Then I check "edit_interaction_autosubscribe"
 # Verifying that it did turn back on
 And the "edit_interaction_autosubscribe" checkbox should be checked
 # Checking off is the default setting on the close new topics checkbox
 And the "edit_interaction_closetopics" checkbox should not be checked
 # Checking it turns on
 And I check "edit_interaction_closetopics"
 # Checking it turns back off
 And I uncheck "edit_interaction_closetopics"
 And I press "Save"
