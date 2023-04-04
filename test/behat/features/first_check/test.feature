@javascript @core @blocktype
Feature: Creating pages with blocks
    As a user
    I want to add multiple pages with a selection of blocks as a background step
    As a group admin
    I want to add a page with blocks as a background step

Background:
    # The * in available fields are compulsory
    # extra tables not currently in this feature file: ...the following ~
    # institutions, group memberships, institution memberships, permissions, messages, ... exist

    Given the following site settings are set:
    | field | value |
    | licensemetadata | 0 |

    Given the following "users" exist:
    # Available fields: username*, password*, email*, firstname*, lastname*, institution, role*, authname, remoteusername, studentid, preferredname, town, country, occupation
    | username | password | email             | firstname | lastname | institution | authname | role |
    | UserA    | Kupuh1pa!| UserA@example.org | Painterio | Mahara   | mahara      | internal | admin |
    | UserB    | Kupuh1pa!| UserB@example.org | Mechania  | Mahara   | mahara      | internal | member |

    And the following "groups" exist:
    # Available fields: name*, owner*, description, grouptype*, editroles* open, controlled, request, invitefriends, suggestfriends, submittableto, allowarchives,
    #                 editwindowstart, editwindowstart, editwindowend, members, staff, admins, institution, public
    | name   | owner | description           | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
    | Group1 | UserB | Group1 owned by UserB | standard  | ON   | OFF           | all       | ON            | OFF           | UserA   |       |

    And the following "personalinformation" exist:
    # Available fields: username*, password*, email, firstname, lastname, institution, authname, role
    | user  | dateofbirth | placeofbirth | citizenship | visastatus | gender | maritalstatus |
    | UserA | 01/01/2000  | Italy        | New Zealand |            |        |               |
    | UserB | 01/01/2018  | Germany      | New Zealand |            |        |               |

    And the following "goals and skills" exist:
    | user  | goaltype/skilltype  | title        | description           | attachment  |
    | UserA | academicgoal        | fix lateness | pack bag night before | Image1.jpg  |
    | UserA | careergoal          | meow         | cat a lyst            | users.csv   |
    | UserA | personalgoal        | gym shark    | do do do              | Image2.png  |
    | UserA | academicskill       | alphabet     | abc                   | 3images.zip |
    | UserA | personalskill       | whistle      | *inset whistle noise  | Image1.jpg  |
    | UserA | workskill           | team work    | axe throwing?         | users.csv   |
    | UserB | academicgoal        | academi doooo| description goal/skill| Image2.png  |
    | UserB | careergoal          | careerg doooo| description goal/skill| groups.csv  |
    | UserB | personalgoal        | persona doooo| description goal/skill| Image1.jpg  |
    | UserB | academicskill       | academi doooo| description goal/skill| users.csv   |
    | UserB | personalskill       | persona doooo| description goal/skill| Image2.png  |
    | UserB | workskill           | workski doooo| description goal/skill| groups.csv  |

    And the following "interests" exist:
    # Available fields: user*, interest*, description
    | user  | interest          | description                 |
    | UserA | FOSS              | exciting open source stuff! |
    | UserA | Mahara            | awesome e-portfolio system  |
    | UserA | Coffee and Coding |  |

    And the following "coverletters" exist:
    # Available fields: user*, content*
    | user  | content |
    | UserA |UserA In Te Reo Māori, "mahara" means "to think, thinking, thought" and that fits the purpose of Mahara very well. Having been started in New Zealand, it was fitting to choose a Māori word to signify the concept of the ePortfolio system |
    | UserB |UserB In Te Reo Māori, "mahara" means "to think, thinking, thought" and that fits the purpose of Mahara very well. Having been started in New Zealand, it was fitting to choose a Māori word to signify the concept of the ePortfolio system |

    And the following "educationhistory" exist:
    # Available fields: user, startdate, enddate, institution, institutionaddress, qualtype, qualname,qualdescription,attachment
    | user  | institution  | startdate | enddate  | qualdescription | attachment |
    | UserA | Catalystania | 12/12/12  | 12/12/21 | 9 years         | Image2.png |
    | UserB | Catalystonia | 21/10/21  | 10/12/26 | educationnn     | Image2.png |
    | UserA | Catalyst High| 12/12/20  | 12/12/21 | 9 years         | Image2.png |
    | UserB | Catalyst High| 21/10/20  | 10/12/26 | educationnn     | Image2.png |

    And the following "employmenthistory" exist:
    # Available fields: user, startdate, enddate, employer, employeraddress, jobtitle, positiondescription
    | user  | employer | startdate | enddate | jobtitle   | positiondescription    | attachment |
    | UserA | Eggman   | 01/02/03  |         | crystal dr | locating magic crystals| Image1.jpg |
    | UserB | Cat      | 02/02/00  |         | Cat sitter | pat kittens            | Image1.jpg |

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
    | user  | date     | title                                     | contribution| description                                                                         | attachment |
    | UserA | 05/05/50 | The Life-Changing Magic of not Tidying Up | co-author   | seven million copies worldwide and have been translated into thirty-eight languages.| Image3.png |
    | UserB | 05/05/50 | The Life-Changing Magic of not Tidying Up | co-author   | seven million copies worldwide and have been translated into thirty-eight languages.| Image3.png |

    And the following "professionalmemberships" exist:
    # Available fields: user, startdate, enddate, title, description, attachment
    | user  | startdate   | title                       | description        | attachment |
    | UserA | 20/02/2008  | cat art company coordinator | catch up with cats | Image3.png |
    | UserB | 20/02/2008  | cat art company catcher     | catch fish for cats| Image3.png |


    And the following "forums" exist:
    # Available fields: title*, description*, group*, creator*
    | group  | title     | description          | creator |
    | Group1 | unicorns! | magic mahara unicorns| UserB   |

    And the following "forumposts" exist:
    # Available fields: group*, forum, topic, subject, message*, user*
    | group  | forum      | topic     | subject    | message                     | user  |
    | Group1 | unicorns!  | topic one | hello      | mahara unicorns unite!      | UserB |
    | Group1 | unicorns!  | topic one | whatsup    | yay! mahara unicorns unite! | UserB |
    | Group1 | unicorns!  | topic one | cheer on   | woo! mahara unicorns unite! | UserB |
    | Group1 |            | topic one | cheer on   | 10 papercranes, let's go!   | UserB |
    | Group1 | unicorns!  | topic one | extra subj | 100 papercranes, let's go!  | UserB |
    | Group1 | unicorns!  |           | origami    | 1000 papercranes, let's go! | UserB |
    | Group1 |            |           | postpost   | 1000 papercranes, let's go! | UserB |

    And the following "pages" exist:
    # Available fields: title*, description, ownertype*, ownername*, layout, tags
    | title        | description  | ownertype | ownername |
    | Page One A   | UserA Page 1 | user      | UserA     |
    | Page Two A   | UserA Page 2 | user      | UserA     |
    | Page Three A | UserA Page 3 | user      | UserA     |
    | Page Four A  | UserA Page 4 | user      | UserA     |
    | Page One B   | UserB Page 1 | user      | UserB     |
    | Page One Grp | Group Page 1 | group     | Group1    |

    And the following "pagecomments" exist:
    # Available fields: user*, comment*, page*, attachment, private, group (compulsory for group pages)
    | user  | page         | comment                        | private |  group  |
    | UserB | Page Two A   | Comment by User B on page      | false   |         |
    | UserB | Page Three A | Hi, I am a comment by User B   | false   |         |
    | UserA | Page Three A | Hi, I am a comment by the owner| false   |         |
    | UserA | Page One Grp | Hi, I am a comment by UserA    | false   |  Group1 |

    And the following "collections" exist:
    # Available fields: title*, description, ownertype*, ownername*, pages
    | title          | ownertype | ownername | description | pages                                |
    | collection one | user      | UserA     | desc of col | Page One A, Page Two A, Page Three A |

    And the following "journals" exist:
    # Available fields: owner*, ownertype*, title*, description, tags
    | owner | ownertype | title   | description      | tags               |
    | UserA | user      | journal1| this is journal1 | amber,brown,cobalt |
    | Group1| group     | journal2| this is journal2 | amber,brown,cobalt |

    And the following "journalentries" exist:
    # Available fields: owner*, ownertype*, title*, entry*, blog, tags, draft
    |  owner  |  ownertype |  title       |  entry                  |  blog     |  tags      |  draft |
    |  UserA  |  user      |  Entry One   |  This is my entry One   |  journal1 |  cats,dogs |  0     |
    |  UserA  |  user      |  Entry Two   |  This is my entry Two   |  journal1 |  cats,dogs |  0     |
    |  UserA  |  user      |  Entry Three |  This is my entry Three |  journal1 |  cats,dogs |  0     |
    |  UserA  |  user      |  Entry Four  |  This is my entry Four  |  journal1 |  cats,dogs |  0     |
    |  UserA  |  user      |  Entry Five  |  This is my entry Five  |  journal1 |  cats,dogs |  0     |
    |  UserA  |  user      |  Entry Mini  |  This is my min fields  |           |            |  0     |
    |  Group1 |  group     |  Group e1    |  This is my group entry |  journal2 |            |  0     |

    And the following "plans" exist:
    # Available fields: owner*, ownertype*, title*, description, tags
    | owner   | ownertype | title      | description           | tags      |
    | UserA   | user      | Plan One   | This is my plan one   | cats,dogs |
    | UserA   | user      | Plan Two   | This is my plan two   | cats,dogs |
    | UserA   | user      | Plan Mini  |                       |           |
    | Group1 | group      | Group Plan | This is my group plan | unicorn   |

    And the following "tasks" exist:
    # Available fields: owner*, ownertype*, plan*, title*, completed*, completiondate*, description, tags
    | owner | ownertype | plan     | title      | description         | completiondate | completed | tags      |
    | UserA | user      | Plan One | Task One a | Task 1a Description | 12/12/19       | false     | cats,dogs |
    | UserA | user      | Plan One | Task One b | Task 1b Description | 12/01/19       | true      | cats,dogs |
    | UserA | user      | Plan Two | Task Two a | Task 2a Description | 12/10/19       | true      | cats,dogs |
    | UserA | user      | Plan Two | Task Two b | Task 2b Description | 11/01/19       | true      | cats,dogs |
    | UserA | user      | Plan Two | Task Two c | Task 2c Description | 22/02/19       | true      | cats,dogs |
    | UserA | user      | Plan Two | Task Min a |                     | 22/02/19       | false     |           |


    And the following "blocks" exist:
    # Available fields: title*, type*, data*, page*, retractable*
    # Page One A
    | title                | type           | page          |retractable | data |
    | Text                 | text           | Page One A    | yes        | textinput=This is some text;tags=texttag |
    | Image JPG            | image          | Page One A    | no         | attachment=Image1.jpg; width=100;tags=imagetag |
    | Image PNG            | image          | Page One A    | no         | attachment=Image2.png |
    | Files to download    | filedownload   | Page One A    | auto       | attachments=mahara_about.pdf |
    | Files to download    | filedownload   | Page One A    | no         | attachments=mahara_about.pdf,Image2.png |
    | External Feed - News | externalfeed   | Page One A    | No         | source=https://stuff.co.nz/rss;count=5 |
    | External Feed - Food | externalfeed   | Page One A    | no         | source=https://www.bonappetit.com/feed/rss |
    | External Feed - Tech | externalfeed   | Page One A    | no         | source=feeds.feedburner.com/geekzone;count=3;tags=cat,a,lyst |
    | Social Media         | socialprofile  | Page One A    | no         | sns=instagram,twitter,facebook,tumblr,pinterest,mysocialmedia |

    And the following "blocks" exist:
    # Page Two A
    | title                   | type           | page          |retractable | data |
    | Image                   | image          | Page Two A    | no         | attachment=Image3.png |
    | Files to download       | filedownload   | Page Two A    | no         | attachments=mahara_about.pdf,Image2.png,testvid3.mp4,mahara.mp3 |
    | External Video          | externalvideo  | Page Two A    | no         | source=https://youtu.be/yRxFm70nOrY;tags=jen,from,the,house |
    | Navigation              | navigation     | Page Two A    | no         | collection=collection one;copytoall=yes |
    | Social Media            | socialprofile  | Page Two A    | no         | sns=instagram,twitter,facebook,tumblr,pinterest,mysocialmedia |
    | Pdf                     | pdf            | Page Two A    | no         | attachment=mahara_about.pdf |
    | Recent Forum Posts      |recentforumposts| Page Two A    | no         | groupname=Group1;maxposts=3 |
    | External Video          | externalvideo  | Page Two A    | no         | source=https://youtu.be/k5t5PD5F8Wo |
    | Note/Textbox 1          | textbox        | Page Two A    | no         | notetitle=secretnote;text=ma ha ha ha ra!;tags=mahara,araham;attachments=Image3.png,Image2.png,Image1.jpg;allowcomments=yes |
    | Note/textbox ref:1      | textbox        | Page Two A    | no         | existingnote=secretnote |
    | Note/Textbox copy:1     | textbox        | Page Two A    | no         | existingnote=secretnote;allowcomments=yes;copynote=true;notetitle=newsecretnote |
    | Profile Information     | profileinfo    | Page Two A    | no         | introtext =Mahara unicorn here! Nice to meet you :);profileicon=Image1.jpg |
    | Profile Information     | profileinfo    | Page Two A    | no         | introtext =Mahara unicorn here! Nice to meet you :);profileicon=Image2.png |
    | Résumé                  | entireresume   | Page Two A    | no         | tags=mahara |
    | Résumé: Personal Goal   | resumefield    | Page Two A    | no         | artefacttype=personalgoal |
    | Résumé: Work Skill      | resumefield    | Page Two A    | no         | artefacttype=workskill |
    | Résumé: Interest        | resumefield    | Page Two A    | no         | artefacttype=interest |
    | Résumé: Achievements    | resumefield    | Page Two A    | no         | artefacttype=certification |
    | Résumé: Employment Hist.| resumefield    | Page Two A    | no         | artefacttype=employmenthistory |
    | Résumé: Books           | resumefield    | Page Two A    | no         | artefacttype=book |
    | Résumé: Memberships     | resumefield    | Page Two A    | no         | artefacttype=membership |
    | Résumé: Education Hist. | resumefield    | Page Two A    | no         | artefacttype=educationhistory |


    And the following "blocks" exist:
    # Page Three A
    | title                   | type           | page          |retractable | data |
    | Blog/Journal         | blog           | Page Three A     | no         | copytype=nocopy;count=5;journaltitle=journal1 |
    | Blogpost/JournalEntry| blogpost       | Page Three A     | no         | copytype=nocopy;journaltitle=journal1;entrytitle=Entry Two |
    | Comments             | comment        | Page Three A     | no         | |
    | Peer Assessment      | peerassessment | Page Three A     | auto       | |
    | Creative Commons     | creativecommons| Page Three A     | no         | commercialuse=yes;license=3.0;allowmods=no |
    | Navigation           | navigation     | Page Three A     | no         | collection=collection one;copytoall=yes |
    | Plans                | plans          | Page Three A     | no         | plans=Plan One,Plan Two;tasksdisplaycount=10 |
    | Internal Media: Video| internalmedia  | Page Three A     | no         | attachment=testvid3.mp4 |
    | Internal Media: Audio| internalmedia  | Page Three A     | no         | attachment=mahara.mp3 |

    And the following "blocks" exist:
    # Page Four A
    | title                   | type           | page          |retractable | data |
    | Recent journal entries| recentposts    | Page Four A   | no         | journaltitle=journal1;maxposts=10 |
    | Tagged journal entries| taggedposts    | Page Four A   | no         | tags=cats; maxposts=5;showfullentries=yes;copytype=nocopy |
    | Recent journal entries| recentposts    | Page Four A    | no         | journaltitle=journal1;maxposts=10 |
    | Tagged journal entries| taggedposts    | Page Four A    | no         | tags=cats; maxposts=5;showfullentries=yes;copytype=nocopy |
    | Open Badges         |openbadgedisplayer| Page Four A    | no         | |

    And the following "blocks" exist:
    # Page One B
    | title                | type           | page          |retractable | data |
    | Gallery - style 1    | gallery        | Page One B    | no         | attachments=Image1.jpg,Image3.png,Image3.png,Image2.png,Image1.jpg;imagesel=2;showdesc=yes |
    | Gallery - style 2    | gallery        | Page One B    | yes        | attachments=Image3.png,Image2.png,Image1.jpg,Image1.jpg;imagesel=2;showdesc=yes;imagestyle=2 |
    | Gallery - style 3    | gallery        | Page One B    | yes        | attachments=Image3.png,Image2.png,Image3.png,Image1.jpg,Image1.jpg;imagesel=2;showdesc=no;imagestyle=3 |
    | Folder               | folder         | Page One B    | no         | dirname=myfolder;attachments=mahara_about.pdf,Image2.png,Image1.jpg,Image3.png,mahara.mp3 |
    | Some HTML            | html           | Page One B    | yes        | attachment=test_html.html |
    | Profile Information  | profileinfo    | Page One B    | no         | introtext =Mahara unicorn here! Nice to meet you :);profileicon=Image3.png |
    | Résumé               | entireresume   | Page One B    | no         | tags=mahara |


    And the following "blocks" exist:
    | title                   | type           | page          |retractable | data |
    | GoogleApps: Google Maps | googleapps     | Page One Grp      | no         | googleapp=<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2997.861064367898!2d174.77176941597108!3d-41.29012814856559!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6d38afd6326bfda5%3A0x5c0d858838e52d7a!2sCatalyst!5e0!3m2!1sen!2snz!4v1550707486290" width="800" height="600" frameborder="0" style="border:0" allowfullscreen></iframe>;height=200;tags=cat,dog,monkeys |
    | GoogleApps: Google Cal. | googleapps     | Page One Grp      | no         | https://calendar.google.com/calendar/embed?src=en.new_zealand%23holiday%40group.v.calendar.google.com&ctz=Pacific%2FAuckland |

Scenario: login as different users to see blocktypes and interaction
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page One A"
    And I go to portfolio page "Page Two A"
    And I go to portfolio page "Page Three A"
    And I go to portfolio page "Page Four A"
    And I go to portfolio page "Page One Grp"
    And I log out

    Then I log in as "UserB" with password "Kupuh1pa!"
    And I go to portfolio page "Page One B"
