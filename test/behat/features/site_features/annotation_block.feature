@javascript @core @core_portfolio
Feature: Annotation block is off by default
 In order to verfify the annotation block is off by default but is still accessible
 As an admin
 So I can annotate people's work

Background:
 Given the following "pages" exist:
  | title | description | ownertype | ownername |
  | Page admin_01 | Page 01 | admin | admin |

Scenario: Accessing annotation block (Bug 1443730)
 Given I log in as "admin" with password "Kupuh1pa!"
# Checking if annotation block is available by default
 And I follow "Page admin_01"
 And I follow "Edit"
 When I follow "Drag to add a new block" in the "blocktype sidebar" property
 And I press "Add"
 And I click on "Show more"
 And I click on "Show more"
 And I should not see "Annotation" in the "Content types" property
 And I display the page
# Navigating to admin block to turn it on
 And I choose "Plugin administration" in "Extensions" from administration menu
 And I press "activate_blocktype_annotation_submit"
 And I am on homepage
# Editing page to add annotation block
 And follow "Page admin_01"
 And I follow "Edit"
 When I follow "Drag to add a new block" in the "blocktype sidebar" property
 And I press "Add"
 And I set the field "Block title" to "Annotation"
 And I click on "Show more"
 And I click on "Show more"
 And I click on "Annotation" in the "Content types" property
 And I press "Save"
# Checking empty annotation message
 And I should see "This field is required"
 And I fill in "Please grade me" in editor "Annotation"
 And I press "Save"
# Checking that the block saved by using the one thing on the page that changed.
 And I am on homepage
 And follow "Page admin_01"
 And I should see "Annotation"
 And I should see "Please grade me"
