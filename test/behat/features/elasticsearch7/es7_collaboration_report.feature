@javascript @core @core_administration @manual
Feature: Generating collaboration reports with enhanced search, Elasticsearch 7 and event logging
Generating a collaboration report
As an admin
So I can view information about site activity

Background:
 Given the following plugin settings are set:
   | plugintype | plugin         | field         | value      |
   | search     | elasticsearch7 | indexname     | behattest  |
   | search     | elasticsearch7 | types         | usr,interaction_instance,interaction_forum_post,group,view,artefact,block_instance,collection,event_log |
   | search     | elasticsearch7 | cronlimit     | 20000      |
   | search     | elasticsearch7 | shards        | 5          |
   | search     | elasticsearch7 | replicashards | 1          |

 And the following plugins are set:
    | plugintype  | plugin      | value |
    | blocktype   | annotation  | 1     |

 And the following site settings are set:
   | field                  | value          |
   | searchplugin           | elasticsearch7 |
   | searchuserspublic      | Yes            |
   | eventloglevel          | all            |
   | eventlogenhancedsearch | Yes            |
   | allowpublicviews       | 1              |

 And the following "institutions" exist:
    | name       | displayname  | registerallowed | registerconfirm | allowinstitutionpublicviews |
    | halloffame | Hall of Fame | ON              | OFF             | 1                           |

 And the following "users" exist:
   # Available fields: username*, password*, email*, firstname*, lastname*, institution, role*, authname, remoteusername, studentid, preferredname, town, country, occupation
   | username | password  | email                | firstname | lastname    | institution | authname | role   |
   | tonysop  | Kupuh1pa! | tony@tonymail.com    | Tony      | Soprano     | halloffame  | internal | member |
   | teeny    | Kupuh1pa! | tina@tinamail.com    | Tina      | Turner      | halloffame  | internal | member |
   | opra     | Kupuh1pa! | opra@opramail.com    | Opra      | Winfrey     | halloffame  | internal | member |
   | brucey   | Kupuh1pa! | brucey@brucemail.com | Bruce     | Springsteen | halloffame  | internal | member |

 And the following "groups" exist:
   | name              | owner   | grouptype | editroles | members                |
   | League of Legumes | opra    | standard  | all       | tonysop, teeny, brucey |

 And the following "pages" exist:
 # Tina's individual pages
   # Available fields: title*, description, ownertype*, ownername*, layout, tags
   | title                         | description | ownertype | ownername |
   | What's Love Got to Do with It |             | user      | teeny     |
   | River Deep - Mountain High    |             | user      | teeny     |
   | Proud Mary                    |             | user      | teeny     |

 And the following "collections" exist:
    # Available fields: title*, description, ownertype*, ownername*, pages
    | title         | description | ownertype | ownername | pages |
    | Greatest Hits |             | user      | teeny     | What's Love Got to Do with It,River Deep - Mountain High |

 And the following "blocks" exist:
    # Available fields: title*, type*, data*, page*, retractable*
    | title               | type         | page       | retractable | data  |
    | Job in the city     | text         | Proud Mary | no          | textinput=A film about orchids |
    | Workin for the man  | image        | Proud Mary | no          | attachment=Image1.jpg |
    | Every night and day | filedownload | Proud Mary | no          | attachments=mahara_about.pdf |
    | Rollin on the river | folder       | Proud Mary | no          | dirname=mammamia;attachments=mahara_about.pdf,Image2.png,Image1.jpg,Image3.png,mahara.mp3 |
    | Big wheels          | gallery      | Proud Mary | no          | attachments=Image3.png,Image2.png,Image1.jpg,Image1.jpg;imagesel=2;showdesc=yes;imagestyle=2 |

 And the following "pagecomments" exist:
    # Available fields: user*, comment*, page*, attachment, private, group (compulsory for group pages)
    | user | page       | comment         | private |
    | opra | Proud Mary | You get a taco! | false   |

