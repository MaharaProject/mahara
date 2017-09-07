@javascript @core @core_portfolio
Feature: Annotation via smart evnidence map
 In order to verify the empty annotations are disabled
 As an admin
 So I can annotate people's work

Background:
    Given the following "pages" exist:
    | title | description| ownertype | ownername |
    | Page 01 | Admin's page 01 | user | admin |
    | Page 02 | Admin's page 02 | user | admin |
    | Page 03 | Admin's page 03 | user | admin |
    | Page 04 | Admin's page 04 | user | admin |

    And the following "collections" exist:
    | title | description| ownertype | ownername | pages |
    | Test collection | This is the collection 01 | user | admin | Page 01, Page 02, Page 03, Page 04 |

Scenario: Accessing annotation block
 Given I log in as "admin" with password "Kupuhipa1"
 # Navigating to admin block to turn it on
 And I choose "Plugin administration" in "Extensions" from administration menu
 And I press "activate_blocktype_annotation_submit"
 And I should see "Hide" in the "form#activate_module_framework" "css_element"
 And I choose "Settings" in "Institutions" from administration menu
 And I press "Edit"
 And I enable the switch "Allow SmartEvidence"
 And I press "Submit"

 # Make sure we have a matrix config form
 And I choose "SmartEvidence" in "Extensions" from administration menu
 And I follow "Add framework"
 And I attach the file "example.matrix" to "Matrix file"
 And I press "Upload matrix"

 # Check that we have new framework
 Then I should see "Title of your framework"

 # Update 'Test collection' to have smart evidence
 And I choose "Pages and collections" in "Portfolio" from main menu
 And I click on "Test collection" panel menu
 And I click on "Edit" in "Test collection" panel menu
 And I select "Title of your framework" from "SmartEvidence framework"
 And I press "Save"
 And follow "Test collection"

 # Click the matrix point and test empty annotation message
 And I click on the matrix point "3,4"
 And I press "Save"
 And I should see "This field is required"
