@javascript @core @blocktype @blocktype_retractable
Feature: Moderate forum posts
    As a moderator of a group
    I want to moderate a forum posts
    So that other group members can read the latest posts

Background:
Given the following "users" exist:
    | username | password  | email             | firstname | lastname | institution | authname | role   |
    | UserA    | Kupuh1pa! | UserA@example.org | Angela    | User     | mahara      | internal | admin |
    | UserB    | Kupuh1pa! | UserB@example.org | Bob       | User     | mahara      | internal | member |
    | UserC    | Kupuh1pa! | UserC@example.org | Cecilia   | User     | mahara      | internal | member |
    | UserD    | Kupuh1pa! | UserD@example.org | Dave      | User     | mahara      | internal | member |

    And the following "groups" exist:
    | name   | owner | description           | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members      | staff |
    | GroupA | UserA | GroupA owned by UserA | standard  | ON   | OFF           | all       | ON            | OFF           | UserB, UserC |  |

Scenario: Group moderator approved a forum post
    # Group owner sets up a forum (Group A's forum)
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I follow "Edit \"GroupA\" Settings"
    And I follow "Forums (tab)"
    And I click on "New forum"
    And I set the following fields to these values:
    | Title       | Group A's forum                        |
    | Description | Talking about things this group can do |
    And I select "Fully expand" from "Forum indent mode"
    # Group owner sets up moderation of forum and adds a moderator "UserC"
    And I disable the switch "Allow group members to unsubscribe"
    And I select "UserC" from "Potential moderators"
    And I press "rightarrow"
    And the "moderateposts" select box should contain "None"
    And the "moderateposts" select box should contain "Posts"
    And the "moderateposts" select box should contain "Replies"
    And the "moderateposts" select box should contain "Posts and replies"
    And I select "Posts and replies" from "moderateposts"
    And I press "Save"
    #Group owner sets up topic for the forum
    And I press "New topic"
    And I set the following fields to these values:
    | Subject | Taking photos of cats |
    | Message | The difficulty of cat photography is often underrated. You need a fast lens to accurately capture the speed and agility of the cat. |
    And I enable the switch "Send message now"
    And I press "Post"
    And I log out
    # Group member logs in and creates a  new topic for the Group A's forum
    When I log in as "UserB" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I follow "GroupA"
    And I follow "Forums (tab)"
    And I follow "Group A's forum"
    And I press "New topic"
    And I set the following fields to these values:
    | Subject | Bob's forum topic                            |
    | Message | Bob's topic is now open for discussion |
    And I press "Post"
    And I should see "Awaiting approval"
    # Group member makes a reply to an existing topic post
    When I follow "Forums"
    And I follow "Group A's forum"
    And I follow "Taking photos of cats"
    And I follow "Reply"
    And I set the following fields to these values:
    | Message | Bob's Two cents worth |
    And I press "Post"
    And I should see "Awaiting approval"
    # group member wants to unsubscribe from forum (Bug 805065)
    And I scroll to the top
    When I follow "Forums"
    And I click on "Unsubscribe" in "Group A's forum" row
    Then I should see "You are not allowed to unsubscribe from this forum"
    And I log out
    # Group Forum moderator should see buttons to moderate new topics and new forums
    When I log in as "UserC" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I follow "GroupA"
    And I follow "Forums (tab)"
    And I follow "Group A's forum"
    And I follow "Bob's forum topic"
    Then I should see "Approve"
    And I should see "Reject"
    When I follow "Forum"
    And I follow "Group A's forum"
    And I follow "Taking photos of cats"
    Then I should see "Approve"
    And I should see "Reject"
    Then I log out
    # Group Owner logs in and Approves Bob's forum topic and rejects Bob's Two cents worth
    When I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I follow "GroupA"
    And I follow "Forums (tab)"
    And I follow "Group A's forum"
    And I follow "Bob's forum topic"
    Then I should see "Approve"
    When I follow "Approve"
    Then I should see "Post approved"
    And I scroll to the top
    When I follow "Forums"
    And I follow "Group A's forum"
    And I follow "Taking photos of cats"
    Then I should see "Approve"
    When I follow "Reject"
    And I should see "Reason"
    When I fill in "Reason" with "I cannot allow this"
    And I press "Notify author"
    Then I should see "The post has been removed"