# Report data
# DONE - Comments: Sum of all comments made on pages and artefacts during the selected time period.

# DONE - Annotations: Sum of all the annotation feedback placed on annotations during the selected time period. Note: This does not count annotations on pages themselves.

# DONE - People: Sum of all portfolios shared directly with specified people during the selected time period.

# DONE - Groups: Sum of all portfolios shared with groups during the selected time period.

# DONE - Institutions: Sum of all portfolios shared with an institution during the selected time period.

# DONE - Registered: Sum of all portfolios shared with all registered people during the selected time period.

# DONE - Public: Sum of all portfolios shared to the public during the selected time period.

# Secret URLs: Sum of all portfolios shared via a secret URL during the selected time period.

# DONE - Friends: Sum of all portfolios shared with friends of the selected people during the selected time period.

Scenario: Testing collaboration report generation
# Index all artefact types
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Select all"
    And I click on "Save"
    And I click on "Reset"
    Then I should see "Settings saved"
    And I log out

# Add annotation block and share page and collection with specified person, group, institution, registered people, public and Friends
    Given I log in as "teeny" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Greatest Hits"
    And I click on "Edit"
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property
    And I set the field "Block title" to "Annotation"
    And I click on blocktype "Annotation"
    And I fill in "You are simply the best" in editor "Annotation"
    And I click on "Save"
    And I click on "Share" in the "Toolbar buttons" "Nav" property
    And I select "Person" from "accesslist[0][searchtype]"
    And I select "Tony Soprano" from select2 hidden search box in row number "1"
    And I select "Group" from "accesslist[1][searchtype]"
    And I select "League of Legumes" from select2 group search box in row number "2"
    And I select "Hall of Fame" from "accesslist[2][searchtype]"
    And I click on "Save"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Manage sharing" in "Proud Mary" card access menu
    And I select "Person" from "accesslist[0][searchtype]"
    And I select "Bruce Springsteen" from select2 hidden search box in row number "1"
    And I select "Registered people" from "accesslist[1][searchtype]"
    And I select "Public" from "accesslist[2][searchtype]"
    And I select "Friends" from "accesslist[3][searchtype]"
    And I click on "Save"
    And I click on "Share" in the "Toolbar buttons" "Nav" property
    And I click on "New secret URL"
    And I accept the confirm popup
    And I log out

# Add comment to block on page
    Given I log in as "brucey" with password "Kupuh1pa!"
    And I click on "Proud Mary"
    And I click on "Details"
    When I click on "Add comment"
    And I fill in "Just dancing in the dark" in editor "Comment"
    And I click on "Comment"
    Then I should see "Comment submitted"
    And I log out
# Add annotation feedback
    Given I log in as "tonysop" with password "Kupuh1pa!"
    And I click on "Greatest Hits"
    And I click on "Place feedback"
    And I fill in "Better than all the rest" in first editor
    And I click on "Place feedback"
    And I log out

# Reindex search results
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Reset"
    Then I should see "Settings saved"
# Generate report
    When I choose "Reports" from administration menu
    And I click on "Configure report"
    And I set the following fields to these values:
    | Report type | Collaboration |
    And I click on "Columns"
    And I check "Annotations"
    And I check "Friends"
    And I click on "Submit"
# Comments:
    Then I should see "2" at matrix point "6,2"
# Annotations:
    Then I should see "1" at matrix point "6,3"
# Shared with People:
    Then I should see "2" at matrix point "6,4"
# Shared with Groups:
    Then I should see "1" at matrix point "6,5"
# Shared with Institutions:
    Then I should see "1" at matrix point "6,6"
# Shared with Registered people:
    Then I should see "1" at matrix point "6,7"
# Shared with Public:
    Then I should see "1" at matrix point "6,8"
# Secret URLs:
    Then I should see "1" at matrix point "6,9"
# Shared with Friends:
    Then I should see "1" at matrix point "6,10"
And I log out



