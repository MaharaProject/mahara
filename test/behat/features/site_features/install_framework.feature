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
    | userA | Kupuhipa1 | test01@example.org | Pete | Mc | mahara | internal | admin |

  And the following "pages" exist:
    | title | description| ownertype | ownername |
    | PageA | This is page A | user | admin |
    | PageB | This is page B | user | admin |
    | PageC | This is page C | user | admin |
    | PageD | This is page D | user | admin |
    | PageE | This is page E | user | admin |
    | PageF | This is page F | user | admin |
    | PageG | This is page G | user | admin |
    | PageH | This is page H | user | admin |

  And the following "collections" exist:
    | title | description| ownertype | ownername | pages |
    | CollA | This is collection A | user | admin | PageA, PageB, PageC, PageD, PageE, PageF, PageG, PageH |

  And the following "permissions" exist:
    | title | accesstype |
    | CollA | public |

Scenario: Installing framework module and activating for an institution
 Given I log in as "admin" with password "Kupuhipa1"
 And I choose "Plugin administration" in "Extensions" from administration menu
 Then I should see "smartevidence"
 And I should see "Hide" in the "form#activate_module_framework" "css_element"
 # Also make sure the annotation blocktype plugin is active
 And I press "Show" in the "form#activate_blocktype_annotation" "css_element"

 # Make sure we have a matrix config form
 And I choose "SmartEvidence" in "Extensions" from administration menu
 And I follow "Add framework"
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
 And I choose "Pages and collections" in "Portfolio" from main menu
 And I click on "CollA" panel menu
 And I click on "Edit" in "CollA" panel menu
 And I select "Title of your framework" from "SmartEvidence framework"
 And I press "Save"
 Then I should see "Collection saved successfully."

 # Testing the collection navigation and matrix carousel
 And I choose "Pages and collections" in "Portfolio" from main menu
 And I click the panel "CollA"
 And I should see "You are on page 1/9"
 And I should see "by Admin User (admin)"
 And I press "Next" in the "table#tablematrix" "css_element"
 Then I should see "PageF"
 And I press "Prev" in the "table#tablematrix" "css_element"
 Then I should not see "PageF"

 # Click on a matrix point to add an annotation
 And I click on the matrix point "3,4"
 And I fill in "My two cents" in editor "Annotation"
 And I press "Save"
 And I go to portfolio page "PageB"
 Then I should see "Annotation"

 # Add another compentency annotation block
 And I follow "Edit"
 And I expand "General" node
 And I follow "Annotation"
 And I press "Add"
 And I fill in "My three cents" in editor "Annotation"
 And I set the select2 value "1.1 - Sub level of the standard" for "instconf_smartevidence"
 And I press "Save"

 # Re-click a matrix point to add some feedback
 And I choose "Pages and collections" in "Portfolio" from main menu
 And I click the panel "CollA"
 And I click on the matrix point "3,4"
 And I fill in "This is annotation feedback" in editor "Feedback"
 And I press "Place feedback"
 # And change assessment status
 And I should not see the field "Assessment"
 And I press "Save"
 And I log out

 # Try as another admin
 Given  I log in as "userA" with password "Kupuhipa1"
 And I follow "CollA"
 And I click on the matrix point "3,4"
 And I wait "1" seconds
 And I select "Partially meets the standard" from "Assessment"
 And I press "Save"
 Then I should see "SmartEvidence updated"
