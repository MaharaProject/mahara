@javascript @core @core_portfolio
Feature: Annotation block is off by default
 In order to verfify the annotation block is off by default but is still accessible
 As an admin
 So I can annotate people's work

Scenario: Accessing annotation block (Bug 1443730)
 Given I log in as "admin" with password "Kupuhipa1"
 # Creating a page
 And I choose "Pages" in "Portfolio"
 And I press "Create page"
 And I fill in "My page is amazing" for "Page title *"
 And I press "Save"
# Checking if annotation block is there
 And I expand "General" node
 And I should not see "Annotation"
 And I follow "Display page"
# Navigating to admin block to turn it on
 And I follow "Administration"
 And I choose "Plugin administration" in "Extensions"
 And I press "activate_blocktype_annotation_submit"
 And I am on homepage
# Editing page to enable annotation block
 And follow "My page is amazing"
 And I follow "Edit"
 And I expand "General" node
 And I wait "2" seconds
 And I follow "Annotation"
 And I press "Add"
 And I press "Save"
# Checking that the block saved by using the one thing on the page that changed.
 And I follow "Display page"
 And I should see "Annotation"
 And I should see "My page is amazing"

