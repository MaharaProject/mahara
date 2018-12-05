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
    | Page Grp1     | Page 01 | group | Group1 |

    And the following "blocks" exist:
    | title      | type         | page          | row | column | order | data |
    | My text 1  | text         | Page UserA_00 | 1   | 1      | 1     | text=This is sometext one |
    | My image 1 | image        | Page UserA_00 | 1   | 1      | 2     | image=Image2.png |
    | My files 1 | filedownload | Page UserA_00 | 1   | 2      | 1     | attachment=mahara_about.pdf |
    | My files 2 | filedownload | Page UserA_00 | 1   | 1      | 3     | attachment=mahara_about.pdf;attachment=Image2.png |
    | G image 1  | image        | Page Grp1     | 1   | 1      | 2     | image=Image2.png |
    | G files 2  | filedownload | Page Grp1     | 1   | 1      | 3     | attachment=mahara_about.pdf;attachment=Image2.png |

Scenario: Create Page UserA_00 with text blocks
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_00"
    And I go to portfolio page "Page Grp1"
