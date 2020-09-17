@javascript @core @core_administration
Feature: Make sure 'framework' module is installed and we can add it
to a collection
In order to use SmartEvidence
As an admin
So I can benefit from the recording/marking of SmartEvidence in a
Mahara institution

Background:
  Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | admin |

  And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page admin_01 | Page A | user | admin |
    | Page admin_02 | Page B | user | admin |
    | Page admin_03 | Page C | user | admin |
    | Page admin_04 | Page D | user | admin |
    | Page admin_05 | Page E | user | admin |
    | Page admin_06 | Page F | user | admin |
    | Page admin_07 | Page G | user | admin |
    | Page admin_08 | Page H | user | admin |

  And the following "collections" exist:
    | title | description | ownertype | ownername | pages |
    | Collection admin_01 | This is collection A | user | admin | Page admin_01, Page admin_02, Page admin_03, Page admin_04, Page admin_05, Page admin_06, Page admin_07, Page admin_08 |

  And the following "permissions" exist:
    | title | accesstype |
    | Collection admin_01 | public |

Scenario: Installing framework module and activating for an institution
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Plugin administration" in "Extensions" from administration menu
 Then I should see "smartevidence"
 And I should see "Hide" in the "smartevidence" property
 # Also make sure the annotation blocktype plugin is active
 And I press "Show" in the "annotation" property

 # Make sure we have a matrix config form
 And I choose "SmartEvidence" in "Extensions" from administration menu
 And I follow "Import" in the "Arrow-bar nav" property
 And I attach the file "example.matrix" to "Matrix file"
 And I press "Upload matrix"

 # Check that we have new framework
 Then I should see "Title of your framework"

 # Activate smartevidence in an institution
 And I choose "Settings" in "Institutions" from administration menu
 And I click on "Edit" in "No Institution" row
 And I enable the switch "Allow SmartEvidence"
 And I press "Submit"
 Then I should see "Institution updated successfully."

 # Adding framework to existing collection
 And I choose "Pages and collections" in "Create" from main menu
 And I click on "Edit" in "Collection admin_01" card menu
 And I select "Title of your framework" from "SmartEvidence framework"
 And I press "Save"
 Then I should see "Collection saved successfully."

 # Testing the collection navigation and matrix carousel
 And I choose "Pages and collections" in "Create" from main menu
 And I click the card "Collection admin_01"
 And I should see "You are on page 1/9"
 And I should see "by Admin Account (admin)"
 And I press "Next" in the "matrix table" property
 Then I should see "Page admin_06"
 And I press "Prev" in the "matrix table" property
 Then I should not see "Page admin_06"

 # Click on a matrix point to add an annotation
 And I click on the matrix point "4,5"
 And I fill in "My two cents" in editor "Annotation"
 And I press "Save"
 And I go to portfolio page "Page admin_02"
 Then I should see "Annotation"

 # Add another compentency annotation block
 And I follow "Edit"
 When I follow "Drag to add a new block" in the "blocktype sidebar" property
 And I press "Add"
 And I click on blocktype "Annotation"
 And I fill in "My three cents" in editor "Annotation"
 And I set the select2 value "1.1 - Sub level of the standard" for "instconf_smartevidence"
 And I press "Save"

 # Re-click a matrix point to add some feedback
 And I choose "Pages and collections" in "Create" from main menu
 And I click the card "Collection admin_01"
 And I click on the matrix point "4,5"
 And I fill in "This is annotation feedback" in editor "Feedback"
 And I press "Place feedback"
 # And change assessment status
 And I should not see the field "Assessment"
 And I close the dialog
 And I log out

 # Try as another admin
 Given  I log in as "UserA" with password "Kupuh1pa!"
 And I wait "1" seconds
 And I follow "Collection admin_01"
 And I click on the matrix point "4,5"
 And I wait "1" seconds
 And I select "Partially meets the standard" from "Assessment"
 And I press "Save"
 Then I should see "SmartEvidence updated"
