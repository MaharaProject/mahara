@javascript @core @core_administration @manual
Feature: Newly created pages showing in Elasticsearch 7 search results
In order to check that new pages are being added to the index
So I can ensure that newly added data is being included in the Elasticsearch 7 results

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

Scenario: Testing search functions with new data
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Select all"
    And I click on "Save"
    And I click on "Reset"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Create" in the "Create" "Views" property
    And I click on "Page" in the dialog
    And I fill in the following:
    | Page title       | Ziggy Stardust |
    | Page description | Amazing, beautiful |
    And I click on "Save"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Reset"
    When I set the following fields to these values:
    | Search | Ziggy |
    And I click on "Go"
    Then I should see "Ziggy Stardust"
    And I log out
