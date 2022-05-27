@javascript @core @blocktype @blocktype_retractable
Feature: Add recent forum posts block to a group page
    As a member of a group
    I want to add a recent forum posts block to my page
    So that other group members can read the latest posts

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
    | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User |mahara | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserC_01 | Page 01| user | UserC |

    And the following "groups" exist:
    | name   | owner | description           | grouptype| open | invitefriends | editroles | submittableto | allowarchives | members | staff | attachments |
    | GroupA | UserA | GroupA owned by UserA | standard | ON   | OFF           | all       | ON            | OFF           | UserB, UserC |  |Image3.png |

    And the following "forums" exist:
    | group  | title           | description                            | creator | config |
    | GroupA | Group A's forum | Talking about things this group can do | UserA   | indentmode=full_indent, autosubscribe=1 |

    And the following "forumposts" exist:
    | group  | forum           | user  | topic                | subject | message                                                 | attachments |
    | GroupA | Group A's forum | UserA | Taking photos of cats|         | The difficulty of cat photography is often underrated.  | Image2.png, mahara_about.pdf, Image3.png |
    | GroupA | Group A's forum | UserB | Taking photos of cats|         | I don't know Angela, I just use my phone to photograph my cat, and I've got some pretty good ones! ||
    | GroupA | Group A's forum | UserA |Taking photos of cats |         |The difficulty of cat photography is often underrated. You need a fast lens to accurately capture the speed and agility of the cat.||
    | GroupA | Group A's forum | UserA |Taking photos of Dogs |         |The difficulty of Dog photography is often underrated. You need a fast lens to accurately capture the speed and agility of the Dog.|| 
    | GroupA | Group A's forum | UserA |Taking photos of Cows |         |The difficulty of Cows photography is often underrated. You need a fast lens to accurately capture the speed and agility of the Cows.||
    And the following "blocks" exist:
    | title              | type            | page          |retractable | data |
    | Recent Forum Posts | recentforumposts| Page UserC_01 | no         | groupname=GroupA; |

Scenario: Create forum and add block to group page
    Given I log in as "UserC" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserC_01" card menu
    # TODO could test other options
    And I display the page
    # Allow the ajax block to load
    And I wait "1" seconds
    Then I should see "cat photography is often underrated"
    And I should see "I just use my phone"
    And I expand the section "Attached files"
    And I wait "1" seconds
    And I should see "Image2.png"
    And I should see "mahara_about.pdf"
    And I should see "Image3.png"

Scenario: Administrative forum bulk actions
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I click on "Edit \"GroupA\""
    And I click on "Forums" in the "Navigation" "Groups" property
    And I click on "Group A's forum"
    # Perform bulk actions of
    And I check "Taking photos of Cows"
    And I check "Taking photos of Dogs"
    And I check "Taking photos of cats"
    And I select "Close" from "action"
    And I click on "Update selected topics"
    And I check "Taking photos of Cows"
    And I check "Taking photos of Dogs"
    And I check "Taking photos of cats"
    And I select "Sticky" from "action"
    When I click on "Update selected topics"
    And I click on "Taking photos of Cows"
    And I click on "Edit topic"
    And the "Closed" checkbox should be checked
    And the "Sticky" checkbox should be checked
