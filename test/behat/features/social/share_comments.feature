@javascript @core @share_comments
Feature: Added ID's for text blocks
In order for allow and moderate comments to save correctly
As an admin
I need to be able to save it once and not have to do it again in Edit Access screen

Scenario: Correctly saving access rules for allowing comments (Bug 1201174)

 #Logging in and Creating a group
 Given I log in as "admin" with password "Kupuhipa1"
 And I choose "Groups" from main menu
 Then I should see "My groups"
 When I follow "Create group"
 Then I should see "Create group"
 When I set the following fields to these values:
 | editgroup_name | Test Group1 |
 And I press "editgroup_submit"
 Then I should see "Group saved successfully"

 #Creating a page to share with group
 And I choose "Portfolio" from main menu
 Then I should see "Pages"
 And I follow "Add"
 And I click on "Page" in the dialog
 Then I should see "No title"
 When I set the following fields to these values:
 | settings_title | Group page1 |
 Then I press "Save"
 Then I should see "Page saved successfully"

 # Sharing Page
 When I follow "Share" in the "Toolbar buttons" property
 Then I should see "Edit access"
 When I set the following fields to these values:
 | accesslist[0][searchtype] | Test Group1 |
 And I press "Save"

 When I choose "Portfolio" from main menu
 And I follow "Group page1"
 And I follow "Edit this page"
 When I follow "Share" in the "Toolbar buttons" property
 And I scroll to the base of id "accessurl_more_open"
 And I follow "Advanced options"
 And I disable the switch "Allow comments"
 And I scroll to the id "accessurl_submit_container"
 And I press "Save"

 When I choose "Portfolio" from main menu
 And I follow "Group page1"
 And I follow "Edit this page"
 When I follow "Share" in the "Toolbar buttons" property
 And I scroll to the base of id "accessurl_more_open"
 And I check "accesslist[0][allowcomments]"
 And I press "Save"
 Then I should see "Share"

 # Checking if checked option worked
 When I choose "Portfolio" from main menu
 And I follow "Group page1"
 And I follow "Edit this page"
 When I follow "Share" in the "Toolbar buttons" property
 Then I should see "Edit access"
 And I expand the section "Advanced options"
 And the "accesslist[0][allowcomments]" checkbox should be checked
