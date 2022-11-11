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

  And the following "blocks" exist:
    | title                     | type     | page                   | retractable | updateonly | data                                                |
    | Portfolios shared with me | newviews | Dashboard page: admin  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |
    | Portfolios shared with me | newviews | Dashboard page: UserA  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |

Scenario: Installing framework module and activating for an institution
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Plugin administration" in "Extensions" from administration menu
 Then I should see "smartevidence"
 And I should see "Hide" in the "Smartevidence" "Smartevidence" property
 # Also make sure the annotation blocktype plugin is active
 And I click on "Show" in the "Annotation" "Smartevidence" property

 # Make sure we have a matrix config form
 And I choose "SmartEvidence" in "Extensions" from administration menu
 And I click on "Import" in the "Arrow-bar nav" "Nav" property
 And I attach the file "example.matrix" to "Matrix file"
 And I click on "Upload matrix"

 # Check that we have new framework
 Then I should see "Title of your framework"

 # Activate smartevidence in an institution
 And I choose "Settings" in "Institutions" from administration menu
 And I click on "Edit" in "No Institution" row
 And I enable the switch "SmartEvidence"
 And I click on "Submit"
 Then I should see "Institution updated successfully."

 # Adding framework to existing collection
 And I choose "Portfolios" in "Create" from main menu
 And I click on "Configure" in "Collection admin_01" card menu
 And I select "Title of your framework" from "SmartEvidence framework"
 And I click on "Continue"
 Then I should see "Collection saved successfully."

 # Testing the collection navigation and matrix carousel
 And I choose "Portfolios" in "Create" from main menu
 And I click the card "Collection admin_01"
 And I should see "You are on page 1/9"
 And I should see "by Admin Account (admin)"
 And I click on "Next" in the "matrix table" "Smartevidence" property
 Then I should see "Page admin_06"
 And I click on "Prev" in the "matrix table" "Smartevidence" property
 Then I should not see "Page admin_06"

 # Click on a matrix point to add an annotation
 And I click on the matrix point "4,5"
 And I fill in "My two cents" in editor "Annotation"
 And I click on "Save"
 And I go to portfolio page "Page admin_02"
 Then I should see "Annotation"

 # Add another compentency annotation block
 And I click on "Edit"
 When I click on the add block button
 And I click on "Add" in the "Add new block" "Blocks" property
 And I click on blocktype "Annotation"
 And I fill in "My three cents" in editor "Annotation"
 And I set the select2 value "1.1 - Sub level of the standard" for "instconf_smartevidence"
 And I click on "Save"

 # Re-click a matrix point to add some feedback
 And I choose "Portfolios" in "Create" from main menu
 And I click the card "Collection admin_01"
 And I click on the matrix point "4,5"
 And I fill in "This is annotation feedback" in editor "Feedback"
 And I click on "Place feedback"
 # And change assessment status
 And I should not see the field "Assessment"
 And I close the dialog
 And I log out

 # Try as another admin
 Given  I log in as "UserA" with password "Kupuh1pa!"
 And I wait "1" seconds
 And I click on "Collection admin_01"
 And I click on the matrix point "4,5"
 And I wait "1" seconds
 And I select "Partially meets the standard" from "Assessment"
 And I click on "Save"
 Then I should see "SmartEvidence updated"
