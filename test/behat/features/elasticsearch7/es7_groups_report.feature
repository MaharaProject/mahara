@javascript @core @core_administration @manual
Feature: Generating group reports with enhanced search and Elasticsearch 7
Generating an enhanced group report
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

 And the following site settings are set:
   | field                  | value          |
   | searchplugin           | elasticsearch7 |
   | searchuserspublic      | Yes            |
   | eventloglevel          | all            |
   | eventlogenhancedsearch | Yes            |

 And the following "users" exist:
   # Available fields: username*, password*, email*, firstname*, lastname*, institution, role*, authname, remoteusername, studentid, preferredname, town, country, occupation
   | username | password  | email                | firstname | lastname    | role |
   | tonysop  | Kupuh1pa! | tony@tonymail.com    | Tony      | Soprano     | user |
   | teeny    | Kupuh1pa! | tina@tinamail.com    | Tina      | Turner      | user |
   | opra     | Kupuh1pa! | opra@opramail.com    | Opra      | Winfrey     | user |
   | brucey   | Kupuh1pa! | brucey@brucemail.com | Bruce     | Springsteen | user |

 And the following "groups" exist:
   | name              | owner   | grouptype | editroles | members                |
   | Vege Kingdom      | tonysop | standard  | all       | teeny, opra, brucey    |
   | Fruit Fraternity  | teeny   | standard  | all       | tonysop, opra, brucey  |
   | League of Legumes | opra    | standard  | all       | tonysop, teeny, brucey |
   | Carbo Kids        | brucey  | standard  | all       | tonysop, teeny, opra   |

 And the following "pages" exist:
 # Group pages
   # Available fields: title*, description, ownertype*, ownername*, layout, tags
   | title     | description          | ownertype | ownername         |
   | Carrot    | Good in a salad      | group     | Vege Kingdom      |
   | Potato    | Best deep fried      | group     | Vege Kingdom      |
   | Pineapple | Luxury fruit         | group     | Fruit Fraternity  |
   | Snow Pea  | Cute and crunchy     | group     | League of Legumes |
   | Lentil    | Brown, red and green | group     | League of Legumes |
   | Muffin    | Sweet or savoury     | group     | Carbo Kids        |

 And the following "pages" exist:
 # Tina's individual pages
   # Available fields: title*, description, ownertype*, ownername*, layout, tags
   | title                         | description | ownertype | ownername |
   | What's Love Got to Do with It |             | user      | teeny     |
   | River Deep - Mountain High    |             | user      | teeny     |
   | Proud Mary                    |             | user      | teeny     |

 And the following "pagecomments" exist:
    # Available fields: user*, comment*, page*, attachment, private, group (compulsory for group pages)
    | user    | page   | comment   | private |  group       |
    | opra    | Carrot | yay!      | false   | Vege Kingdom |
    | brucey  | Carrot | what?     | false   | Vege Kingdom |
    | teeny   | Carrot | why?!     | false   | Vege Kingdom |
    | tonysop | Carrot | how??     | false   | Vege Kingdom |

 And the following "collections" exist:
    # Available fields: title*, description, ownertype*, ownername*, pages
    | title         | description | ownertype | ownername | pages |
    | Greatest Hits |             | user      | teeny     | What's Love Got to Do with It,River Deep - Mountain High |

# Enhanced search results:
# Group page comments = Sum of all comments added to group pages and artefacts for a group that was created during the selected time period.

# Shared pages = Sum of all the pages, including all within collections, that have been shared with a group that was created during the selected time period.

# Shared page comments = Sum of all comments added to pages and page artefacts after they have been shared with a group that was created during the selected time period. Any comments added before the page was shared are not counted.

Scenario: Testing report generation
# Index all artefact types
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Select all"
    And I click on "Save"
    And I click on "Reset"
    Then I should see "Settings saved"
    And I log out

# Share portfolios with group
    Given I log in as "teeny" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Manage sharing" in "Greatest Hits" card access menu
    And I select "Carbo Kids" from "Groups" in shared with select2 box
    And I click on "Save"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Manage sharing" in "Proud Mary" card access menu
    And I select "League of Legumes" from "Groups" in shared with select2 box
    And I click on "Save"
    And I log out

# Add comments to pages and page artefacts that have been shared with group
    And I log in as "brucey" with password "Kupuh1pa!"
    And I scroll to the base of id "blockinstance_55"
    And I click on "Proud Mary"
    And I click on "Add comment"
    And I fill in "Keep on burnin" in editor "Comment"
    And I click on "Comment"
    And I click on "Mahara"
    And I scroll to the base of id "blockinstance_55"
    And I click on "Greatest Hits"
    And I click on "Add comment"
    And I fill in "Born in the USA" in editor "Comment"
    And I click on "Comment"
    And I log out

# Reindex search results
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Reset"
    Then I should see "Settings saved"

# Generate Groups report
    When I choose "Reports" from administration menu
    And I click on "Configure report"
    And I set the following fields to these values:
    | Report type | Groups |
    And I click on "Columns"
    And I check "Group page comments"
    And I check "Shared pages"
    And I check "Shared page comments"
    And I click on "Submit"

# Group page comments
    Then I should see "4" at matrix point "4,4"

# Shared pages
    And I should see "2" at matrix point "5,1"
    And I should see "1" at matrix point "5,3"

# Shared page comments
    And I should see "1" at matrix point "6,1"
    And I should see "1" at matrix point "6,3"
    And I log out