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
 Given I log in as "admin" with password "Kupuhipa1"
# Checking if annotation block is available by default
 And I follow "Page admin_01"
 And I follow "Edit"
 And I expand "General" node
 And I should not see "Annotation"
 And I display the page
# Navigating to admin block to turn it on
 And I choose "Plugin administration" in "Extensions" from administration menu
 And I press "activate_blocktype_annotation_submit"
 And I am on homepage
# Editing page to add annotation block
 And follow "Page admin_01"
 And I follow "Edit"
 And I expand "General" node
 And I follow "Annotation"
 And I press "Add"
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
