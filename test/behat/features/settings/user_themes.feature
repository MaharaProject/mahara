@javascript @core @core_view
Feature: Allow page themes
In order to allow an author to use a theme on a page
Enable "People can choose page themes" setting
Log in as a person and confirm it works

Background:
  Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.com | Angela | User | mahara | internal | member |
  And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |

Scenario: Activate page themes setting and edit a page (Bug 1591304)
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Site options" from administration menu
 # I set the page themes option
 And I expand the section "Account settings"
 And I enable the switch "Authors can choose page themes"
 And I click on "Update site options"
 And I log out
 # Now set a theme as an author and confirm logo changes
 Given I log in as "UserA" with password "Kupuh1pa!"
 And I click on "Page UserA_01"
 And I click on "Edit"
 And I click on "Configure" in the "Toolbar buttons" "Nav" property
 And I click on "Advanced"
 And I scroll to the id "settings_theme"
 And I select "Modern" from "theme"
 And I click on "Save"
 Then "/theme/modern/images/site-logo" should be in the "Logo" "Header" property
