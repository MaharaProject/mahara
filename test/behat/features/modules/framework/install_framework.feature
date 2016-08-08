@javascript @core @core_administration
Feature: Make sure 'framework' module is installed and we can add it
to a collection
In order to use SmartEvidence
As an admin
So I can benefit from the recording/marking of SmartEvidence in a
Mahara institution

Background:
Given the following "pages" exist:
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


Scenario: Installing framework module and activating for an institution
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 And I choose "Plugin administration" in "Extensions"
 Then I should see "Hide" in the "form#activate_module_framework" "css_element"
 # Also make sure the annotation blocktype plugin is active
 And I press "Show" in the "form#activate_blocktype_annotation" "css_element"

 # Make sure we have a matrix config form
 And I scroll to the base of id "module.framework"
 And I follow "Configuration for module framework"
 Then I should see "Name of matrix file"

 # Activate smartevidence in an institution
 And I choose "Institutions" in "Institutions"
 And I click on "Edit" in "No Institution" row
 And I enable the switch "Allow SmartEvidence"
 And I press "Submit"
 Then I should see "Institution updated successfully."

 # Adding framework to existing collection
 And I follow "Return to site"
 And I choose "Collections" in "Portfolio"
 And I follow "Edit title and description"
 And I select "SmartEvidence" from "SmartEvidence framework"
 And I press "Save"
 Then I should see "Collection saved successfully."
