@javascript @core @core_administration @manual
Feature: Search for different blocktypes with Elasticsearch 7
In order to index and search the site using elasticsearch7
As a user
So I can check that search functionality is picking up the titles of various blocktypes

Background:
 Given the following plugin settings are set:
    | plugintype | plugin         | field         | value      |
    | search     | elasticsearch7 | indexname     | behattest  |
    | search     | elasticsearch7 | types         | usr,interaction_instance,interaction_forum_post,group,view,artefact,block_instance,collection |
    | search     | elasticsearch7 | cronlimit     | 500        |
    | search     | elasticsearch7 | shards        | 5          |
    | search     | elasticsearch7 | replicashards | 0          |

 And the following site settings are set:
    | field        | value          |
    | searchplugin | elasticsearch7 |

 And the following "users" exist:
    | username | password  | email               | firstname | lastname   | institution | authname | role   |
    | meryl    | Kupuh1pa! | meryl@example.org   | Meryl     | Streep     | mahara      | internal | member |
    | jack     | Kupuh1pa! | jack@example.org    | Jack      | Nicholson  | mahara      | internal | member |

 And the following "pages" exist:
    | title          | description        | ownertype | ownername |
    | Films          | All about my films | user      | meryl     |

 And the following "collections" exist:
   # Available fields: title*, description, ownertype*, ownername*, pages
   | title          | ownertype | ownername | description | pages |
   | Complete Works | user      | meryl     | About me    | Films |

 And the following "plans" exist:
   # Available fields: owner*, ownertype*, title*, description, tags
   | owner | ownertype | title        | description | tags |
   | meryl | user      | Voice Acting | | |
   | meryl | user      | Auditions    | | |

 And the following "journals" exist:
   # Available fields: owner*, ownertype*, title*, description*, tags
   | owner | ownertype | title         | description                       |
   | meryl | user      | Meryl's Diary | The thoughts and musings of Meryl |

 And the following "journalentries" exist:
   # Available fields: owner*, ownertype*, title*, entry*, blog*, tags*, draft*
   | owner | ownertype | title              | entry                                      | blog           | draft | tags |
   | meryl | user      | The day I was born | This was a pivotal moment in human history | Meryl's Diary  | 0     |      |

 And the following "groups" exist:
   # Available fields: name*, owner*, description, grouptype*, editroles*
   | name          | owner | description            | grouptype | editroles | members |
   | Actor's Guild | meryl | Actors only            | standard  | all       | jack    |

 And the following "forums" exist:
   # Available fields: title*, description*, group*, creator*
   | group         | title      | description                                 | creator |
   | Actor's Guild | Hot Topics | A place to discuss why everyone worships us | meryl   |

 And the following "forumposts" exist:
   # Available fields: group*, forum, topic, subject, message*, user*
   | group         | forum      | topic   | subject | message                                            | user  |
   | Actor's Guild | Hot Topics | Elitism | Make-up | I think it's because we always have perfect makeup | meryl |

 And the following "goals and skills" exist:
    | user  | goaltype/skilltype  | title        | description           | attachment  |
    | meryl | personalgoal        | Live forever | Look into cryogenesis | |

 And the following "blocks" exist:
    # Available fields: title*, type*, data*, page*, retractable*
    # 28 different block types
    | title                          | type               | page  | retractable | data  |
    | Adaptation                     | text               | Films | no          | textinput=A film about orchids |
    | The Hours                      | image              | Films | no          | attachment=Image1.jpg |
    | The Devil Wears Prada          | filedownload       | Films | no          | attachments=mahara_about.pdf |
    | Mamma Mia!                     | folder             | Films | no          | dirname=mammamia;attachments=mahara_about.pdf,Image2.png,Image1.jpg,Image3.png,mahara.mp3 |
    | Out of Africa                  | gallery            | Films | no          | attachments=Image3.png,Image2.png,Image1.jpg,Image1.jpg;imagesel=2;showdesc=yes;imagestyle=2 |
    | Sophie's Choice                | internalmedia      | Films | no          | attachment=mahara.mp3 |
    | Don't Look Up                  | pdf                | Films | no          | attachment=mahara_about.pdf |
    | Little Women                   | html               | Films | no          | attachment=test_html.html |
    | Let Them All Talk              | blog               | Films | no          | journaltitle=Meryl's Diary |
    | The Laundromat                 | blogpost           | Films | no          | journaltitle=Meryl's Diary;entrytitle=The day I was born |
    | Mary Poppins Returns           | recentposts        | Films | no          | journaltitle=Meryl's Diary |
    | The Post                       | taggedposts        | Films | no          | tags=cats; maxposts=5;showfullentries=yes; |
    | Suffragette City               | comment            | Films | no          | |
    | Ricki and the Flash            | peerassessment     | Films | no          | |
   #| Into the Woods                 | signoff            | Films | no          | | (not supported in Behat)
    | The Giver                      | creativecommons    | Films | no          | commercialuse=yes;license=3.0;allowmods=no |
    | The Homesman                   | navigation         | Films | no          | collection=Complete Works |
    | Hope Springs                   | plans              | Films | no          | plans=Voice Acting,Auditions;tasksdisplaycount=10 |
    | The Iron Lady                  | recentforumposts   | Films | no          | groupname=Actor's Guild;maxposts=3 |
    | It's Complicated               | textbox            | Films | no          | notetitle=secretnote;text=ma ha ha ha ra!;tags=mahara,araham;attachments=Image3.png,Image2.png,Image1.jpg; |
    | Fantastic Mr. Fox              | profileinfo        | Films | no          | introtext =Mahara unicorn here! Nice to meet you :);profileicon=Image1.jpg |
    | Julie & Julia                  | socialprofile      | Films | no          | sns=instagram,twitter,facebook,tumblr,pinterest,mysocialmedia |
    | A Series of Unfortunate Events | entireresume       | Films | no          | |
    | Lions for Lambs                | resumefield        | Films | no          | artefacttype=personalgoal |
    | The Manchurian Candidate       | externalfeed       | Films | no          | source=https://stuff.co.nz/rss;count=5 |
    | Stuck on You                   | externalvideo      | Films | no          | source=https://youtu.be/yRxFm70nOrY; |
    | Dark Matter                    | googleapps         | Films | no          | googleapp=<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2997.861064367898!2d174.77176941597108!3d-41.29012814856559!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6d38afd6326bfda5%3A0x5c0d858838e52d7a!2sCatalyst!5e0!3m2!1sen!2snz!4v1550707486290" width="800" height="600" frameborder="0" style="border:0" allowfullscreen></iframe>;height=200; |
    | The Ant Bully                  | openbadgedisplayer | Films | no          | |

