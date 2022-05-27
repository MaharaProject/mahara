@javascript @core @core_artefact @core_content @artefact_comment
Feature: Moderating group comments

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
     | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | mahara | internal | member |

    And the following "groups" exist:
    | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
    | GroupA | UserA | GroupA owned by UserA | standard | ON | ON | all | ON | ON | UserB |  |

    And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page GroupA_01 | Page 01 | group | GroupA |

    And the following "permissions" exist:
     | title | accesstype | accessname | allowcomments | approvecomments |
     | Page GroupA_01 | loggedin | loggedin | 1 | 1 |

Scenario: Moderating a group comment when approve comments is turned on
    # Adding a comment to a group page as a non-group member
    Given I log in as "UserC" with password "Kupuh1pa!"
    And I go to portfolio page "Page GroupA_01"
    And I click on "Add comment"
    And I set the field "Comment" to "This is a comment from UserC"
    And I enable the switch "Make comment public"
    And I click on "Comment" in the "Comment button" "Comment" property
    Then I should see "Comment submitted, awaiting moderation"
    # Check that multibyte comments render correctly
    And I set the field "Comment" to "これはUserCからのコメントです"
    And I enable the switch "Make comment public"
    And I click on "Comment" in the "Comment button" "Comment" property
    And I log out

    # Checking that normal group member is not able to moderate comment
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I go to portfolio page "Page GroupA_01"
    Then I should not see "This is a comment from UserC"
    And I log out

    # Moderating the comment as group admin
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page GroupA_01"
    And I click on "Comments"
    Then I should see "This is a comment from UserC"
    And I should see "これはUserCからのコメントです"
    And I click on "Make comment public" in the "This is a comment from UserC" comment
