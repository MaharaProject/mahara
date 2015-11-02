@javascript @core @core_content
Feature: Adding tags and Verifying them
   In order to add a tag
   As an admin I need to be able to go Content
   So I can create a file and tag


Scenario: Adding tags to files (Bug 1426983)
   # Log in as the admin user
   Given I log in as "admin" with password "Kupuhipa1"
   # Verifying log in was successful
   And I should see "Admin User"
   # Creating a folder with a  tag
   When I choose "Files" in "Content"
   And I set the following fields to these values:
   | Create folder | folder1 |
   # Pressing create folder button
   And I press "Create folder"
   # Editing the folder
   And I press "Edit folder \"folder1\""
   And I set the following fields to these values:
   | Description | This is a subdirectory |
   And I fill in select2 input "files_filebrowser_edit_tags" with "folder" and select "folder"
   And I press "Save changes"
   #Creating a Journal with tag
   And I choose "Journals" in "Content"
   And I follow "New entry"
   And I set the following fields to these values:
   | Title *  | Journal one  |
   | Entry | This is a test |
   And I fill in select2 input "editpost_tags" with "journal" and select "journal"
   And I scroll to the base of id "editpost_tags_container"
   And I fill in select2 input "editpost_tags" with "test" and select "test"
   And I press "Save entry"
   # Creating a Plan with a tag
   And I choose "Plans" in "Content"
   And I follow "New plan"
   And I fill in the following:
   | Title *  | Plan 9 from outer space  |
   | Description  | Woooo |
   And I fill in select2 input "addplan_tags" with "plan" and select "plan"
   And I fill in select2 input "addplan_tags" with "test" and select "test"
   And I press "Save plan"
   # Creating a Task with a tag
   And I follow "New task"
   And I fill in the following:
   | Title * | Task one   |
   | Completion date * | 2020/12/31   |
   And I fill in select2 input "addtasks_tags" with "task" and select "task"
   And I scroll to the base of id "addtasks_tags_container"
   And I fill in select2 input "addtasks_tags" with "test" and select "test"
   And I press "Save task"
   # Creating page 1 with a tag
   And I follow "Portfolio"
   And I press "Create page"
   And I set the following fields to these values:
   | Page title * | Test page 1   |
   And I fill in select2 input "editview_tags" with "page" and select "page"
   And I fill in select2 input "editview_tags" with "test" and select "test"
   And I press "Save"
   And I follow "Portfolio"
   # Creating page 2 with a tag
   And I press "Create page"
   And I set the following fields to these values:
   | Page title *    | Testing page 2    |
   And I fill in select2 input "editview_tags" with "page" and select "page"
   And I fill in select2 input "editview_tags" with "test" and select "test"
   And I press "Save"
   # Creating a Note with a tag
   And I expand "General" node
   And I wait "1" seconds
   And I follow "Note" in the "div#general" "css_element"
   And I press "Add"
   And I fill in select2 input "instconf_tags" with "box" and select "box"
   And I fill in select2 input "instconf_tags" with "test" and select "test"
   And I press "Save"
   And I go to "/view/index.php"
   And I wait "1" seconds
   Then I follow "Tags"
   # Verifying tags are saved
   And I should see "Note"
   And I should see "Journal entry"
   And I should see "Plan"
