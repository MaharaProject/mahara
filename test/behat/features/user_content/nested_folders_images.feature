@javascript @core @core_artefact @core_content
Feature: Creating folders and subfolders with images inside
   In order to organise my files
   As an user I need to create folders with other folders inside
   So I can add files to them

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01 | user | UserA |

Scenario: Creating sub folder and attaching files (Bug 1426983)
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Creating folder 1
    When I choose "Files" in "Create" from main menu
    And I fill in "Folder1" for "files_filebrowser_createfolder_name"
    And I press "Create folder"
    And I follow "Folder1"
    # Creating subfolder inside Folder1
    And I scroll to the base of id "files_filebrowser_upload_container"
    And I fill in "Subfolder" for "files_filebrowser_createfolder_name"
    And I press "Create folder"
    # Uploading Image to Folder1
    And I attach the file "Image1.jpg" to "File"
    # Confirming upload was successful
    And I should see "Upload of Image1.jpg to Folder1 complete"
    # Going back to Home
    And I scroll to the base of id 'files_filebrowser_foldernav'
    And I follow "Home"
    # Creating Folder2
    And I scroll to the base of id "files_filebrowser_upload_container"
    And I fill in "Folder2" for "files_filebrowser_createfolder_name"
    And I press "Create folder"
    And I follow "Folder2"
    # Creatign Subfolder2
    And I scroll to the base of id "files_filebrowser_upload_container"
    And I fill in "Subfolder2" for "files_filebrowser_createfolder_name"
    And I press "Create folder"
    And I wait "1" seconds
    And I follow "Subfolder2"
    And I attach the file "Image3.png" to "File"
    # Confirming upload was successful
    And I should see "Upload of Image3.png to Subfolder2 complete"
    # Check the modal preview works
    And I follow "Image3.png"
    And I press "Close"
    # Confirming deletion of upload
    And I delete the "Image3.png" row
    And I should see "Image Image3.png deleted"
    # Verifying you can move from subfolder2 to Home
    And I scroll to the base of id 'files_filebrowser_foldernav'
    And I follow "Home"
    # Verifying all 2 folders are still there
    Then I should see "Folder1"
    And I should see "Folder2"

    # Check folder can be inserted into block and image displayed on a page(Bug 1679886)
    # this could be expanded to check the other folder block options
    Given I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    When I follow "Drag to add a new block" in the "blocktype sidebar" "Views" property
    And I press "Add"
    And I click on blocktype "Folder"
    And I expand "Folders" node
    And I click on "Select" in "Folder1" row
    # Set the block title to blank so it uses the name of the folder
    And I set the field "Block title" to ""
    And I press "Save"
    And I display the page
    Then I should see images within the block "Folder1"
