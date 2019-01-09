@javascript @core @blocktype
Feature: Creating a page with blocks
    As a user
    I want to add a page with blocks as a background step
    As a group admin
    I want to add a page with blocks as a background step

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela  | User | mahara | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org | Bob     | Boi  | mahara | internal | member |

    And the following "groups" exist:
    | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
    | Group1 | UserB | Group1 owned by UserB | standard | ON | OFF | all | ON | OFF | UserA |  |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_00 | Page 01 | user  | UserA |
    | Page UserB_00 | Page 01 | user  | UserA |
    | Page Grp1     | Page 01 | group | Group1 |

    And the following "blocks" exist:
    | title       | type         | page          |retractable | data |
    | My text 1   | text         | Page UserA_00 | yes        | This is some text |
    | image jpg   | image        | Page UserA_00 | no         | attachment=Image1.jpg; width=100 |
    | image png   | image        | Page UserA_00 | no         | attachment=Image2.png |
    | My files 1  | filedownload | Page UserA_00 | auto       | attachments=mahara_about.pdf |
    | My files 2  | filedownload | Page UserA_00 | no         | attachments=mahara_about.pdf,Image2.png |
    | Rss news    | externalfeed | Page UserA_00 | No         | source=http://rss.nzherald.co.nz/rss/xml/nzhtsrsscid_000000698.xml |
    | Rss food    | externalfeed | Page UserA_00 | no         | source=http://www.thekitchenmaid.com/feed |

    | G image 3   | image        | Page Grp1     | no         | attachment=Image3.png |
    | G files 2   | filedownload | Page Grp1     | no         | attachments=mahara_about.pdf,Image2.png |
    | nzslang     | externalvideo| Page Grp1     | no         | source=https://youtu.be/yRxFm70nOrY |

    | my social   | socialprofile| Page UserB_00 | no         | sns=instagram,twitter,facebook,tumblr,pinterest |
    | gall style1 | gallery      | Page UserB_00 | no         | attachments=Image1.jpg,Image3.png,Image3.png,Image2.png;imagesel=2;showdesc=yes;width=75;imagestyle=1;photoframe=1 |
    | gall style2 | gallery      | Page UserB_00 | yes        | attachments=Image3.png,Image2.png,Image1.jpg;imagesel=2;showdesc=yes;width=75;imagestyle=2 |
    | gall style3 | gallery      | Page UserB_00 | yes        | attachments=Image3.png,Image2.png,Image1.jpg;imagesel=2;showdesc=no;imagestyle=3;photoframe=0|
    | myfolder    | folder       | Page UserB_00 | no         | dirname=myfolder;attachments=mahara_about.pdf,Image2.png,Image1.jpg,Image3.png |
    | my html     | html         | Page UserB_00 | yes        | attachment=test_html.html |


Scenario: Create Page UserA_00 with text blocks
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_00"
    And I go to portfolio page "Page Grp1"
    And I go to portfolio page "Page UserB_00"
