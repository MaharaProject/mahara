@javascript @core @core_messages
Feature: Clicking on Inbox
   In order to click on the mailbox
   As a student user
   So I can send messages to other students

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |

Scenario: Clicking on the Inbox link on the right menu (Bug 1427019)
   # Log in as users
   Given I log in as "userA" with password "Password1"
   # Navigating to the Inbox to check the new ID tag works
   And I click on "mail"
   # Verifying that you do not see a page full of error messages
   And I should not see "Call stack"
   And I should see "Notifications"
   And I should see "Compose"

