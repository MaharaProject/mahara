@javascript @core @core_administration
Feature: Enable/disable external resources to display in pages
In order to control external resources displayed in mahara pages
As an admin
For security reason, I can enable/disable the setting "Disable external resources" in
"Site configuration" page

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | Page 1 | This is the page | user | userA |

Scenario: Enable/disable external resources in mahara pages
# By default external resources are allowed in a page
 When I log in as "userA" with password "Kupuhipa1"
 # Upload an image
 And I choose "Files" in "Content"
 And I attach the file "Image2.png" to "files_filebrowser_userfile"
 And I choose "Pages" in "Portfolio"
 And I follow "Page 1"
 And I follow "Edit this page"

 # Add a "Text" block with an image from file area
 And I follow "Text"
 And I press "Add"
 And I set the following fields to these values:
 | Block title | Text block with an internal image |
 And I click the "Insert/edit image" button in the editor
 And I expand the section "Image"
 And I follow "Image2.png"
 And I press "Submit"
 And I press "Save"

 # Add a "Text" block with an embedded external image
 And I follow "Text"
 And I press "Add"
 And I set the following fields to these values:
 | Block title | Text block with an external image |
 | Block content | <p><img title="Open source logo" src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/Opensource.svg/744px-Opensource.svg.png" alt="" width="300" /></p> |
 And I press "Save"
 And I scroll to the id "main-nav"
 And I follow "Display page"
 Then I should see images in the block "Text block with an internal image"
 And I should see images in the block "Text block with an external image"
 And I log out

# Disable external resources and check if external images are displayed in pages
 When I log in as "admin" with password "Kupuhipa1"
 And I choose "Administration"
 And I choose "Site options" in "Configure site"
 And I expand the section "Security settings"
 And I enable the switch "Disable external resources in user HTML"
 And I press "Update site options"
 And I log out
 And I log in as "userA" with password "Kupuhipa1"
 And I choose "Pages" in "Portfolio"
 And I follow "Page 1"
 Then I should see images in the block "Text block with an internal image"
 And I should not see images in the block "Text block with an external image"