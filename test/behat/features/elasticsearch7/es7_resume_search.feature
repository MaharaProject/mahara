@javascript @core @core_administration @manual
Feature: Site search for Resume data with Elasticsearch 7
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
   # Available fields: username*, password*, email*, firstname*, lastname*, institution, role, authname, remoteusername, studentid, preferredname, town, country, occupation
   | username | password | email             | firstname | lastname | institution | authname | role   |
   | UserA    | Kupuh1pa!| UserA@example.org | Painterio | Mahara   | mahara      | internal | admin  |
   | UserB    | Kupuh1pa!| UserB@example.org | Mechania  | Mahara   | mahara      | internal | member |

 And the following "groups" exist:
   # Availble fields: name*, owner*, description, grouptype, open, controlled, request, invitefriends, suggestfriends, submittableto, allowarchives, editwindowstart, editwindowstart, editwindowend, members, staff, admins, institution, public
   | name              | owner   | grouptype | editroles | members |
   | Fantastic Five    | UserB   | standard  | all       | UserA   |

 And the following "personalinformation" exist:
   # Available fields: username*, password*, email, firstname, lastname, institution, authname, role
   | user  | dateofbirth | placeofbirth | citizenship | visastatus | gender | maritalstatus |
   | UserA | 01/01/2000  | Italy        | New Zealand |            |        |               |
   | UserB | 01/01/2018  | Germany      | New Zealand |            |        |               |

 And the following "goals and skills" exist:
   | user  | goaltype/skilltype  | title         | description            | attachment  |
   | UserA | academicgoal        | fix lateness  | pack bag night before  | Image1.jpg  |
   | UserA | careergoal          | meow          | cat a lyst             | users.csv   |
   | UserA | personalgoal        | gym shark     | do do do               | Image2.png  |
   | UserA | academicskill       | alphabet      | abc                    | 3images.zip |
   | UserA | personalskill       | whistle       | *inset whistle noise   | Image1.jpg  |
   | UserA | workskill           | team work     | axe throwing?          | users.csv   |
   | UserB | academicgoal        | academi doooo | description goal/skill | Image2.png  |
   | UserB | careergoal          | careerg doooo | description goal/skill | groups.csv  |
   | UserB | personalgoal        | persona doooo | description goal/skill | Image1.jpg  |
   | UserB | academicskill       | academi doooo | description goal/skill | users.csv   |
   | UserB | personalskill       | persona doooo | description goal/skill | Image2.png  |
   | UserB | workskill           | workski doooo | description goal/skill | groups.csv  |

 And the following "interests" exist:
   # Available fields: user*, interest*, description
   | user  | interest          | description                 |
   | UserA | FOSS              | exciting open source stuff! |
   | UserA | kite surfing      | awesome e-portfolio system  |
   | UserA | Coffee and Coding |                             |

 And the following "coverletters" exist:
   # Availble fields: user*, content*
   | user  | content |
   | UserA | UserA In Te Reo Māori, "mahara" means "to think, thinking, thought" and that fits the purpose of Mahara very well. Having been started in New Zealand, it was fitting to choose a Māori word to signify the concept of the ePortfolio system |
   | UserB | UserB In Te Reo Māori, "mahara" means "to think, thinking, thought" and that fits the purpose of Mahara very well. Having been started in New Zealand, it was fitting to choose a Māori word to signify the concept of the ePortfolio system |

 And the following "educationhistory" exist:
   # Available fields: user, startdate, enddate, institution, institutionaddress, qualtype, qualname,qualdescription,attachment
   | user  | institution   | startdate | enddate  | qualdescription | attachment |
   | UserA | Catalystania  | 12/12/12  | 12/12/21 | 9 years         | Image2.png |
   | UserB | Catalystonia  | 21/10/21  | 10/12/26 | educationnn     | Image2.png |
   | UserA | Catalyst High | 12/12/20  | 12/12/21 | 9 years         | Image2.png |
   | UserB | Catalyst High | 21/10/20  | 10/12/26 | educationnn     | Image2.png |

 And the following "employmenthistory" exist:
   # Available fields: user, startdate, enddate, employer, employeraddress, jobtitle, positiondescription
   | user  | employer | startdate | enddate | jobtitle   | positiondescription     | attachment |
   | UserA | Eggman   | 01/02/03  |         | crystal dr | locating magic crystals | Image1.jpg |
   | UserB | Cat      | 02/02/00  |         | Cat sitter | pat kittens             | Image1.jpg |

 And the following "contactinformation" exist:
    # Available fields: user*, email*, officialwebsite, personalwebsite, blogaddress, town, city/region, country, homenumber,
    #                   businessnumber, mobilenumber, faxnumber
    | user  | email            | mobilenumber |
    | UserA | userA@mahara.com | 01234567890  |

 And the following "achievements" exist:
   # Available fields: user, date, title, description, attachment
   | user  | date     | title               | attachment       | description |
   | UserA | 02/02/80 | European Witchcraft | Image3.png       | While the streets may be education enough for real gangsters, this course aims to teach students about the history and culture of the mafia around the world. [Williams College] |
   | UserB | 02/02/80 | Western Witchcraft  | mahara_about.pdf | While the streets may be education enough for real gangsters, this course aims to teach students about the history and culture of the mafia around the world. [Williams College] |

 And the following "books and publications" exist:
   # Available fields: user, date, title, contribution, description, url, attachment
   | user  | date     | title                                     | contribution | description                                                                          | attachment |
   | UserA | 05/05/50 | The Life-Changing Magic of not Tidying Up | co-author    | seven million copies worldwide and have been translated into thirty-eight languages. | Image3.png |
   | UserB | 05/05/50 | The Life-Changing Magic of not Tidying Up | co-author    | seven million copies worldwide and have been translated into thirty-eight languages. | Image3.png |

 And the following "professionalmemberships" exist:
   # Available fields: user, startdate, enddate, title, description, attachment
   | user  | startdate   | title                       | description         | attachment |
   | UserA | 20/02/2008  | cat art company coordinator | catch up with cats  | Image3.png |
   | UserB | 20/02/2008  | cat art company catcher     | catch fish for cats | Image3.png |

