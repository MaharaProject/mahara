@javascript @core @core_portfolio
Feature: Adding a Note to a page
In order to be able to write notes on my portfolio
As a student
I need to be able to add a Note block to my portfolio

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |



Scenario Outline: Adding a Note block to a portfolio (Bug 1424512)
 # Logging in as a user
 Given I log in as "<Log>" with password "Password1"
 # Verifying log in
 And I should see "<Verify>"
 # Creating a page
 And I follow "Portfolio"
 And I press "Create page"
 And I press "Save"
 # Adding a note block
 And I expand "General" node
 And I wait "1" seconds
 And I follow "Note" in the "div#general" "css_element"
 And I press "Add"
 # Configuring the block
 And I fill in the following:
 | Block title | Note block 1 |
 And I press "Save"
 And I should see "Note block 1"
 And I follow "Display page"
 # Verifying the Note block saved
 And I choose "Notes" in "Content"
 And I should see "Note block 1"

Examples:
| Log | Verify |
| admin | Admin User |
| userA | Pete Mc |
