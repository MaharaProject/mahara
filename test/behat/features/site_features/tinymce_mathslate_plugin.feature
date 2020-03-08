@javascript @core @core_administration
Feature: TinyMCE mathslate plugin
In order to view mathslate plugin
As an admin
I need to be able to access mathslate in Tinymce

Background:
 Given the following "pages" exist:
  | title | description | ownertype | ownername |
  | Page mahara_01 | Page 01 | institution | mahara |

Scenario: Making adjustments to the mathslate plugin for mahara (Bug 1472446)
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Site options" in "Configure site" from administration menu
 And I follow "Site settings"
 And I enable the switch "Enable MathJax"
 And I press "Update site options"
 And I choose "Pages and collections" in "Configure site" from administration menu
 And I follow "Page mahara_01"
 # Tinymce field adding a math equation
 And I scroll to the id "feedbacktable"
 And I fill in "\\[\\alpha A\\beta B\\]" in editor "Comment"
 And I press "Comment"
 And I choose "Pages and collections" in "Configure site" from administration menu
 And I follow "Page mahara_01"
 And I wait "1" seconds
 And I should see "αAβB"
