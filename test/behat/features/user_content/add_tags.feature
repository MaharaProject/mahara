@javascript @core @core_content
Feature: Adding tags and Verifying them
In order to add a tag
As an UserA I need to be able to go Content
So I can create a file and tag

Background:
  Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1 | test01@example.com | Angela | User | mahara | internal | member |

  And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01 | user | UserA |
     | Page UserA_02 | Page 02 | user | UserA |

  Scenario: Adding tags to files (Bug 1426983)
   # Log in as the UserA
   Given I log in as "UserA" with password "Kupuhipa1"
   # Creating a folder with a  tag
   When I choose "Files" in "Content" from main menu
   And I set the following fields to these values:
   | Create folder | folder1 |
   # Pressing create folder button
   And I scroll to the base of id "files_filebrowser_createfolder"
   And I press "Create folder"
   # Editing the folder
   And I press "Edit folder \"folder1\""
   And I set the following fields to these values:
   | Description | This is a subdirectory |
   And I fill in select2 input "files_filebrowser_edit_tags" with "red" and select "red"
   And I press "Save changes"
   #Creating a Journal with tag
   And I choose "Journals" in "Content" from main menu
   And I follow "New entry"
   And I set the following fields to these values:
   | Title *  | Journal one  |
   | Entry | This is a test |
   And I scroll to the base of id "editpost_tags_container"
   And I fill in select2 input "editpost_tags" with "blue" and select "blue"
   And I press "Save entry"
   # Creating a Plan with a tag
   And I choose "Plans" in "Content" from main menu
   And I follow "New plan"
   And I fill in the following:
   | Title *  | Plan 9 from outer space  |
   | Description  | Woooo |
   And I scroll to the base of id "addplan_tags_container"
   And I fill in select2 input "addplan_tags" with "blue" and select "blue"
   And I press "Save plan"
   # Creating a Task with a tag
   And I follow "New task"
   And I fill in the following:
   | Title * | Task one   |
   | Completion date * | 2020/12/31   |
   And I scroll to the base of id "addtasks_tags_container"
   And I fill in select2 input "addtasks_tags" with "blue" and select "blue"
   And I press "Save task"
   # Adding a tag to page 1
   And I choose "Pages and collections" in "Portfolio" from main menu
   And I click on "Edit" in "Page UserA_01" panel menu
   And I follow "Settings" in the "#toolbar-buttons" "css_element"
   And I fill in select2 input "settings_tags" with "blue" and select "blue"
   And I press "Save"
   # Adding a tag to page 2
   And I choose "Pages and collections" in "Portfolio" from main menu
   And I click on "Edit" in "Page UserA_02" panel menu
   And I follow "Settings" in the "#toolbar-buttons" "css_element"
   And I fill in select2 input "settings_tags" with "orange" and select "orange"
   And I press "Save"
   # Creating a Note with a tag
   And I expand "General" node
   And I follow "Note" in the "blocktype sidebar" property
   And I press "Add"
   And I fill in select2 input "instconf_tags" with "black" and select "black"
   And I press "Save"
   And I go to "/view/index.php"
   Then I follow "Tags"
   # Verifying tags are saved
   And I should see "blue"
   And I should see "black"
   And I should see "orange"
   And I should see "red"
   #Check the repeated tags
   And I follow "blue"
   And I should see "Journal one"
   And I should see "woooo"
   And I should see "task one"
   #Check single tag
   And I follow "orange"
   And I should see "Page UserA_02"
   And I should not see "Note"
   #Check tags can be deleted from a page - Bug 1715491
   Given I follow "Page UserA_02"
   And I follow "Edit"
   And I click on "Settings" in the "Toolbar buttons" property
   And I clear value "orange (1)" from select2 field "settings_tags"
   And I press "Save"
   And I display the page
   Then I should not see "orange"
