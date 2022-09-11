@javascript @core @core_administration @manual
Feature: Search for portfolios with Elasticsearch
In order to index and search the site using elasticsearch7
As an admin and a user
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
    # Available fields: name*, owner*, description, grouptype, open, controlled, request, invitefriends, suggestfriends, submittableto, allowarchives,
    #                 editwindowstart, editwindowstart, editwindowend, members, staff, admins, institution, public
    | name           | owner | description                   | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
    | Fantastic Five | UserB | Fantastic Five owned by UserB | standard  | ON   | OFF           | all       | ON            | OFF           | UserA   |       |

 And the following "pages" exist:
    # Available fields: title*, description, ownertype*, ownername*, layout, tags
    | title        | description                       | ownertype | ownername      |
    | Tangaroa     | God of the sea                    | user      | UserA          |
    | Papatūānuku  | Earth mother                      | user      | UserA          |
    | Ranginui     | Sky father                        | user      | UserA          |
    | Tānemahuta   | God of forests and birds          | user      | UserA          |
    | Tāwhirimātea | God of storms and violent weather | user      | UserB          |
    | Tūmatauenga  | God of war, hunting, cooking, etc | group     | Fantastic Five |

# And the following "pagecomments" exist:
    # Available fields: user*, comment*, page*, attachment, private


 And the following "collections" exist:
    # Available fields: title*, description, ownertype*, ownername*, pages
    | title   | ownertype | ownername | description                 | pages                           |
    | Amazing | user      | UserA     | An indescribable collection | Tangaroa, Papatūānuku, Ranginui |

 And the following "blocks" exist:
    # Available fields: title*, type*, data*, page*, retractable*
    # Tangaroa (page 1, UserA, in Amazing collection)
    | title                | type           | page        |retractable | data                                                              |
    | Text                 | text           | Tangaroa    | yes        | textinput=Howdy my friends;tags=texttag                           |
    | Image JPG            | image          | Tangaroa    | no         | attachment=Image1.jpg; width=100;tags=imagetag                    |
    | Image PNG            | image          | Tangaroa    | no         | attachment=Image2.png                                             |
    | Files to download    | filedownload   | Tangaroa    | auto       | attachments=mahara_about.pdf                                      |
    | Files to download    | filedownload   | Tangaroa    | no         | attachments=mahara_about.pdf,Image2.png                           |
    | External Feed - News | externalfeed   | Tangaroa    | No         | source=https://stuff.co.nz/rss;count=5                            |
    | External Feed - Food | externalfeed   | Tangaroa    | no         | source=https://www.bonappetit.com/feed/rss                        |
    | External Feed - Tech | externalfeed   | Tangaroa    | no         | source=feeds.feedburner.com/geekzone;count=3;tags=cat,a,lyst      |
    | Social Media         | socialprofile  | Tangaroa    | no         | sns=instagram,twitter,facebook,tumblr,pinterest,mysocialmedia     |

 And the following "blocks" exist:
    # Papatūānuku (page 2, UserA, in Amazing collection)
    | title                   | type           | page          |retractable | data                                                                                                                        |
    | Image                   | image          | Papatūānuku   | no         | attachment=Image3.png                                                                                                       |
    | Files to download       | filedownload   | Papatūānuku   | no         | attachments=mahara_about.pdf,Image2.png,testvid3.mp4,mahara.mp3                                                             |
    | External Video          | externalvideo  | Papatūānuku   | no         | source=https://youtu.be/yRxFm70nOrY;tags=jen,from,the,house                                                                 |
    | Navigation              | navigation     | Papatūānuku   | no         | collection=collection one;copytoall=yes                                                                                     |
    | Social Media            | socialprofile  | Papatūānuku   | no         | sns=instagram,twitter,facebook,tumblr,pinterest,mysocialmedia                                                               |
    | Pdf                     | pdf            | Papatūānuku   | no         | attachment=mahara_about.pdf                                                                                                 |
    | Recent Forum Posts      |recentforumposts| Papatūānuku   | no         | groupname=Fantastic Five;maxposts=3                                                                                         |
    | External Video          | externalvideo  | Papatūānuku   | no         | source=https://youtu.be/k5t5PD5F8Wo                                                                                         |
    | Note/Textbox 1          | textbox        | Papatūānuku   | no         | notetitle=secretnote;text=ma ha ha ha ra!;tags=mahara,araham;attachments=Image3.png,Image2.png,Image1.jpg;allowcomments=yes |
    | Note/textbox ref:1      | textbox        | Papatūānuku   | no         | existingnote=secretnote                                                                                                     |
    | Note/Textbox copy:1     | textbox        | Papatūānuku   | no         | existingnote=secretnote;allowcomments=yes;copynote=true;notetitle=newsecretnote                                             |
    | Profile Information     | profileinfo    | Papatūānuku   | no         | introtext =Mahara unicorn here! Nice to meet you :);profileicon=Image1.jpg                                                  |
    | Profile Information     | profileinfo    | Papatūānuku   | no         | introtext =Mahara unicorn here! Nice to meet you :);profileicon=Image2.png                                                  |

 And the following "blocks" exist:
    # Ranginui (page 3, UserA, in Amazing collection)
    | title                  | type            | page         | retractable | data                                       |
    | Comments               | comment         | Ranginui     | no          |                                            |
    | Peer Assessment        | peerassessment  | Ranginui     | auto        |                                            |
    | Creative Commons       | creativecommons | Ranginui     | no          | commercialuse=yes;license=3.0;allowmods=no |
    | Navigation             | navigation      | Ranginui     | no          | collection=Amazing;copytoall=yes           |
    | whacky                 | internalmedia   | Ranginui     | no          | attachment=testvid3.mp4                    |
    | Internal Media: Audio  | internalmedia   | Ranginui     | no          | attachment=mahara.mp3                      |

 And the following "blocks" exist:
    # Tānemahuta (page 4, UserA, not in collection)
    | title             | type | page       | retractable | data                              |
    | Sneaky text block | text | Tānemahuta | no          | textinput=I'm not in a collection |

    And the following "blocks" exist:
    # Tāwhirimātea (page 1, UserB)
    | title                | type           | page            | retractable | data                                                                                                   |
    | Gallery - style 1    | gallery        | Tāwhirimātea    | no          | attachments=Image1.jpg,Image3.png,Image3.png,Image2.png,Image1.jpg;imagesel=2;showdesc=yes             |
    | Gallery - style 2    | gallery        | Tāwhirimātea    | yes         | attachments=Image3.png,Image2.png,Image1.jpg,Image1.jpg;imagesel=2;showdesc=yes;imagestyle=2           |
    | Gallery - style 3    | gallery        | Tāwhirimātea    | yes         | attachments=Image3.png,Image2.png,Image3.png,Image1.jpg,Image1.jpg;imagesel=2;showdesc=no;imagestyle=3 |
    | Folder               | folder         | Tāwhirimātea    | no          | dirname=myfolder;attachments=mahara_about.pdf,Image2.png,Image1.jpg,Image3.png,mahara.mp3              |
    | Some HTML            | html           | Tāwhirimātea    | yes         | attachment=test_html.html                                                                              |
    | Profile Information  | profileinfo    | Tāwhirimātea    | no          | introtext =Mahara unicorn here! Nice to meet you :);profileicon=Image3.png                             |

 And the following "blocks" exist:
   # Tūmatauenga (group page owned by 'Fantastic Five' group)
    | title                   | type           | page             | retractable | data |
    | GoogleApps: Google Maps | googleapps     | Tūmatauenga      | no          | googleapp=<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2997.861064367898!2d174.77176941597108!3d-41.29012814856559!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x6d38afd6326bfda5%3A0x5c0d858838e52d7a!2sCatalyst!5e0!3m2!1sen!2snz!4v1550707486290" width="800" height="600" frameborder="0" style="border:0" allowfullscreen></iframe>;height=200;tags=cat,dog,monkeys |
    | GoogleApps: Google Cal. | googleapps     | Tūmatauenga      | no          | https://calendar.google.com/calendar/embed?src=en.new_zealand%23holiday%40group.v.calendar.google.com&ctz=Pacific%2FAuckland |

