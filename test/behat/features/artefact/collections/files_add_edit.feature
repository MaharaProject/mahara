@javascript @core @core_content
Feature: Creating folders
   In order to fill in a folder with content
   As an admin I need to create a folder with other folders inside
   So I can add files to it


Scenario: Creating sub folder and attaching files (Bug 1426983)
    # Log in as "Admin user"
    Given I log in as "admin" with password "Password1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Creating folder 1
    When I choose "Files" in "Content"
    And I fill in "Folder1" for "files_filebrowser_createfolder_name"
    And I press "Create folder"
    And I follow "Folder1"
    # Creating subfolder inside Folder1
    And I fill in "Subfolder" for "files_filebrowser_createfolder_name"
    And I press "Create folder"
    # Uploading Image to Folder1
    And I attach the file "Image1.jpg" to "Upload file"
    # Confirming upload was successful
    And I should see "Upload of Image1.jpg to Folder1 complete"
    # Going back to Home
    And I follow "Home"
    # Creating Folder2
    And I fill in "Folder2" for "files_filebrowser_createfolder_name"
    And I press "Create folder"
    And I follow "Folder2"
    # Creatign Subfolder2
    And I fill in "Subfolder2" for "files_filebrowser_createfolder_name"
    And I press "Create folder"
    And I follow "Subfolder2"
    And I attach the file "Image3.png" to "Upload file"
    # Confirming upload was successful
    And I should see "Upload of Image3.png to Subfolder2 complete"
    # Verifying you can move from subfolder2 to Home
    And I follow "Home"
    # Verifying all 2 folders are still there
    Then I should see "Folder1"
    And I should see "Folder2"
