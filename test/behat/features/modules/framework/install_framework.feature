@javascript @core @core_administration
Feature: Make sure 'framework' module is installed and active
In order to use SmartEvidence
As an admin
So I can benefit from the recording/marking of SmartEvidence in a
Mahara institution

Scenario: Installing framework module and actoivating for an institution
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
