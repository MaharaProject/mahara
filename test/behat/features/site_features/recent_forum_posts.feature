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
    | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
    | GroupA | UserA | GroupA owned by UserA | standard | ON | OFF | all | ON | OFF | UserB, UserC |  |

Scenario: Create forum and add block to group page
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "My groups" in "Engage" from main menu
    And I follow "Edit \"GroupA\" Settings"
    And I follow "Forums (tab)"
    And I follow "New forum"
    And I set the following fields to these values:
    | Title | Group A's forum |
    | Description | Talking about things this group can do |
    And I select "Fully expand" from "Forum indent mode"
    And I enable the switch "Automatically subscribe users"
    And I press "Save"
    And I follow "New topic"
    And I set the following fields to these values:
    | Subject | Taking photos of cats |
    | Message | The difficulty of cat photography is often underrated. You need a fast lens to accurately capture the speed and agility of the cat. |
    And I enable the switch "Send message now"
    And I press "Post"
    And I log out
    And I log in as "UserB" with password "Kupuh1pa!"
    And I wait "1" seconds
    And I scroll to the id 'inboxblock'
    And I follow "Taking photos of cats"
    And I follow "Topic"
    And I follow "Reply"
    And I set the field "Message" to "I don't know Angela, I just use my phone to photograph my cat, and I've got some pretty good ones!"
    And I press "Post"
    And I log out
    And I log in as "UserC" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserC_01" panel menu
    And I expand "General" node
    And I follow "Recent forum posts"
    And I press "Add"
    # TODO could test other options
    And I press "Save"
    And I display the page
    Then I should see "cat photography is often underrated"
    And I should see "I just use my phone"
