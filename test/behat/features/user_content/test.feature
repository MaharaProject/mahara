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
    | title       | type         | page          | row | column | order |retractable | data |
    | My text 1   | text         | Page UserA_00 | 1   | 1      | 1     | yes        | text=This is sometext one |
    | image jpg   | image        | Page UserA_00 | 1   | 1      | 2     | no         | image=Image1.jpg |
    | image png   | image        | Page UserA_00 | 1   | 1      | 3     | no         | image=Image2.png |
    | My files 1  | filedownload | Page UserA_00 | 1   | 2      | 1     | auto       | attachment=mahara_about.pdf |
    | My files 2  | filedownload | Page UserA_00 | 1   | 2      | 2     | no         | attachment=mahara_about.pdf;attachment=Image2.png |
    | Rss news    | externalfeed | Page UserA_00 | 1   | 3      | 1     | no         | feed_location=http://rss.nzherald.co.nz/rss/xml/nzhtsrsscid_000000698.xml |
    | Rss food    | externalfeed | Page UserA_00 | 1   | 3      | 2     | no         | feed_location=http://www.thekitchenmaid.com/feed |
    | G image 3   | image        | Page Grp1     | 1   | 1      | 1     | no         | image=Image3.png |
    | G files 2   | filedownload | Page Grp1     | 1   | 1      | 2     | no         | attachment=mahara_about.pdf;attachment=Image2.png |
    | nzslang     | externalvideo| Page Grp1     | 1   | 1      | 3     | no         | video_location=https://youtu.be/yRxFm70nOrY |
    | my social   | socialprofile| Page UserB_00 | 1   | 1      | 1     | no         | social_profile=instagram,twitter,facebook,tumblr,pinterest |
    | gall style1 | gallery      | Page UserB_00 | 1   | 2      | 1     | no         | gallery_images=Image1.jpg,Image3.png,Image3.png,Image2.png;select=1;showdescription=1;width=75;style=0;photoframe=1 |
    | gall style2 | gallery      | Page UserB_00 | 1   | 2      | 2     | no         | gallery_images=Image3.png,Image2.png,Image1.jpg;select=1;showdescription=1;width=75;style=1 |
    | gall style3 | gallery      | Page UserB_00 | 1   | 2      | 3     | no         | gallery_images=Image3.png,Image2.png,Image1.jpg;select=1;showdescription=1;style=2;photoframe=0|
    | myfolder    | folder       | Page UserB_00 | 1   | 2      | 4     | no         | folder_name=myfolder;folder_files=mahara_about.pdf,Image2.png |

Scenario: Create Page UserA_00 with text blocks
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_00"
    And I go to portfolio page "Page Grp1"
    And I go to portfolio page "Page UserB_00"
