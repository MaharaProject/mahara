@javascript @core @core_administration @manual
Feature: File search with Elasticsearch 7
In order to index and search the site using elasticsearch7
As an admin and a user
So I can benefit from the rich search information

Background:
 Given the following plugin settings are set:
    | plugintype | plugin         | field         | value      |
    | search     | elasticsearch7 | indexname     | behattest  |
    | search     | elasticsearch7 | types         | usr,interaction_instance,interaction_forum_post,group,view,artefact,block_instance,collection,event_log |
    | search     | elasticsearch7 | cronlimit     | 20000      |
    | search     | elasticsearch7 | shards        | 5          |
    | search     | elasticsearch7 | replicashards | 1          |

 And the following site settings are set:
    | field             | value          |
    | searchplugin      | elasticsearch7 |
    | searchuserspublic | Yes            |

 And the following "users" exist:
 # Available fields: username*, password*, email*, firstname*, lastname*, institution, role*, authname, remoteusername, studentid, preferredname, town, country, occupation
    | username | password  | email             | firstname | lastname | role  |
    | tonysop  | Kupuh1pa! | tony@tonymail.com | Tony      | Soprano  | admin |
    | teeny    | Kupuh1pa! | tina@tinamail.com | Tina      | Turner   | admin |

 And the following "pages" exist:
    # Available fields: title*, description, ownertype*, ownername*, layout, tags
    | title      | description                | ownertype | ownername  |
    | Proud Mary | Big wheels keep on turnin' | user      | teeny      |

 And the following "blocks" exist:
    # Available fields: title*, type*, data*, page*, retractable*
    | title        | type         | page       |retractable | data |
    | Tina's Stuff | filedownload | Proud Mary | no         | attachments=mahara_about.pdf,Image1.jpg,mahara.mp3,testvid3.mp4 |
    | Picture      | image        | Proud Mary | no         | attachment=Image2.png |

Scenario: Testing file search function
# Editing filenames and descriptions
    Given I log in as "teeny" with password "Kupuh1pa!"
    And I choose "Files" in "Create" from main menu
# pdf
    When I scroll to the base of id "files_filebrowser_filelist_container"
    And I click on "Edit \"mahara_about.pdf\""
    And I set the field "Name" to "camelia.pdf"
    And I set the field "Description" to "This is not a picture of a flower"
    And I click on "Save changes"
# jpg
    When I scroll to the id "files_filebrowser_filelist_container"
    And I click on "Edit \"Image1.jpg\""
    And I set the field "Name" to "lavender.jpg"
    And I set the field "Description" to "This smells wonderful"
    And I click on "Save changes"
# png
    When I scroll to the id "files_filebrowser_filelist_container"
    And I click on "Edit \"Image2.png\""
    And I set the field "Name" to "daisy.png"
    And I set the field "Description" to "Loves me, loves me not"
    And I click on "Save changes"
# mp3
    When I scroll to the id "files_filebrowser_filelist_container"
    And I click on "Edit \"mahara.mp3\""
    And I set the field "Name" to "music.mp3"
    And I set the field "Description" to "The sound of music"
    And I click on "Save changes"
# mp4
    When I click on "Edit \"testvid3.mp4\""
    And I set the field "Name" to "watchme.mp4"
    And I set the field "Description" to "Look at this incredible thing I can do"
    And I click on "Save changes"

# Share page
    When I choose "Portfolios" in "Create" from main menu
    And I click on "Manage sharing" in "Proud Mary" card access menu
    And I select "Person" from "accesslist[0][searchtype]"
    And I select "Tony Soprano" from select2 nested search box in row number "1"
    And I click on "Save"
    And I log out

# Indexing search results
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Select all"
    And I click on "Save"
    And I click on "Reset"
    And I log out

# Search as Tina Turner by filename
   When I log in as "teeny" with password "Kupuh1pa!"
   And I scroll to the top
   # And I set the following fields to these values:
   # | Search | camelia |
   # And I click on "Go"
   # Then I should see "camelia.pdf"

# Search as Tina Turner by file description
    When I set the following fields to these values:
    | Search | loves |
    And I click on "Go"
    And I should see "daisy.png"
    Then I should see "Used on page:"
    When I click on "Proud Mary"
    Then I should see "Picture"
    And I log out

# Search by type?

# Search as Tony Soprano for Tina's Stuff by file description
    Given I log in as "tonysop" with password "Kupuh1pa!"
    And I set the following fields to these values:
    | Search | smells |
    When I click on "Go"
    Then I should see "lavender.jpg"
    And I should see "Used on page:"
    When I click on "Proud Mary"
    Then I should see "Tina's Stuff"
    And I log out