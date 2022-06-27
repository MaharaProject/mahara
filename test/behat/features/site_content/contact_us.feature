@javascript @core
Feature: A user selects contact us from homepage and creates a message
In order to view the message
As user/admin
So admin can view the message in their inbox

Background:
  Given the following "institutions" exist:
  | name | displayname | registerallowed | registerconfirm |
  | pcnz | Institution One | ON | OFF |

Scenario: Checking that admin user can view messages in their mail sent from Contact us page
 Given I follow "Contact us"
 And I fill in "Name" with "Dean"
 And I fill in "Email" with "deans@example.org"
 And I fill in "Subject" with "Whats wrong"
 And I fill in "Message" with "hello world"
 # to avoid 5-second spam trap on contact.php
 And I wait "5" seconds
 When I click on "Send message"
 # Trigger the cron and make sure all jobs are done
 # TODO: run all cron jobs
 And I trigger cron
 And I go to the homepage
 And I log in as "admin" with password "Kupuh1pa!"
 And I choose inbox
 Then I should see "New contact us"
