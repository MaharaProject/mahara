@javascript @core @core_administration @manual
Feature: Groups search with Elasticsearch 7
In order to index and search the site using elasticsearch7
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
    | field        | value          |
    | searchplugin | elasticsearch7 |

 And the following "users" exist:
    | username | password  | email               | firstname | lastname | institution | authname | role   |
    | PersonA  | Kupuh1pa! | PersonA@example.org | Angela    | Person   | mahara      | internal | member |
    | PersonB  | Kupuh1pa! | PersonB@example.org | Bob       | Person   | mahara      | internal | member |
    | PersonC  | Kupuh1pa! | PersonC@example.org | Cecilia   | Person   | mahara      | internal | member |
    | PersonD  | Kupuh1pa! | PersonD@example.org | Dmitri    | Person   | mahara      | internal | member |
    | PersonE  | Kupuh1pa! | PersonE@example.org | Evonne    | Person   | mahara      | internal | member |
    | UserF    | Kupuh1pa! | UserD@example.org   | Dmitri    | User     | mahara      | internal | member |
    | UserG    | Kupuh1pa! | UserE@example.org   | Evonne    | User     | mahara      | internal | member |

 And the following "groups" exist:
    | name        | owner   | description             | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members          | staff |
    | Alpha       | PersonA | GroupA owned by PersonA | standard  | ON   | ON            | all       | ON            | ON            | PersonB, PersonC | admin |
    | Alpha Romeo | PersonB | GroupB owned by PersonA | standard  | ON   | ON            | all       | ON            | ON            | PersonA, PersonC | admin |
    | Alpha Beta  | PersonC | GroupC owned by PersonA | standard  | ON   | ON            | all       | ON            | ON            | PersonB, PersonA | admin |
    | BetaBeta    | PersonD | GroupC owned by PersonA | standard  | ON   | ON            | all       | ON            | ON            | PersonB, PersonA | admin |
    | CetaCeta    | PersonE | GroupC owned by PersonA | standard  | ON   | ON            | all       | ON            | ON            | PersonB, PersonA | admin |

 And the following "forums" exist:
    | group      | title     | description          | creator |
    | Alpha Beta | unicorns! | magic mahara unicorns| PersonC |

 And the following "forumposts" exist:
    # Available fields: group*, forum, topic, subject, message*, user*
    | group       | forum      | topic     | subject    | message                     | user    |
    | Alpha Romeo | bananas    | yellow?   | I think so | I believe they are yellow   | PersonA |

Scenario: Testing functions for group search
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Select all"
    And I press "Save"
    And I press "Reset"
    And I log out
    When I log in as "PersonB" with password "Kupuh1pa!"
    And I set the following fields to these values:
    | Search | alpha |
    And I press "Go"
    When I follow "Group (3)"
    Then I should see "GroupA owned by PersonA"
    When I set the following fields to these values:
    | Search | believe |
    And I press "Go"
    Then I should see "yellow?"
    And I log out
    When I log in as "PersonD" with password "Kupuh1pa!"
    And I set the following fields to these values:
    | Search | believe |
    And I press "Go"
    Then I should not see "yellow?"
    And I log out

 # Check result counts match counts in group search and in people search
    When I log in as "admin" with password "Kupuh1pa!"
    And I choose "Administer groups" in "Groups" from administration menu
    And I set the following fields to these values:
    | search_query | alpha |
    And I press "Search"
    Then I should see "3 groups"
    When I choose "People search" in "People" from administration menu
    And I set the following fields to these values:
    | Search: | Person |
    And I press "Search"
    Then I should see "5 results"
    When I set the following fields to these values:
    | Search | Person |
    And I press "Go"
    Then I should see "People (5)"
    And I log out
