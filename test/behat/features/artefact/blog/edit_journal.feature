@javascript @core @core_artefact
Feature: Editing my journal
In order to edit my journal
As an admin/user
I need to create a journal

Background:
Given the following "users" exist:
 | username | password | email | firstname | lastname | institution | authname | role |
 | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |


Scenario Outline: Creating a journal and editing it (Bug 1513716)
Given I log in as "<log>" with password "Password1"
# Creating a journal
And I choose "Journal" in "Content"
And I should see "Journals"
When I click on "New entry"
And I set the following fields to these values:
 | Title | My new journal |
 | Entry | blog |
And I press "Save entry"
Then I should see "Journal entry saved"
# Editing the Journal
And I click on "Edit \"My new journal\""
And I set the following fields to these values:
 | Title | My new journal |
 | Entry | Jinelle was here Nov 2015 |
 And I press "Save entry"
 And I should see "Journal entry saved"
 And I should see "Jinelle was here Nov 2015"

Examples:
| log |
| admin |
| userA |