Scenario: Testing search for resume artefacts
   Given I log in as "admin" with password "Kupuh1pa!"
   And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
   And I click on "Select all"
   And I click on "Save"
   And I click on "Reset"
   Then I should see "Settings saved"
   When I set the following fields to these values:
   | Search | painterio |
   And I click on "Go"
   Then I should see "UserA"

   When I set the following fields to these values:
   | Search | fantastic |
   And I click on "Go"
   Then I should see "Fantastic Five"
   And I log out

   When I log in as "UserA" with password "Kupuh1pa!"
# Place of birth not indexed in ES5
   # And I set the following fields to these values:
   # | Search | italy |
   # And I click on "Go"
   # Then I should see "Personal information"

   When I set the following fields to these values:
   | Search | bag |
   And I click on "Go"
   Then I should see "Academic goals"

   When I set the following fields to these values:
   | Search | meow |
   And I click on "Go"
   Then I should see "Career goals"

   When I set the following fields to these values:
   | Search | gym |
   And I click on "Go"
   Then I should see "Personal goals"

   When I set the following fields to these values:
   | Search | alphabet |
   And I click on "Go"
   Then I should see "Academic skills"

   When I set the following fields to these values:
   | Search | whistle |
   And I click on "Go"
   Then I should see "Personal skills"

   When I set the following fields to these values:
   | Search | axe |
   And I click on "Go"
   Then I should see "Work skills"

# Search by name of interest
   When I set the following fields to these values:
   | Search | kite |
   And I click on "Go"
   Then I should see "Interests"

# Search by text in cover letter
   When I set the following fields to these values:
   | Search | te reo |
   And I click on "Go"
   Then I should see "Cover letter"

# TODO: Search by name of education institution (Currently only appears when searching with "Education")
    When I set the following fields to these values:
  # | Search | catalystonia |
    | Search | Education |
    And I click on "Go"
    Then I should see "Education history"

# TODO: Search by name of employer (Currently only appears when searching with "Employment")
    When I set the following fields to these values:
    # | Search | eggman |
    | Search | Employment |
    And I click on "Go"
    Then I should see "Employment history"

# TODO: Search by address (Currently only appears when searching with "Contact")
    When I set the following fields to these values:
    # | Search | hutt |
    | Search | Contact |
    And I click on "Go"
    Then I should see "Contact information"
    And I log out
