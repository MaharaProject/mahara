@javascript @core @core_administration
Feature: TinyMCE mathslate plugin
In order to view mathslate plugin
As an admin
I need to be able to access mathslate in Tinymce

Scenario: Making adjustments to the mathslate plugin for mahara (Bug 1472446)
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 And I follow "Configure site"
 And I follow "General settings"
 And I enable the switch "Enable MathJax"
 And I press "Update site options"
 And I should see "Site options have been updated."
 And I choose "Pages" in "Configure site"
 And I press "Create page"
 And I set the following fields to these values:
   | Page title | test |
   | Page description | testing |
 And I press "Save"
 And I should see "Page saved successfully"
 And I wait "1" seconds
 And I choose "Pages" in "Configure site"
 And I follow "test"
 # Tinymce field adding a math equation
 And I fill in "\\[\\alpha A\\beta B\\]" in WYSIWYG editor "add_feedback_form_message_ifr"
 And I press "Comment"
 And I wait "1" seconds
 And I should see "αAβB"
 And I should see "Feedback submitted"
