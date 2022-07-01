@javascript @core @core_administration
Feature: Enable/disable external resources to display in pages
In order to control external resources displayed in mahara pages
As an admin
For security reason, I can enable/disable the setting "Disable external resources" in
"Site configuration" page

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |
    | pcnz | Institution One | ON | OFF |

    And the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
    And the following "pages" exist:
      | title | description | ownertype | ownername |
      | Page UserA_01 | Page 01| user | UserA |

Scenario: Enable/disable external resources in mahara pages
 # By default external resources are allowed in a page
 When I log in as "UserA" with password "Kupuh1pa!"
 # Upload an image
 And I choose "Files" in "Create" from main menu
 And I attach the file "Image2.png" to "files_filebrowser_userfile"
 And I choose "Pages and collections" in "Create" from main menu
 And I follow "Page UserA_01"
 And I press "Edit"

 # Add a "Text" block with an image from file area
 When I click on the add block button
 And I press "Add"
 And I click on blocktype "Text"
 And I set the following fields to these values:
 | Block title | Text block with an internal image |
 And I click the "Insert/edit image" button in the editor "text"
 And I expand the section "Image"
 And I press "Select \"Image2.png\""
 And I scroll to the base of id "imgbrowserconf_action_submitimage"
 And I press "Submit"
 And I wait "1" seconds
 And I press "Save"

 # Add a "Text" block with an embedded external image
 When I click on the add block button
 And I press "Add"
 And I click on blocktype "Text"
 And I set the following fields to these values:
 | Block title | Text block with an external image |
 | Block content | <p><img title="Open source logo" src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/Opensource.svg/744px-Opensource.svg.png" alt="" width="300" /></p> |
 And I press "Save"
 And I display the page
 Then I should see images within the block "Text block with an internal image"
 And I should see images within the block "Text block with an external image"
 And I log out

# Disable external resources and check if external images are displayed in pages
 When I log in as "admin" with password "Kupuh1pa!"
 And I choose "Site options" in "Configure site" from administration menu
 And I expand the section "Security settings"
 And I enable the switch "Disable external resources in HTML"
 And I press "Update site options"
 And I log out
 And I log in as "UserA" with password "Kupuh1pa!"
 And I choose "Pages and collections" in "Create" from main menu
 And I follow "Page UserA_01"
 Then I should see images within the block "Text block with an internal image"
 And I should not see images within the block "Text block with an external image"
