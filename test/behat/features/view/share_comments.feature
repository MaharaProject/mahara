@javascript @core @share_comments
Feature: Added ID's for text blocks
In order for allow and moderate comments to save correctly
As an admin
I need to be able to save it once and not have to do it again in Edit Access screen

Scenario: Correctly saving access rules for allowing comments (Bug 1201174)

 #Logging in and Creating a group
 Given I log in as "admin" with password "Kupuhipa1"
 Then I follow "Groups"
 Then I should see "My groups"
 When I follow "Create group"
 Then I should see "Create group"
 When I set the following fields to these values:
 | editgroup_name | Test Group1 |
 And I press "editgroup_submit"
 Then I should see "Group saved successfully"

 #Creating a page to share with group
 When I follow "Portfolio"
 Then I should see "Pages"
 When I press "createview_submit"
 Then I should see "No title"
 When I set the following fields to these values:
 | editview_title | Group page1 |
 And I wait "5" seconds
 Then I press "editview_submit"
 Then I should see "Page saved successfully"

 # Sharing Page
 When I follow "Share page"
 Then I should see "Edit access"
 When I set the following fields to these values:
 | accesslist[0][searchtype] | Test Group1 |
 Then I follow "Advanced options"
 And I set the following fields to these values:
 |Allow comments | 0 |
 Then I should see "Comments" in the "table#accesslisttable" "css_element"
 When I check "accesslist[0][allowcomments]"
 And I press "Save"
 Then I should see "Share"

 # Checking if checked option worked
 When I click on "Edit access" in "Group page1" row
 Then I should see "Edit access"
 And the "accesslist[0][allowcomments]" checkbox should be checked
