@javascript @core @core_artefact @core_group
Feature: Commenting on a group page
    In order to be able to verify I commented publicly on a group page
    As a user
    So leave a comment and it appears in the right place

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |

Given the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | GroupA | UserA | Group A owned by UserA | standard | ON | ON | all | OFF | OFF | UserB |   |

Given the following "pages" exist:
      | title | description | ownertype | ownername |
      | Page GroupA_01 | Page 01 | group | GroupA |

Scenario: As a user leaving a public comment on a group page (Bug 1509129)
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    # Changing the settings of the block to change comment notification
    And I click on "Settings" in "Group A" row
    And I set the following fields to these values:
    | Comment notifications | None |
    And I press "Save group"
    When I click on "Pages and collections" in the "Arrow-bar nav" "Nav" property
    And I follow "Page GroupA_01"
    And I press "Add comment"
    And I fill in "Adding a comment to this field. Student = Awesome!" in editor "Comment"
    # Checking that the make public is on
    And I enable the switch "Make comment public"
    And I press "Comment"
    # Verifying that it saves
    Then I should see "Comment submitted"
    And I log out
    And I log in as "UserB" with password "Kupuh1pa!"
    # Needs to navigate to see the comment and check it can be seen publicly
    When I follow "GroupA"
    When I follow "Pages and collections (tab)"
    And I click the card "Page GroupA_01"
    And I press "Comments"
    Then I should see "Adding a comment to this field. Student = Awesome!" in the "Comment text" "Comment" property

# As part of consolidating behat tests, this scenario has been added.
# Original feature title: Sending notification message when someone leaves a comment in a group page
Scenario: Adding a comment on a group page (Bug 1426983) and verifying the notification message.
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I follow "GroupA"
    And I follow "Pages and collections (tab)"
    # And I click on "Pages"
    And I follow "Page GroupA_01"
    And I press "Add comment"
    And I fill in "Testing comment notifications" in editor "Comment"
    And I press "Comment"
    # Log out as user 1
    And I log out
    # Log in as  admin
    When I log in as "UserB" with password "Kupuh1pa!"
    # Checking notification display on the dashboard
    And I wait "1" seconds
    Then I should see "New comment on Page GroupA_01"
    # Checking notifications also appear in my inbox
    And I choose inbox
    And I follow "New comment on Page GroupA_01"
    And I should see "Testing comment notifications"
