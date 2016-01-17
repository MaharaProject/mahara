@javascript @core @core_artefact @core_content @artefact_comment
Feature: Moderating group comments

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | User | Eh | mahara | internal | member |
     | userB | Kupuhipa1 | test02@example.com | User | Bee | mahara | internal | member |
     | userC | Kupuhipa1 | test03@example.com | User | Sea | mahara | internal | member |

    And the following "groups" exist:
    | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
    | GroupA | userA | This is group A | standard | ON | ON | all | ON | ON | userB |  |

    And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page1 | Page one | group | GroupA |

    And the following "permissions" exist:
     | title | accesstype | accessname | allowcomments | approvecomments |
     | Page1 | loggedin | loggedin | 1 | 1 |

Scenario: Moderating a group comment when approve comments is turned on
    # Adding a comment to a group page as a non-group member
    Given I log in as "userC" with password "Kupuhipa1"
    And I go to portfolio page "Page1"
    And I set the field "Message" to "This is a comment from userC"
    And I enable the switch "Make public"
    And I press "Comment"
    Then I should see "You have requested that this comment be made public."
    And I log out

    # Checking that normal group member is not able to moderate comment
    Given I log in as "userB" with password "Kupuhipa1"
    And I go to portfolio page "Page1"
    Then I should not see "This is a comment from userC"
    And I log out

    # Moderating the comment as group admin
    Given I log in as "userA" with password "Kupuhipa1"
    And I go to portfolio page "Page1"
    Then I should see "This is a comment from userC"
    And I press "Make public"