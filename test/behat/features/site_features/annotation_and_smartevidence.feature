@javascript @core @core_portfolio
Feature: Annotation via SmartEvidence map
 In order to verify the empty annotations are disabled
 As an admin
 So I can annotate people's work

Background:
    Given the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page admin_01 | Page 01 | user | admin |
    | Page admin_02 | Page 02 | user | admin |
    | Page admin_03 | Page 03 | user | admin |
    | Page admin_04 | Page 04 | user | admin |

    And the following "collections" exist:
    | title | description | ownertype | ownername | pages |
    | Collection admin_01 | Collection 01 | user | admin | Page admin_01, Page admin_02, Page admin_03, Page admin_04 |

Scenario: Accessing annotation block
 Given I log in as "admin" with password "Kupuh1pa!"
 # Navigating to admin block to turn it on
 And I choose "Plugin administration" in "Extensions" from administration menu
 And I click on "Show" in the "#activate_blocktype_annotation_submit_container" "css_element"
 And I should see "Hide" in the "Smartevidence" "Smartevidence" property
 And I choose "Settings" in "Institutions" from administration menu
 And I click on "Edit"
 And I enable the switch "SmartEvidence"
 And I click on "Submit"

 # Make sure we have a matrix config form
 And I choose "SmartEvidence" in "Extensions" from administration menu
 And I click on "Import" in the "Arrow-bar nav" "Nav" property
 And I attach the file "example.matrix" to "Matrix file"
 And I click on "Upload matrix"

 # Check that we have new framework
 Then I should see "Title of your framework"

 # Update 'Test collection' to have SmartEvidence
 And I choose "Portfolios" in "Create" from main menu
 And I click on "Configure" in "Collection admin_01" card menu
 And I select "Title of your framework" from "SmartEvidence framework"
 And I click on "Continue"
 And I click on "Collection admin_01"

 # Click the matrix point and test empty annotation message
 And I click on the matrix point "4,5"
 And I wait "1" seconds
 And I click on "Save"
 And I should see "This field is required"
