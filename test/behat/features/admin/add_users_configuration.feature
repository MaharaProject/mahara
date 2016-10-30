@javascript @core @core_administration
Feature: Configuration changes on add users page
In order to change configuration settings on the add users page
As an admin
So I can benefit from the use of different configuration changes

Scenario: Turning switches on and off on the Add user page (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 And I choose "Add user" in "Users"
 And I set the following fields to these values:
 | Site staff | 1 |
 | Site administrator | 1 |
 | Institution administrator | 1 |
 And I press "Create user"
 # Checking that the page will require you to fill more fields in before you hit save
 And I should see "There was an error with submitting this form. Please check the marked fields and try again."
