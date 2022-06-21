@javascript @core @core_administration @manual
Feature: Configuration on the Elasticsearch plugin
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
    | username | password  | email             | firstname | lastname | institution | authname | role   |
    | UserA    | Kupuh1pa! | UserA@example.org | Angela    | User     | mahara      | internal | member |

 And the following "pages" exist:
    | title         | description | ownertype | ownername |
    | Page UserA_01 | Page 01     | user      | UserA     |

 And the following "permissions" exist:
    | title         | accesstype | accessname |
    | Page UserA_01 | user       | admin      |

Scenario: Testing functions for general search
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
    And I click on "Select all"
    And I press "Save"
    And I press "Reset"
    And I set the following fields to these values:
    | Search | Page |
    And I press "Go"
    Then I should see "Angela"
    And I should see "Page UserA_01"

# Set system off elasticsearch
    Then I choose "Site options" in "Configure site" from administration menu
    And I expand the section "Search settings"
    And I select "internal" from "Search plugin"
    And I press "Update site options"
    And I log out
