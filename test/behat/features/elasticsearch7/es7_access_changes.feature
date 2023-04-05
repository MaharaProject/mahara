@javascript @core @core_administration @manual
Feature: Access permissions adhered to by Elasticsearch
In order to check that the access permissions are being reindexed by elasticsearch correctly
As a user
So I can control access to my portfolios

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
    | username | password  | email             | firstname | lastname | institution | authname | role   |
    | UserA    | Kupuh1pa! | UserA@example.org | Angela    | User     | mahara      | internal | member |
    | UserB    | Kupuh1pa! | UserB@example.org | Bob       | User     | mahara      | internal | member |
    | UserC    | Kupuh1pa! | UserC@example.org | Cindy     | User     | mahara      | internal | member |

 And the following "pages" exist:
    | title         | description | ownertype | ownername |
    | Page UserA_01 | Page 01     | user      | UserA     |

Scenario: Testing functions for user search page (Bug 1431569)
 # Index content
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Select all"
    And I click on "Save"
    And I click on "Reset"
    And I log out

 # Grant access to specific person
    When I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Manage sharing" in "Page UserA_01" card access menu
    And I select "Person" from "accesslist[0][searchtype]"
    And I select "Bob User" from select2 nested search box in row number "1"
    And I click on "Save"
    And I log out

 # Reindex
    When I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Reset"
    And I log out

 # Search
    And I log in as "UserB" with password "Kupuh1pa!"
    And I set the following fields to these values:
    | Search | Page |
    And I click on "Go"
    Then I should see "Page UserA_01"
    And I log out
    When I log in as "UserC" with password "Kupuh1pa!"
    And I set the following fields to these values:
    | Search | Page |
    And I click on "Go"
    Then I should not see "Page UserA_01"
    And I log out

 # Remove access
    When I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Manage sharing" in "Page UserA_01" card access menu
    And I click on "Remove"
    And I click on "Save"
    And I log out
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Reset"
    And I log out
    When I log in as "UserB" with password "Kupuh1pa!"
    And I set the following fields to these values:
    | Search | Page |
    And I click on "Go"
    Then I should not see "Page UserA_01"
    And I log out

 # Grant access to registered people
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Manage sharing" in "Page UserA_01" card access menu
    And I select "Registered people" from "accesslist[0][searchtype]"
    And I click on "Save"
    And I log out

 # Reindex
    When I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Reset"
    And I log out

 # Search
    And I log in as "UserC" with password "Kupuh1pa!"
    And I set the following fields to these values:
    | Search | Page |
    And I click on "Go"
    Then I should see "Page UserA_01"
    And I log out
