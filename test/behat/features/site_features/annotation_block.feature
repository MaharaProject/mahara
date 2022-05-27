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
 And I click on "Page admin_01"
 And I click on "Edit"
 When I click on the add block button
 And I click on "Add" in the "Add new block" "Blocks" property
 And I click on "Show more"
 And I click on "Show more"
 And I should not see "Annotation" in the "Content types" "Blocks" property
 And I display the page
# Navigating to admin block to turn it on
 And I choose "Plugin administration" in "Extensions" from administration menu
 And I click on "Show" in the "#activate_blocktype_annotation_submit_container" "css_element"
 And I am on homepage
# Editing page to add annotation block
 And I click on "Page admin_01"
 And I click on "Edit"
 When I click on the add block button
 And I click on "Add" in the "Add new block" "Blocks" property
 And I set the field "Block title" to "Annotation"
 And I click on blocktype "Annotation"
 And I click on "Save"
# Checking empty annotation message
 And I should see "This field is required"
 And I fill in "Please grade me" in editor "Annotation"
 And I click on "Save"
# Checking that the block saved by using the one thing on the page that changed.
 And I am on homepage
 And I click on "Page admin_01"
 And I should see "Annotation"
 And I should see "Please grade me"
