@javascript @core_artefact @core @core_portfolio
Feature: Page feedback configuration
In order to change the settings for placing feedback for a page
As an admin
So I can choose how I want to display my feedback

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

 Scenario Outline: Turning switchboxes on and off on feedback page (Bug 1431569)
 Given I log in as "<log>" with password "Kupuhipa1"
 And I follow "Portfolio"
 And I press "Create page"
 And I fill in the following:
 | Page title | Sharky |
 And I press "Save"
 And I follow "Display page"
 # Checking the default settings on the fields are correct
 And the following fields match these values:
 | Make public | 1 |
 # Changing the switchbox to be the opposite
 And I set the following fields to these values:
 | Make public | 0 |
 # Changing the switchbox back to default setting
 And I set the following fields to these values:
 | Make public | 1 |
 And I press "Comment"
 And I should see "There was an error with submitting this form. Please check the marked fields and try again."
 And I should see "Your message is empty. Please enter a message or attach a file."
 And I set the following fields to these values:
 | Message | This is a comment 1 |
 And I press "Comment"
 And I wait "1" seconds
 And I delete the "This is a comment 1" row
 Then I should see "Comment removed by the author"

 Examples:
| log |
| admin |
| userA |
