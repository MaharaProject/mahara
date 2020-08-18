@javascript @core @core_group
Feature: Notification when a user is about to reach their quota
    In order to verify notification when reaching a quota
    As an admin create users
    So I can change their quota limit and verify notification

Background:
    Given the following "users" exist:
    | username  | password  | email | firstname | lastname  | institution   | authname  |role   |
    | UserA   | Kupuh1pa!   | UserA@example.org   | Angela   | User | mahara    | internal  | member    |

Scenario: When quota notification threshold is changed, send notifications to users who are now over threshold (Bug 1367539)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"
    # Modifying account quota to 2MB
    And I go to the "artefact" plugin "file" configuration "file" type
    And I follow "Default account quota"
    And I fill in "Default quota" with "2"
    # Update already existing accounts
    And I enable the switch "Update account quotas"
    # Modifying quota notification threshold to fifty
    And I fill in "Quota notification threshold" with "50"
    And I press "Save"
    # Verifying changes were made
    And I should see "Settings saved"
    # Log out as "Admin user"
    And I log out
    # Log in as user 1
    When I log in as "UserA" with password "Kupuh1pa!"
    # Upload files to reach quota threshold of 50%
    And I choose "Files" in "Create" from main menu
     # Verify Quota is displayed on the sidebar of the Content->Files page and default maximum of 50.0MB
    And I should see "You have used 0.0MB of your 2.0MB quota."
    # Verify folder shows content size
    When I fill in "Folder1" for "files_filebrowser_createfolder_name"
    And I press "Create folder"
    And I follow "Folder1"
    And I attach the file "Image1.jpg" to "File"
    And I attach the file "Image2.png" to "File"
    And I attach the file "Image3.png" to "File"
    And I follow "Parent folder"
    Then I should see "1.5M"
    And I should see "You have used 1.5MB of your 2.0MB quota."
    # Verifying notification for reaching account quota threshold have been received
    And I am on homepage
    And I choose inbox
    # Regression testing for previous errors
    And I should see "Your file storage is almost full"