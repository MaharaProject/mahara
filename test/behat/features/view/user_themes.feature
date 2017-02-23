@javascript @core @core_view
Feature: Allow user themes on pages
In order to allow a user theme on a page
As an admin
I need to be able to activate Users can choose page themes setting

Scenario: Activate page themes setting and edit a page (Bug 1591304)
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 # I set the page themes option
 And I follow "Configure site"
 And I expand the section "User settings"
 And I enable the switch "Users can choose page themes"
 And I press "Update site options"
 # Now edit a page
 And I follow "Return to site"
 And I follow "Portfolio"
 And I follow "Add"
 And I click on "Page" in the dialog
 And I press "Save"
 And I follow "Text"
 And I press "Add"
 And I press "Save"