Scenario: Testing search functions for portfolios
   Given I log in as "admin" with password "Kupuh1pa!"
   And I go to the "search" plugin "elasticsearch7" configuration "elasticsearch7" type
   And I click on "Select all"
   And I click on "Save"
   And I click on "Reset"
   And I should see "Settings saved"
   And I log out

 # Search by page title
   When I log in as "UserA" with password "Kupuh1pa!"
   And I set the following fields to these values:
   | Search | tangaroa |
   And I click on "Go"
   Then I should see "God of the sea"

 # Search by description
   When I set the following fields to these values:
   | Search | earth |
   And I click on "Go"
   Then I should see "Papatūānuku"

 # Search by collection title
  When I set the following fields to these values:
   | Search | indescribable |
   And I click on "Go"
   Then I should see "Amazing"

 # Search by collection description
   When I set the following fields to these values:
   | Search | indescribable |
   And I click on "Go"
   Then I should see "Amazing"

 # Search by text block text
   When I set the following fields to these values:
   | Search | howdy |
   And I click on "Go"
   Then I should see "Tangaroa"

 # TODO: Search by block title within collection (Currently ES7 does not fetch collections from block titles, only pages)
  # When I set the following fields to these values:
  # | Search | whacky |
  # And I click on "Go"
  # Then I should see "Amazing"

 # Search by block title not in collection
   When I set the following fields to these values:
   | Search | sneaky |
   And I click on "Go"
   Then I should see "Sneaky text block"
   And I log out
