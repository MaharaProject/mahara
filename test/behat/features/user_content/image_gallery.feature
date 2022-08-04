@javascript @core
Feature:

Background:
  Given the following "users" exist:
  | username | password | email | firstname | lastname | institution | authname | role |
  | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

  And the following "pages" exist:
  | title | description | ownertype | ownername |
  | Page UserA_01 | Page 01 | user | UserA |

Scenario:
  Given I log in as "UserA" with password "Kupuh1pa!"
  When I choose "Files" in "Create" from main menu
  And I fill in "Folder1" for "files_filebrowser_createfolder_name"
  And I click on "Create folder"
  And I click on "Folder1"
  And I attach the file "Image1.jpg" to "File"
  And I attach the file "Image2.png" to "File"
  And I attach the file "Image3.png" to "File"
  # check that folder size is displayed after uploading 3 images
  And I reload the page
  And I should see "1.5M" in the "Folder1" row
  # Creating folder 1
  Given I choose "Portfolios" in "Create" from main menu
  And I click on "Edit" in "Page UserA_01" card menu
  When I click on the add block button
  And I click on "Add" in the "Add new block" "Blocks" property
  And I click on blocktype "Image gallery"
  And I set the field "Block title" to "Image gallery"
  And I select the radio "Image selection: Display all images from a folder including images uploaded later"
  And I click on the "Select" "Files" property
  And I select the radio "Style: Thumbnails (square)"
  And I click on "Save" in the "Submission" "Modal" property
  And I display the page
  Then I should see images within the block "Image gallery"
