@javascript @core @blocktype @blocktype_notes
Feature: Add notes using content from an existing note (Bugs 1710988 & 1044878)
As a Person
I want to add portfolio page note content using an existing note
So that I can easily keep my portfolio pages up to date

Background:

Given the following "users" exist:
  | username | password | email | firstname | lastname | institution | authname | role |
  | UserA | Kupuh1pa! | UserA@example.com | Angela | User | mahara | internal | member |
And the following "pages" exist:
  | title | description| ownertype | ownername |
  | Page UserA_01 | Page 01| user | UserA |
  | Page UserA_02 | Page 02| user | UserA |

Scenario: Page1 - create note with content then use it to create another note (via copy)
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I follow "Page UserA_01"

# 1. Create an original Page1 note1 with content on Page1
  And I follow "Edit"
  When I follow "Drag to add a new block" in the "blocktype sidebar" property
  And I press "Add"
  And I click on blocktype "Note"
  And I set the following fields to these values:
  | Block title | Note page1 block1 original title |
  | Block content | This is page1 block1 original content |
  And I press "Save"

# 2.1 Create a second Page1 note2 with content COPIED from note1 and edited
  When I follow "Drag to add a new block" in the "blocktype sidebar" property
  And I press "Add"
  And I click on blocktype "Note"
  And I follow "Use content from another note"
  And I select the radio "Note page1 block1 original title"
  And I should see "If you edit the text of this block, it will also be changed in 1 other block(s) where it appears. Make a copy"
  And I click on "Make a copy"
  And I should not see "If you edit the text of this block, it will also be changed in 1 other block(s) where it appears. Make a copy"
# 2.2 Check tinymce editor is displayed
  And I should see "Paragraph" in the "Tinymce editor menu" property
  And I should see "Note page1 block1 original title"
  And I should see "This is page1 block1 original content"

# 3. Edit title & content after copy selection updates them with original values
  And I set the following fields to these values:
  | Block title | Note page1 block2 title |
  | Block content | This is only a COPY of page1 block1 original content |
  And I press "Save"
  And I should see "This is page1 block1 original content" in the block "Note page1 block1 original title"
  And I should see "This is only a COPY of page1 block1 original content" in the block "Note page1 block2 title"

# 4. Set up Page2
  When I am on homepage
  And I follow "Page UserA_02"
# 5. Create a Page2 note1 with the (original, unedited) Page1 note1 content
  And I follow "Edit"
  When I follow "Drag to add a new block" in the "blocktype sidebar" property
  And I press "Add"
  And I click on blocktype "Note"
  And I follow "Use content from another note"
  And I select the radio "Note page1 block1 original title"
  And I should see "If you edit the text of this block, it will also be changed in 1 other block(s) where it appears. Make a copy"
  And I set the following fields to these values:
  | Block title | Note page2 block1 using original note content title |
  And I press "Save"
  And I scroll to the top
  And I should see "This is page1 block1 original content" in the block "Note page2 block1 using original note content title"

# 6. Create a Page2 note2 with the Page1 note1 content, edit the content of ALL Page1 note1s then CANCEL the changes
  When I follow "Drag to add a new block" in the "blocktype sidebar" property
  And I press "Add"
  And I click on blocktype "Note"
  And I follow "Use content from another note"
  And I select the radio "Note page1 block1 original title"
  And I should see "If you edit the text of this block, it will also be changed in 2 other block(s) where it appears. Make a copy"
  And I scroll to the base of id "instconf_edit_container"
  And I set the following fields to these values:
  | Edit all copies of this note | 1 |
  And I set the following fields to these values:
  | Block title | Note page2 block2 title - CANCEL original note changes |
  | Block content | This is page2 block2 original content change will be CANCELLED |
  And I close the dialog
  And I should see "This is page1 block1 original content" in the block "Note page2 block1 using original note content title"

# 7. Create a Page2 note2 with the Page1 note1 content, edit & Save the content of ALL Page1 note1s (ie Page1 note1, Page2 note1 and this one, Page 2 note2)
  When I follow "Drag to add a new block" in the "blocktype sidebar" property
  And I press "Add"
  And I click on blocktype "Note"
  And I follow "Use content from another note"
  And I select the radio "Note page1 block1 original title"
  And I should see "If you edit the text of this block, it will also be changed in 2 other block(s) where it appears. Make a copy"
  And I set the following fields to these values:
  | Block title | Note page2 block2 title AND update original title |
  | Block content | This is page1 block1 original content UPDATED NOW! |
  And I press "Save"
  And I scroll to the top
  And I should see "This is page1 block1 original content UPDATED NOW!" in the block "Note page2 block1 using original note content title"
  And I should see "This is page1 block1 original content UPDATED NOW!" in the block "Note page2 block2 title AND update original title"

# 8. Check that the original Page1 note1 content has been updated but the copied Page1 note2 content remains unchanged
  When I am on homepage
  And I follow "Page UserA_01"
  And I should see "This is page1 block1 original content UPDATED NOW!" in the block "Note page1 block1 original title"
  And I should see "This is only a COPY of page1 block1 original content" in the block "Note page1 block2 title"
