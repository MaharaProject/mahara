@javascript @core @core_administration @manual
Feature: Tabs, filter, sort, owner and pagination functions in search results
In order to index and search the site using elasticsearch7
As an admin
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

 And the following "groups" exist:
    | name              | owner   | grouptype | editroles | members |
    | Vege Kingdom      | admin   | standard  | all       | tonysop |
    | Fruit Fraternity  | admin   | standard  | all       | teeny   |
    | League of Legumes | tonysop | standard  | all       | teeny   |
    | Carbo Kids        | teeny   | standard  | all       | admin   |
 
 And the following "pages" exist:
    # Available fields: title*, description, ownertype*, ownername*, layout, tags
    | title          | ownertype | ownername  | tags  |
    | Coriander      | user      | admin      | vege  |
    | Tomato         | user      | tonysop    | fruit |
    | Beans          | group     | Carbo Kids | vege  |
    | Apple          | user      | admin      | fruit |
    | Pear           | user      | admin      | fruit |
    | Banana         | user      | admin      | fruit |
    | Spinach        | user      | admin      | vege  |
    | Carrot         | user      | admin      | vege  |
    | Brocolli       | user      | admin      | vege  |
    | Artichoke      | user      | admin      | vege  |
    | Capsicum       | user      | admin      | vege  |
    | Potato         | user      | admin      | vege  |
    | Corn           | user      | admin      | vege  |
    | Lemon          | user      | admin      | fruit |
    | Peach          | user      | admin      | fruit |
    | Plum           | user      | admin      | fruit |
    | Apricot        | user      | admin      | fruit |
    | Eggplant       | user      | admin      | vege  |
    | Brussel Sprout | user      | admin      | vege  |

 And the following "blocks" exist:
    # Available fields: title*, type*, data*, page*, retractable*
    | title                | type  | page          | retractable | data |
    | Herb Text            | text  | Coriander     | no          | textinput=The most fickle herb on earth |
    | Images of coriander? | image | Coriander     | no          | attachment=Image1.jpg;width=100;tags=imagetag |

Scenario: Testing filter, sort, owner and pagination functions for general search
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Select all"
    And I click on "Save"
    And I click on "Reset"
    And I click on "Go"

# Testing tabs 
    When I click on "Media (1)"
    Then I should see "Coriander"
    When I click on "Portfolio (24)"
    Then I should see "Potato"
    When I click on "People (3)"
    Then I should see "Tony Soprano"
    When I click on "Group (4)"
    Then I should see "Vege Kingdom"

# TODO: Filter by owner (Administrator = Others is currently not working)
    When I set the following fields to these values:
    | Administrator: | Me |
    Then I should see "Fruit Fraternity"
    And I should not see "League of Legumes"
    When I set the following fields to these values:
    | Administrator: | Others |
    Then I should see "League of Legumes"
    # And I should not see "Fruit Fraternity"

# Filter by owner
    When I click on "Portfolio (24)"
    And I set the following fields to these values:
    | Owner: | Me |
    Then I should not see "Beans"
    When I set the following fields to these values:
    | Owner: | Others |
    Then I should see "Beans"
    And I should not see "Coriander"

# TODO: Testing filter functions (Working in ES7 but need to write steps that target filter functions)
   # When I click on "Text (44)"
   # And I click on "Document (40)"
   # Then I should not see "Carbo Kids"
   # And I should see "Images of coriander?"
   # When I click on "Document (40)"
   # And I click on "Forum (3)"
   # Then I should not see "Document"
   # And I should see "Carbo Kids"
   # When I click on "Forum (3)"
   # And I click on "Journal (1)"
   # Then I should not see "Document"
   # And I should see "Journal"


# Testing pagination and sort
    When I click on "Portfolio (24)"
    When I set the following fields to these values:
    | Sort by: | A to Z |
    Then "Apple" "text" should appear before "Apricot" "text"
    When I set the following fields to these values:
    | Results per page: | 1 |
    Then I should not see "Corn"
    When I set the following fields to these values:
    | Results per page: | 20 |
    Then I should see "Pear"
    And I log out
