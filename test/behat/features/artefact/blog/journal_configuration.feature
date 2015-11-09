@javascript @core @core_content
Feature: Creating a journal entry and configuring the form
In order to change the configuration of my Journal entry
As a user
So I can benefit from the different settings


Scenario: Turning on and of switches in Journal configuration block (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 # Navigating to switchbox in Journal block
 And I choose "Journals" in "Content"
 And I follow "New entry"
# Checking the default fields match
 And the following fields match these values:
 | Draft | 0 |
 | Allow comments | 1 |
# Changing the switches once
 And I set the following fields to these values:
 | Draft | 1 |
 | Allow comments | 0 |
# Changing the switches back
 And I set the following fields to these values:
 | Draft | 0 |
 | Allow comments | 1 |
 And I press "Save entry"
 And I should see "There was an error with submitting this form. Please check the marked fields and try again."


Scenario: Creating a Journal entry
 Given I log in as "admin" with password "Kupuhipa1"
 # Navigating to switchbox in Journal block
 And I choose "Journals" in "Content"
 And I follow "New entry"
# Checking the default fields match
 And the following fields match these values:
 | Title * | |
 | Entry * | |
 | Draft | 0 |
 | Allow comments | 1 |
# Changing the switches once and filling out a journal
 And I set the following fields to these values:
 | Title * | Story of my life |
 | Draft | 1 |
 | Allow comments | 0 |
 | Entry | Preventing bugs from appearing :D |
 And I press "Save entry"




