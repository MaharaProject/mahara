@javascript @core @core_content
Feature: Adding tags and Verifying them
   In order to add a tag
   As an admin I need to be able to go Content
   So I can create a file and tag


Scenario: Adding tags to files (Bug 1426983)
   # Log in as the admin user
   Given I log in as "admin" with password "Password1"
   # Verifying log in was successful
   And I should see "Admin User"
   # Creating a folder with a  tag
   When I choose "Files" in "Content"
   And I set the following fields to these values:
   |Folder name | folder1 |
   # Pressing create folder button
   And I press "Create folder"
   # Editing the folder
   And I press "edit_6"
   And I set the following fields to these values:
   | Description | This is a subdirectory |
   | Tags | folder |
   And I press "Save changes"
   #Creating a Journal with tag
   And I choose "Journal" in "Content"
   And I follow "New entry"
   And I set the following fields to these values:
   | Title *  | Journal one  |
   | Tags   | journal, test   |
   | Entry | This is a test |
   And I press "Save entry"
   # Creating a Plan with a tag
   And I choose "Plans" in "Content"
   And I follow "New plan"
   And I fill in the following:
   | Title *  | Plan 9 from outer space  |
   | Description  | Woooo |
   | Tags   | plan, test   |
   And I press "Save plan"
   # Creating a Task with a tag
   And I follow "New task"
   And I fill in the following:
   | Title * | Task one   |
   | Completion date * | 2020/12/31   |
   | Tags   | task, test |
   And I press "Save task"
   # Creating page 1 with a tag
   And I follow "Portfolio"
   And I press "Create page"
   And I set the following fields to these values:
   | Page title * | Test page 1   |
   | Tags       | page, test    |
   And I press "Save"
   And I press "Done"
   # Creating page 2 with a tag
   And I press "Create page"
   And I set the following fields to these values:
   | Page title *    | Testing page 2    |
   | Tags          | page, test        |
   And I press "Save"
   # Creating a Note with a tag
   And I expand "General" node
   And I wait until the page is ready
   And I follow "Note"
   And I press "Add"
   And I fill in "Tags" with "box, test"
   And I press "Save"
   And I press "Done"
   Then I follow "Tags"
   # Verifying tags are saved
   And I should see "Note"
   And I should see "Journal entry"
   And I should see "Plan"
