@javascript @core @core_administration
Feature: A user selects contact us from homepage and creates a message
In order to view the message
As user/admin
So admin can view the message in their inbox

Scenario: Checking that admin user can view messages in their mail sent from Contact us page
 Given I follow "contact us"
 And I set the field "Name" to "Dean"
 And I set the field "Email" to "deans@catalyst.net.nz"
 And I set the field "Subject" to "Whats wrong"
 And I set the field "Message" to "hello world"
 When I click on "Send message"
 And I log in as "admin" with password "Password1"
 And I trigger cron
 And I go to the homepage
 And I click on "mail"
 Then I should see "New contact us"
