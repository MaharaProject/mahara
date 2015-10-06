@javascript @core @core_content
Feature: Creating a journal
In order write in my journal
As an admin
I need to have a journal

Scenario: Creating a Journal and publishing a draft
 Given I log in as "admin" with password "Kupuhipa1"
 When I choose "Journal" in "Content"
And I follow "New entry"
 And I set the following fields to these values:
 | Title *| my diary |
 | Entry | I  love my mum |
 | Tags | george |
 | Draft | 1  |
 | Allow comments | 0 |
 And I press "Save entry"
 Then I should see "Journal entry saved"
 And I press "Publish"
