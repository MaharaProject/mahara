@javascript @core @blocktype
Feature: Creating a page with blocks
    As a user
    I want to add a page with blocks as a background step
    As a group admin
    I want to add a page with blocks as a background step

Background:
    Given the following "users" exist:
    | username | password | email             | firstname | lastname | institution | authname | role |
    | UserA    | Kupuh1pa!| UserA@example.org | Painterio | Mahara   | mahara      | internal | member |
    | UserB    | Kupuh1pa!| UserB@example.org | Mechania  | Mahara   | mahara      | internal | member |

    And the following "personalinformation" exist:
    | user  | dateofbirth | placeofbirth | citizenship | visastatus | gender | maritalstatus |
    | UserA | 01/01/2000  | Italy        | New Zealand |            |        |               |
    | UserB | 01/01/2018  | Germany      | New Zealand |            |        |               |

    And the following "goals and skills" exist:
    | user  | goaltype/skilltype  | title        | description           |
    | UserA | academicgoal        | fix lateness | pack bag night before |
    | UserA | careergoal          | meow         | cat a lyst            |
    | UserA | personalgoal        | gym shark    | do do do              |
    | UserA | academicskill       | alphabet     | abc                   |
    | UserA | personalskill       | whistle      | *inset whistle noise  |
    | UserA | workskill           | team work    | axe throwing?         |
    | UserB | academicgoal        | academi doooo| de sc ri p t i o n nn |
    | UserB | careergoal          | careerg doooo| de sc ri p t i o n nn |
    | UserB | personalgoal        | persona doooo| de sc ri p t i o n nn |
    | UserB | academicskill       | academi doooo| de sc ri p t i o n nn |
    | UserB | personalskill       | persona doooo| de sc ri p t i o n nn |
    | UserB | workskill           | workski doooo| de sc ri p t i o n nn |

    And the following "interests" exist:
    | user  | interest  | description                 |
    | UserA | FOSS      | exciting open source stuff! |
    | UserA | Mahara    | awesome e-portfolio system  |
    | UserA | Coding and Coffee |  |

    And the following "coverletters" exist:
    | user  | content |
    | UserA |UserA In Te Reo Māori, "mahara" means "to think, thinking, thought" and that fits the purpose of Mahara very well. Having been started in New Zealand, it was fitting to choose a Māori word to signify the concept of the ePortfolio system |
    | UserB |UserB In Te Reo Māori, "mahara" means "to think, thinking, thought" and that fits the purpose of Mahara very well. Having been started in New Zealand, it was fitting to choose a Māori word to signify the concept of the ePortfolio system |

    And the following "educationhistory" exist:
    | user  | institution  | startdate | enddate  | qualdescription |
    | UserA | Catalystania | 12/12/12  | 12/12/21 | 9 years         |
    | UserB | Catalystonia | 21/10/21  | 10/12/26 | educationnn     |
    | UserA | Catalyst High| 12/12/20  | 12/12/21 | 9 years         |
    | UserB | Catalyst High| 21/10/20  | 10/12/26 | educationnn     |

    And the following "employmenthistory" exist:
    | user  | employer | startdate | enddate | jobtitle   | positiondescription    |
    | UserA | Eggman   | 01/02/03  |         | crystal dr | locating magic crystals|
    | UserB | Cat      | 02/02/00  |         | Cat sitter | pat kittens            |

    And the following "contactinformation" exist:
    | user  | email            | mobilenumber |
    | UserA | userA@mahara.com | 01234567890  |

    And the following "certifications and accreditations" exist:
    | user  | date     | title               | description |
    | UserA | 02/02/80 | European Witchcraft | While the streets may be education enough for real gangsters, this course aims to teach students about the history and culture of the mafia around the world. [Williams College] |
    | UserB | 02/02/80 | European Witchcraft | While the streets may be education enough for real gangsters, this course aims to teach students about the history and culture of the mafia around the world. [Williams College] |

    And the following "books and publications" exist:
    | user  | date     | title                                     | contribution| description |
    | UserA | 05/05/50 | The Life-Changing Magic of not Tidying Up | co-author   | seven million copies worldwide and have been translated into thirty-eight languages.|
    | UserB | 05/05/50 | The Life-Changing Magic of not Tidying Up | co-author   | seven million copies worldwide and have been translated into thirty-eight languages.|

    And the following "professionalmemberships" exist:
    | user  | startdate   | title                       | description        |
    | UserA | 20/02/2008  | cat art company coordinator | catch up with cats |
    | UserB | 20/02/2008  | cat art company catcher     | catch fish for cats|

    And the following "groups" exist:
    | name   | owner | description           | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
    | Group1 | UserB | Group1 owned by UserB | standard  | ON   | OFF           | all       | ON            | OFF           | UserA   |       |

    And the following "forums" exist:
    | group  | title     | description          | creator |
    | Group1 | unicorns! | magic mahara unicorns| UserB   |

    And the following "forumposts" exist:
    | group  | forum      | topic     | subject    | message                     | user  |
    | Group1 | unicorns!  | topic one |            | mahara unicorns unite!      | UserB |
    | Group1 | unicorns!  | topic one |            | yay! mahara unicorns unite! | UserB |
    | Group1 | unicorns!  | topic one | cheer on   | woo! mahara unicorns unite! | UserB |
    | Group1 |            | topic one | cheer on   | 10 papercranes, let's go!   | UserB |
    | Group1 | unicorns!  | topic one | extra subj | 100 papercranes, let's go!  | UserB |
    | Group1 | unicorns!  |           | origami    | 1000 papercranes, let's go! | UserB |

    And the following "pages" exist:
    | title         | description | ownertype | ownername |
    | Page UserA    | Page 01     | user      | UserA     |
    | Page UserB    | Page 01     | user      | UserB     |
    | Page Grp1     | Page 01     | group     | Group1    |
    | Page One      | test 01     | user      | UserA     |
    | Page Two      | test 01     | user      | UserA     |

    And the following "collections" exist:
    | title          | ownertype | ownername | description | pages             |
    | collection one | user      | UserA     | desc of col |Page One,Page Two  |


    And the following "journals" exist:
    | owner | ownertype | title   | description      | tags               |
    | UserA | user      | journal1| this is journal1 | amber,brown,cobalt |
    | Group1| group     | journal2| this is journal1 | amber,brown,cobalt |

    And the following "journalentries" exist:
    | owner   | ownertype | title       | entry                  | blog     | tags      | draft |
    | UserA   | user      | Entry One   | This is my entry  One  | journal1 | cats,dogs | 0     |
    | UserA   | user      | Entry Two   | This is my entry Two   | journal1 | cats,dogs | 0     |
    | UserA   | user      | Entry Three | This is my entry Three | journal1 | cats,dogs | 0     |
    | UserA   | user      | Entry Four  | This is my entry Four  | journal1 | cats,dogs | 0     |
    | UserA   | user      | Entry Five  | This is my entry Five  | journal1 | cats,dogs | 0     |
    | Group1  | group     | Group e1    | This is my group entry | journal2 |           | 0     |

    And the following "plans" exist:
    | owner   | ownertype | title      | description           | tags      |
    | UserA   | user      | Plan One   | This is my plan one   | cats,dogs |
    | UserA   | user      | Plan Two   | This is my plan two   | cats,dogs |
    | Group1 | group      | Group Plan | This is my group plan | unicorn   |

    And the following "tasks" exist:
    | owner | ownertype | plan     | title      | description         | completiondate | completed | tags      |
    | UserA | user      | Plan One | Task One a | Task 1a Description | 12/12/19       | false     | cats,dogs |
    | UserA | user      | Plan One | Task One b | Task 1b Description | 12/01/19       | true      | cats,dogs |
    | UserA | user      | Plan Two | Task Two a | Task 2a Description | 12/10/19       | true      | cats,dogs |
    | UserA | user      | Plan Two | Task Two b | Task 2b Description | 11/01/19       | true      | cats,dogs |
    | UserA | user      | Plan Two | Task Two c | Task 2c Description | 22/02/19       | true      | cats,dogs |


    And the following "blocks" exist:
    | title                | type           | page          |retractable | data |
    | Text                 | text           | Page UserA    | yes        | textinput=This is some text |
    | Image JPG            | image          | Page UserA    | no         | attachment=Image1.jpg; width=100 |
    | Image PNG            | image          | Page UserA    | no         | attachment=Image2.png |
    | Files to download    | filedownload   | Page UserA    | auto       | attachments=mahara_about.pdf |
    | Files to download    | filedownload   | Page UserA    | no         | attachments=mahara_about.pdf,Image2.png |
    | External Feed - News | externalfeed   | Page UserA    | No         | source=http://rss.nzherald.co.nz/rss/xml/nzhtsrsscid_000000698.xml;count=5 |
    | External Feed - Food | externalfeed   | Page UserA    | no         | source=http://www.thekitchenmaid.com/feed;count=3 |
    | Social Media         | socialprofile  | Page UserA    | no         | sns=instagram,twitter,facebook,tumblr,pinterest,mysocialmedia |
    | Image                | image          | Page Grp1     | no         | attachment=Image3.png |
    | Files to download    | filedownload   | Page Grp1     | no         | attachments=mahara_about.pdf,Image2.png,testvid3.mp4,mahara.mp3 |
    | External Video       | externalvideo  | Page Grp1     | no         | source=https://youtu.be/yRxFm70nOrY |
    | Navigation           | navigation     | Page Grp1     | no         | collection=collection one;copytoall=yes |
    | Social Media         | socialprofile  | Page UserB    | no         | sns=instagram,twitter,facebook,tumblr,pinterest,mysocialmedia |
    | Gallery - style 1    | gallery        | Page UserB    | no         | attachments=Image1.jpg,Image3.png,Image3.png,Image2.png,Image1.jpg;imagesel=2;showdesc=yes;width=75;imagestyle=1;photoframe=1 |
    | Gallery - style 2    | gallery        | Page UserB    | yes        | attachments=Image3.png,Image2.png,Image1.jpg,Image1.jpg;imagesel=2;showdesc=yes;width=75;imagestyle=2 |
    | Gallery - style 3    | gallery        | Page UserB    | yes        | attachments=Image3.png,Image2.png,Image3.png,Image1.jpg,Image1.jpg;imagesel=2;showdesc=no;imagestyle=3;photoframe=0|
    | Folder               | folder         | Page UserB    | no         | dirname=myfolder;attachments=mahara_about.pdf,Image2.png,Image1.jpg,Image3.png,mahara.mp3 |
    | Some HTML            | html           | Page UserB    | yes        | attachment=test_html.html |
    | Profile Information  | profileinfo    | Page UserB    | no         | introtext =Mahara unicorn here! Nice to meet you :);profileicon=Image3.png |
    | Résumé               | entireresume   | Page UserB    | no         | tags=mahara |

    | Blog/Journal         | blog           | Page One      | no         | copytype=nocopy;count=5;journaltitle=journal1 |
    | Blogpost/JournalEntry| blogpost       | Page One      | no         | copytype=nocopy;journaltitle=journal1;entrytitle=Entry Two |
    | Comments             | comment        | Page One      | no         | |
    | Peer Assessment      | peerassessment | Page One      | auto       | |
    | Creative Commons     | creativecommons| Page One      | no         | commercialuse=yes;license=3.0;allowmods=no |

    | Navigation           | navigation     | Page One      | no         | collection=collection one;copytoall=yes |
    | Plans                | plans          | Page One      | no         | plans=Plan One,Plan Two;tasksdisplaycount=10 |
    | Internal Media: Video| internalmedia  | Page One      | no         | attachment=testvid3.mp4 |
    | Internal Media: Audio| internalmedia  | Page One      | no         | attachment=mahara.mp3 |
    | Pdf                  | pdf            | Page Two      | no         | attachment=mahara_about.pdf |
    | Recent Forum Posts   |recentforumposts| Page Two      | no         | groupname=Group1;maxposts=3 |
    | External Video       | externalvideo  | Page Two      | no         | source=https://youtu.be/yRxFm70nOrY |
    | Note/Textbox 1       | textbox        | Page Two      | no         | notetitle=secretnote;text=ma ha ha ha ra!;tags=mahara,araham;attachments=Image3.png,Image2.png,Image1.jpg;allowcomments=yes |
    | Note/textbox ref:1   | textbox        | Page Two      | no         | existingnote=secretnote |
    | Note/Textbox copy:1  | textbox        | Page Two      | no         | existingnote=secretnote;allowcomments=yes;copynote=true;notetitle=newsecretnote |
    | Profile Information  | profileinfo    | Page Two      | no         | introtext =Mahara unicorn here! Nice to meet you :);profileicon=Image1.jpg |
    | Profile Information  | profileinfo    | Page Two      | no         | introtext =Mahara unicorn here! Nice to meet you :);profileicon=Image2.png |
    | Résumé               | entireresume   | Page Two      | no         | tags=mahara |
    | Résumé field: Employment History | resumefield    | Page Two      | no         | artefacttype=employmenthistory |
    | Résumé field: Personal Goal      | resumefield    | Page Two      | no         | artefacttype=personalgoal |
    | Résumé field: Work Skill         | resumefield    | Page Two      | no         | artefacttype=workskill |
    | Résumé field: Interest           | resumefield    | Page Two      | no         | artefacttype=interest |


Scenario: Login as admin to change upload settings
    # To allow users to upload specific internal media types
    Given I log in as "admin" with password "Kupuh1pa!"
    And I go to "/admin/extensions/pluginconfig.php?plugintype=blocktype&pluginname=file/internalmedia"
    And I set the following fields to these values:
    | 3GPP media file       | 1 |
    | AVI video file        | 1 |
    | FLV flash movie       | 1 |
    | MP3 audio file        | 1 |
    | MP4 media file        | 1 |
    | MPEG movie            | 1 |
    | OGA audio file        | 1 |
    | OGG Vorbis audio file | 1 |
    | OGV video file        | 1 |
    | QuickTime movie       | 1 |
    | WEBM video file       | 1 |
    | WMV video file        | 1 |
    And I press "Save"
    And I log out

    Then I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA"
    And I go to portfolio page "Page Grp1"
    And I go to portfolio page "Page One"
    And I go to portfolio page "Page Two"
    And I log out

    Then I log in as "UserB" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserB"
