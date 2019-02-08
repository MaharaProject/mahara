@javascript @core @blocktype
Feature: Creating a page with blocks
    As a user
    I want to add a page with blocks as a background step
    As a group admin
    I want to add a page with blocks as a background step

Background:
    Given the following "users" exist:
    | username | password | email             | firstname | lastname | institution | authname | role |
    | UserA    | Kupuh1pa!| UserA@example.org | Angela    | User     | mahara      | internal | member |
    | UserB    | Kupuh1pa!| UserB@example.org | Bob       | Boi      | mahara      | internal | member |

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
    | Page Three    | test 01     | user      | UserA     |
    | Page Four     | test 01     | user      | UserA     |

    And the following "collections" exist:
    | title          | ownertype | ownername | description | pages             |
    | collection one | user      | UserA     | desc of col |Page One,Page Two  |


    And the following "journals" exist:
    | owner | ownertype | title   | description      | tags               |
    | UserA | user      | journal1| this is journal1 | amber,brown,cobalt |
    | Group1| group      |journal2| this is journal1 | amber,brown,cobalt |

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
    | Text                 | text           | Page UserA    | yes        | This is some text |
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

    | Blog/Journal         | blog           | Page One      | no         | copytype=nocopy;count=5;journaltitle=journal1 |
    | Blogpost/JournalEntry| blogpost       | Page One      | no         | copytype=nocopy;journaltitle=journal1;entrytitle=Entry Two |
    | Comments             | comment        | Page One      | no         | no configdata |
    | Peer Assessment      | peerassessment | Page One      | auto       | no configdata |
    | Creative Commons     | creativecommons| Page One      | no         | commercialuse=yes;license=3.0;allowmods=no |

    | Navigation           | navigation     | Page Two      | no         | collection=collection one;copytoall=yes |
    | Plans                | plans          | Page Two      | no         | plans=Plan One,Plan Two;tasksdisplaycount=10 |

    | Internal Media: Video| internalmedia  | Page Three    | no         | attachment=testvid3.mp4 |
    | Internal Media: Audio| internalmedia  | Page Three    | no         | attachment=mahara.mp3 |
    | Pdf                  | pdf            | Page Three    | no         | attachment=mahara_about.pdf |
    | Recent Forum Posts   |recentforumposts| Page Three    | no         | groupname=Group1;maxposts=3 |
    | External Video       | externalvideo  | Page Three    | no         | source=https://youtu.be/yRxFm70nOrY |

    | Note/Textbox 1       | textbox        | Page Three    | no         | notetitle=secretnote;text=ma ha ha ha ra!;tags=mahara,araham;attachments=Image3.png,Image2.png,Image1.jpg;allowcomments=yes |
    | Note/textbox ref:1   | textbox        | Page Three    | no         | existingnote=secretnote |
    | Note/Textbox copy:1  | textbox        | Page Three    | no         | existingnote=secretnote;allowcomments=yes;copynote=true;notetitle=newsecretnote |
    | Profile Information  | profileinfo    | Page Two      | no         | introtext =Mahara unicorn here! Nice to meet you :);profileicon=Image1.jpg |
    | Profile Information  | profileinfo    | Page Two      | no         | introtext =Mahara unicorn here! Nice to meet you :);profileicon=Image2.png |


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
    And I go to portfolio page "Page Three"
    And I log out
    And I log in as "UserB" with password "Kupuh1pa!"
    Then I go to portfolio page "Page UserB"
