@javascript @core @core_administration @manual
Feature: Page deletion reflected in search results using Elasticsearch 7
In order to search the site using elasticsearch7
As an admin and a user
So I can check that search results are being updated when a page has been deleted

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

Scenario: Testing search functions with deleted data
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Select all"
    And I press "Save"
    And I press "Reset"
    When I set the following fields to these values:
    | Search | Page |
    And I press "Go"
    Then I should see "Page UserA_01"
    And I log out

 # Delete page
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Delete" in "Page UserA_01" card menu
    And I press "Yes"
    And I log out

# Reindex and search
    When I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I press "Reset"
    And I set the following fields to these values:
    | Search | Page |
    And I press "Go"
    Then I should not see "Page UserA_01"
    And I log out
