@javascript @core @core_administration @manual
Feature: Edited data reflected in search results using Elasticsearch 7
In order to index and search the site using elasticsearch7
As an admin and a user
So I can ensure that modified data is being included in the Elasticsearch 7 results

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

 And the following "pages" exist:
    | title         | description | ownertype | ownername |
    | Page UserA_01 | Page 01     | user      | UserA     |

 And the following "permissions" exist:
    | title         | accesstype | accessname |
    | Page UserA_01 | user       | admin      |

Scenario: Testing edited data appears in search
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Select all"
    And I click on "Save"
    And I click on "Reset"
    When I set the following fields to these values:
    | Search | Page |
    And I click on "Go"
    Then I should see "Angela"
    And I should see "Page UserA_01"
    And I log out
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    And I click on "Configure" in the "Toolbar buttons" "Nav" property
    And I set the following fields to these values:
    | Page title | Different title |
    And I click on "Save"
    And I log out
    When I log in as "admin" with password "Kupuh1pa!"
    And I set the following fields to these values:
    | Search | Page |
    Then I should see "Different title"
    And I log out
