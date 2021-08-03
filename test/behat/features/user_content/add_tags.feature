@javascript @core @core_content
Feature: Adding tags and Verifying them
In order to add a tag
As an UserA I need to be able to go Content
So I can create a file and tag

Background:
  Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | test01@example.com | Angela | User | mahara | internal | member |
     | UserB | Kupuh1pa! | test02@example.com | Albert | User | mahara | internal | member |

  And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01 | user | UserA |
     | Page UserA_02 | Page 02 | user | UserA |

  And the following "blocks" exist:
     | title                     | type     | page                   | retractable | updateonly | data                                                |
     | Latest changes I can view | newviews | Dashboard page: UserA  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |
     | Latest changes I can view | newviews | Dashboard page: UserB  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |

 Scenario: Adding tags to files (Bug 1426983)
   # Log in as the UserA
   Given I log in as "UserA" with password "Kupuh1pa!"
   # Creating a folder with a  tag
   When I choose "Files" in "Create" from main menu
   And I set the following fields to these values:
   | Create folder | folder1 |
   # Pressing create folder button
   And I scroll to the base of id "files_filebrowser_createfolder"
   And I press "Create folder"
   # Editing the folder
   And I press "Edit folder \"folder1\""
   And I set the following fields to these values:
   | Description | This is a subdirectory |
   And I fill in select2 input "files_filebrowser_edit_tags" with "&red" and select "&red"
   And I fill in select2 input "files_filebrowser_edit_tags" with "Tag" and select "Tag"
   And I press "Save changes"
   # Creating a Journal with tag
   And I choose "Journals" in "Create" from main menu
   And I follow "New entry"
   And I set the following fields to these values:
   | Title *  | Journal one  |
   | Entry | This is a test |
   And I scroll to the base of id "editpost_tags_container"
   And I fill in select2 input "editpost_tags" with "blue" and select "blue"
   And I press "Save entry"
   # Creating a Plan with a tag
   And I choose "Plans" in "Create" from main menu
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
   And I fill in "Completion date" with "tomorrow" date in the format "Y/m/d"
   And I scroll to the base of id "edittask_tags_container"
   And I fill in select2 input "edittask_tags" with "blue" and select "blue"
   And I press "Save task"
   # Adding a tag to page 1
   And I choose "Pages and collections" in "Create" from main menu
   And I click on "Edit" in "Page UserA_01" card menu
   And I click on "Settings" in the "Toolbar buttons" "Nav" property
   And I fill in select2 input "settings_tags" with "blue" and select "blue"
   And I fill in select2 input "settings_tags" with "Tag" and select "Tag"
   And I press "Save"
   # Adding a tag to page 2
   And I choose "Pages and collections" in "Create" from main menu
   And I click on "Edit" in "Page UserA_02" card menu
   And I click on "Settings" in the "Toolbar buttons" "Nav" property
   And I fill in select2 input "settings_tags" with "#orange" and select "#orange"
   And I fill in select2 input "settings_tags" with "Tag" and select "Tag"
   And I press "Save"
   # Creating a Note with a tag
   When I follow "Drag to add a new block" in the "blocktype sidebar" "Views" property
   And I press "Add"
   And I click on blocktype "Note"
   # create a note block with a Unique name (Note Block 3304)
   And I fill in the following:
   | Block title | Note Block 3304 |
   And I fill in select2 input "instconf_tags" with "@black" and select "@black"
   And I fill in select2 input "instconf_tags" with "Tag" and select "Tag"
   And I press "Save"
   # Creating a Text block with a tag
   When I follow "Drag to add a new block" in the "blocktype sidebar" "Views" property
   And I press "Add"
   And I click on blocktype "Text"
   # create a text block with a Unique name (Text Block 101)
   And I fill in the following:
   | Block title | Text Block 101 |
   And I fill in select2 input "instconf_tags" with "ébrown" and select "ébrown"
   And I fill in select2 input "instconf_tags" with "Tag" and select "Tag"
   And I press "Save"
   # Creating a resume field with a tag
   When I follow "Drag to add a new block" in the "blocktype sidebar" "Views" property
   And I press "Add"
   And I click on blocktype "One résumé field"
   And I fill in select2 input "instconf_tags" with "êyellow" and select "êyellow"
   And I press "Save"
   # Creating an external video block with a tag
   # need to do this one last as the loading of video effects takes focus away from the add block modal
   When I follow "Drag to add a new block" in the "blocktype sidebar" "Views" property
   And I press "Add"
   And I click on blocktype "External media"
   And I fill in "URL or embed code" with "https://www.youtube.com/embed/VeS1iqQ6VIc"
   And I fill in select2 input "instconf_tags" with "ègreen" and select "ègreen"
   And I fill in select2 input "instconf_tags" with "Tag" and select "Tag"
   And I press "Save"
   And I choose "Pages and collections" in "Create" from main menu
   Then I follow "Tags" in the "Tags block" "Blocks" property
   # Verifying tags are saved
   And I should see "blue" in the "Search results for all tags" "Tags" property
   And I should see "@black" in the "Search results for all tags" "Tags" property
   And I should see "#orange" in the "Search results for all tags" "Tags" property
   And I should see "&red" in the "Search results for all tags" "Tags" property
   And I should see "ébrown" in the "Search results for all tags" "Tags" property
   And I should see "ègreen" in the "Search results for all tags" "Tags" property
   And I should see "êyellow" in the "Search results for all tags" "Tags" property
   # Check the repeated tags
   And I follow "blue"
   And I should see "Journal one"
   And I should see "woooo"
   And I should see "task one"
   # Check single tag
   And I follow "#orange"
   And I should see "Page UserA_02"
   And I should not see "Text Block 101"
   # Check tags can be deleted from a page - Bug 1715491
   Given I follow "Page UserA_02"
   And I press "Edit"
   And I click on "Settings" in the "Toolbar buttons" "Nav" property
   And I clear value "#orange (1)" from select2 field "settings_tags"
   And I press "Save"
   And I display the page
   Then I should not see "#orange"

   # Create Portfolio page via tags = blue
   Given I choose "Pages and collections" in "Create" from main menu
   And I scroll to the base of id "addview-button"
   And I follow "Add"
   And I click on "Page" in the dialog
   And I fill in the following:
   | Page title | Create portfolio via tags feature |
   And I fill in "Create portfolio via tags feature description" in first editor
   # verify help text for "Create via tags" is displayed
   And I should see "Search for or enter tags to pull content into your page automatically. If you enter more than one tag, only content that is tagged with all these tags will appear on the page. You can then re-arrange and delete blocks."
   And I fill in select2 input "settings_createtags" with "blue" and select "blue"
   When I press "Save"
   Then I should see "Tagged journal entries"
   And I should see "Plans"
   When I click on "Settings" in the "Toolbar buttons" "Nav" property
   And I fill in select2 input "settings_createtags" with "@black" and select "@black"
   And I press "Save"
   Then I should see "Note Block 3304"
   When I click on "Settings" in the "Toolbar buttons" "Nav" property
   And I fill in select2 input "settings_createtags" with "ébrown" and select "ébrown"
   And I press "Save"

   When I choose "Shared by me" in "Share" from main menu
   And I click on "Edit access" in "Page UserA_01" row
   And I set the select2 value "Page UserA_01, Page UserA_02, Create portfolio via tags feature" for "editaccess_views"
   And I select "Public" from "accesslist[0][searchtype]"
   And I press "editaccess_submit"
   And I log out

   Given I log in as "UserB" with password "Kupuh1pa!"
   And I wait "2" seconds
   When I follow "Page UserA_02"
   And I follow "Tag"
   Then I should see "Tagged content of Angela User"
   And I should see "External media"
   Then I should see "Note Block 3304"
   Then I should see "Page UserA_01"
   Then I should see "Page UserA_02"
   Then I should see "Text Block 101"
