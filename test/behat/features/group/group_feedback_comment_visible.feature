@javascript @core @core_artefact @core_group
Feature: Commenting on a group page
In order to be able to verify I commented publically on a group page
As a user
So leave feedback and it appears in the right place

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
     | userB | Kupuhipa1 | test02@example.com | Rachel | Mc | mahara | internal | member |

Given the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | group 01 | userA | This is group 01 | standard | ON | ON | all | OFF | OFF | userB |   |



Scenario: As a user leaving public feedback on a group page (Bug 1509129)
 Given I log in as "userA" with password "Kupuhipa1"
 And I choose "Groups"
 # Changing the settings of the block to change notification feedback
 And I click on "Settings" in the "div.groupuserstatus" "css_element"
 And I set the following fields to these values:
 | Feedback notifications | None |
 And I press "Save group"
 When I click on "Pages" in the ".right-text" "css_element"
 And I press "Create page"
 And I set the following fields to these values:
 | Page title | Group Page 01 |
 And I press "Save"
 And I follow "Display page"
 And I fill in "Adding a comment to this field. Student = Awesome!" in WYSIWYG editor "add_feedback_form_message_ifr"
 # Checking that the make public is on
 And the following fields match these values:
  | Make public | 1 |
 And I press "Comment"
 # Verifying that it saves
 Then I should see "Feedback submitted"
 And I should see "Adding a comment to this field. Student = Awesome!"
 And I log out
 And I log in as "userB" with password "Kupuhipa1"
 # Needs to navigate to see the feedback and check it can be seen publically
 Then I should see "group 01"
 When I follow "group 01"
 Then I should see "About | group 01"
 When I click on "Pages" in the "ul.nav-inpage" "css_element"
 Then I should see "Group Page 01" in the "h3.title" "css_element"
 When I click on "Group Page 01" in the "div.list-group-item" "css_element"
 Then I should see "Adding a comment to this field. Student = Awesome!" in the "div.comment-text" "css_element"
