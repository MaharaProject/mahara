@javascript @core @core_portfolio @walter
Feature: Adding an attachment to a note
In order to be able to add files to the notes on my portfolio
As a student
I need to be able to add an attachment to a note

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |



Scenario Outline: Adding an attachment to a note
 # Logging in as a user
 Given I log in as "<Log>" with password "Kupuhipa1"
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
 # Attaching a file to the note
 And I follow "Attachments" in the "div#instconf_artefactfieldset_container" "css_element"
 And I wait "1" seconds
 And I attach the file "Image2.png" to "userfile[]"
 And I should see "Upload of Image2.png complete"
 And I press "Save"
 And I should see "Note block 1"
 # Verifying the Note block saved
 And I choose "Notes" in "Content"
 And I should see "Note block 1"
 And I follow "Note block 1"
 And I should see "Image2.png"
 And I delete the "Note block 1" row
 Then I should see "Note deleted"

Examples:
| Log | Verify |
| admin | Admin User |
| userA | Pete Mc |