Scenario: Testing search with different blocktypes
   Given I log in as "admin" with password "Kupuh1pa!"
   And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
   And I click on "Select all"
   And I click on "Save"
   And I click on "Reset"
   And I log out
   And I log in as "meryl" with password "Kupuh1pa!"

# Blocktype: text
   Given I set the following fields to these values:
   | Search | adaptation |
   And I click on "Go"
   Then I should see "orchids"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: image
   Given I set the following fields to these values:
   | Search | hours |
   And I click on "Go"
   Then I should see "The Hours"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: filedownload
   Given I set the following fields to these values:
   | Search | devil |
   And I click on "Go"
   Then I should see "The Devil Wears Prada"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: folder
   Given I set the following fields to these values:
   | Search | mamma |
   And I click on "Go"
   Then I should see "Mamma Mia!"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: gallery
   Given I set the following fields to these values:
   | Search | africa |
   And I click on "Go"
   Then I should see "Out of Africa"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: internalmedia
   Given I set the following fields to these values:
   | Search | choice |
   And I click on "Go"
   Then I should see "Sophie's Choice"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: pdf
   Given I set the following fields to these values:
   | Search | look |
   And I click on "Go"
   Then I should see "Don't Look Up"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: html
   Given I set the following fields to these values:
   | Search | women |
   And I click on "Go"
   Then I should see "Little Women"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: blog
   Given I set the following fields to these values:
   | Search | talk |
   And I click on "Go"
   Then I should see "Let Them All Talk"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: blogpost
   Given I set the following fields to these values:
   | Search | laundromat |
   And I click on "Go"
   Then I should see "The Laundromat"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: recentposts
   Given I set the following fields to these values:
   | Search | poppins |
   And I click on "Go"
   Then I should see "Mary Poppins Returns"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: taggedposts
   Given I set the following fields to these values:
   | Search | post |
   And I click on "Go"
   Then I should see "The Post"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: comment
   Given I set the following fields to these values:
   | Search | suffragette |
   And I click on "Go"
   Then I should see "Suffragette City"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: peerassessment
   Given I set the following fields to these values:
   | Search | ricki |
   And I click on "Go"
   Then I should see "Ricki and the Flash"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: creativecommons
   Given I set the following fields to these values:
   | Search | giver |
   And I click on "Go"
   Then I should see "The Giver"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: navigation
   Given I set the following fields to these values:
   | Search | homesman |
   And I click on "Go"
   Then I should see "The Homesman"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: plans
   Given I set the following fields to these values:
   | Search | hope |
   And I click on "Go"
   Then I should see "Hope Springs"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: recentforumposts
   Given I set the following fields to these values:
   | Search | iron |
   And I click on "Go"
   Then I should see "The Iron Lady"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: textbox (note)
   Given I set the following fields to these values:
   | Search | complicated |
   And I click on "Go"
   Then I should see "It's Complicated"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: profileinfo
   Given I set the following fields to these values:
   | Search | fox |
   And I click on "Go"
   Then I should see "Fantastic Mr. Fox"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: socialprofile
   Given I set the following fields to these values:
   | Search | julie |
   And I click on "Go"
   Then I should see "Julie & Julia"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: entireresume
   Given I set the following fields to these values:
   | Search | unfortunate |
   And I click on "Go"
   Then I should see "A Series of Unfortunate Events"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: resumefield
   Given I set the following fields to these values:
   | Search | lambs |
   And I click on "Go"
   Then I should see "Lions for Lambs"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: externalfeed
   Given I set the following fields to these values:
   | Search | candidate |
   And I click on "Go"
   Then I should see "The Manchurian Candidate"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: externalvideo
   Given I set the following fields to these values:
   | Search | stuck |
   And I click on "Go"
   Then I should see "Stuck on You"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: googleapps
   Given I set the following fields to these values:
   | Search | dark |
   And I click on "Go"
   Then I should see "Dark Matter"
   When I click on "Films"
   Then I should see "Adaptation"

# Blocktype: openbadgedisplayer
   Given I set the following fields to these values:
   | Search | bully |
   And I click on "Go"
   Then I should see "The Ant Bully"
   When I click on "Films"
   Then I should see "Adaptation"
   And I log out
