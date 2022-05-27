@javascript @core @core_administration @manual
Feature: Site search for journals, plans and tags with Elasticsearch 7
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
   | field        | value          |
   | searchplugin | elasticsearch7 |

 And the following "users" exist:
    # Available fields: username*, password*, email*, firstname*, lastname*, institution, role, authname, remoteusername, studentid, preferredname, town, country, occupation
    | username | password | email             | firstname | lastname | institution | authname | role   |
    | UserA    | Kupuh1pa!| UserA@example.org | Painterio | Mahara   | mahara      | internal | admin  |
    | UserB    | Kupuh1pa!| UserB@example.org | Mechania  | Mahara   | mahara      | internal | member |

 And the following "groups" exist:
    # Availble fields: name*, owner*, description, grouptype, open, controlled, request, invitefriends, suggestfriends, submittableto, allowarchives,
    #                 editwindowstart, editwindowstart, editwindowend, members, staff, admins, institution, public
    | name          | owner | description                  | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
    | Talking Heads | UserB | Talking Heads owned by UserB | standard  | ON   | OFF           | all       | ON            | OFF           | UserA   |       |

 And the following "journals" exist:
    # Available fields: owner*, ownertype*, title*, description*, tags
    | owner         | ownertype | title                | description                | tags                       |
    | UserA         | user      | Chronicles of Narnia | Lions and tigers and bears | amber,brown,cobalt         |
    | Talking Heads | group     | David Byrne's Diary  | Why am I so cool           | amber,brown,cobalt,magenta |

 And the following "journalentries" exist:
    # Available fields: owner*, ownertype*, title*, entry*, blog*, tags, draft
    | owner         | ownertype | title       | entry                          | blog                 | tags       | draft |
    | UserA         | user      | Wardrobes   | More than what they seem       | Chronicles of Narnia | cats,dogs  | 0     |
    | UserA         | user      | Witches     | Mode of transport = broomstick | Chronicles of Narnia | cats,dogs  | 0     |
    | UserA         | user      | Thoughts    | This is about cats             | Chronicles of Narnia | cats,dogs  | 0     |
    | UserA         | user      | Entry Four  | This is my entry Four          | Chronicles of Narnia | cats,dogs  | 0     |
    | UserA         | user      | Entry Five  | This is my entry Five          | Chronicles of Narnia | cats,dogs  | 0     |
    | UserA         | user      | Entry Mini  | This is my min fields          | Chronicles of Narnia | ferrets    | 0     |
    | Talking Heads | group     | Group e1    | This is my group entry         | David Byrne's Diary  | ferrets    | 0     |

 And the following "plans" exist:
    # Available fields: owner*, ownertype*, title*, description, tags
    | owner         | ownertype | title                    | description           | tags      |
    | UserA         | user      | Take over the world      | This is my plan one   | cats,dogs |
    | UserA         | user      | April Fools Day          | This is my plan two   | cats,dogs |
    | UserA         | user      | Rest                     | Take a break          |           |
    | Talking Heads | group     | Band practice            | Party time            | unicorn   |

 And the following "tasks" exist:
    # Available fields: owner*, ownertype*, plan*, title*, completed*, completiondate*, description, tags
    | owner         | ownertype | plan                | title       | description                   | completiondate | completed | tags      |
    | UserA         | user      | Take over the world | Finance     | Get all the money             | 12/12/19       | false     | cats,dogs |
    | UserA         | user      | Take over the world | Power       | Become president of the world | 12/01/19       | true      | cats,dogs |
    | UserA         | user      | April Fools Day     | Toilet seat | Glad wrap                     | 12/10/19       | true      | cats,dogs |
    | UserA         | user      | April Fools Day     | Salt        | Salt in the sugar bowl        | 11/01/19       | true      | cats,dogs |
    | UserA         | user      | April Fools Day     | Clocks      | Turn the clocks back          | 22/02/19       | true      | cats,dogs |
    | Talking Heads | group     | Band practice       | Gear        | Bring your instruments        | 22/02/19       | false     |           |

Scenario: Testing search functions
   Given I log in as "admin" with password "Kupuh1pa!"
   And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
   And I click on "Select all"
   And I click on "Save"
   And I click on "Reset"
   And I log out

 # Check tags are included in search results
   When I log in as "UserA" with password "Kupuh1pa!"
   And I set the following fields to these values:
   | Search | cats |
   And I click on "Go"
   Then I should see "13"

 # Check tagsonly option is working (these steps are incorrect but manual testing works)
   When I set the following fields to these values:
   | search_tagsonly | Tags only |
   | search_query | cats |
   And I click on "Go"
   Then I should see "Tags only"
   And I should see "12"

 # Search by journal title
   When I set the following fields to these values:
   | Search | narnia |
   And I click on "Go"
   Then I should see "Journal"
   When I click on "Chronicles of Narnia"
   Then I should see "Lions and tigers and bears"

 # Search by journal description
   When I set the following fields to these values:
   | Search | cool |
   And I click on "Go"
   Then I should see "David Byrne's Diary"

 # Search by journal entry title
   When I set the following fields to these values:
   | Search | wardrobes |
   And I click on "Go"
   Then I should see "Journal entry"
   When I click on "Wardrobes"
   Then I should see "Chronicles of Narnia"

 # Search by journal entry content
   When I set the following fields to these values:
   | Search | broomstick |
   And I click on "Go"
   And I click on "Witches"
   Then I should see "Chronicles of Narnia"

 # Search by plan title
   When I set the following fields to these values:
   | Search | april |
   And I click on "Go"
   Then I should see "April Fools Day"
   # And I click on "April Fools Day"
   # Then I should see "Salt in the sugar bowl"

 # Search by plan description
   When I set the following fields to these values:
   | Search | break |
   And I click on "Go"
   Then I should see "Plan"
   When I click on "Rest"
   Then I should see "New task"

 # Search by task title
   When I set the following fields to these values:
   | Search | finance |
   And I click on "Go"
   Then I should see "Plan"
   When I click on "Finance"
   Then I should see "Take over the world"

 # Seach by task description
   When I set the following fields to these values:
   | Search | president |
   And I click on "Go"
   Then I should see "Plan"
   When I click on "Power"
   Then I should see "Take over the world"
   And I log out
