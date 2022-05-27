@javascript @core @core_artefact @core_content @artefact_comment
Feature: Threaded comments
   In order to allow private conversations between an instructor and student on a student's page
   As a teacher I need to have a private thread on the student's page
   So I can post things only they can see, and they can post private replies to it

Background:
    Given the following site settings are set:
     | field            | value |
     | allowpublicviews | 1     |

    Given the following "institutions" exist:
     | name | displayname | commentthreaded | allowinstitutionpublicviews |
     | instone | Institution One | 1 | 1 |

    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | AdminA | Kupuh1pa! | AdminA@example.org | Page | Owner | instone | internal | admin |
     | AdminB | Kupuh1pa! | AdminB@example.org | Page | Commenter | mahara | internal | admin |
     | AdminC | Kupuh1pa! | AdminC@example.org | Page | Follower | mahara | internal | admin |

    Given the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page AdminA_01 | Page 01 | user | AdminA |

    Given the following "permissions" exist:
     | title | accesstype | accessname | allowcomments | approvecomments |
     | Page AdminA_01 | public | public | 1 | 0 |

Scenario: Public comment by page owner, public reply by third party
    Given I log in as "AdminA" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    And I click on "Add comment"
    And I fill in "Public comment by AdminA" in editor "Comment"
    And I enable the switch "Make comment public"
    And I click on "Comment" in the "Comment button" "Comment" property
    And I log out
    And I log in as "AdminB" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    And I click on "Comments"
    And I click on "Reply" in "Public comment by AdminA" row
    # I should see a preview of the reply-to comment below the feedback form
    And I should see "Public comment by AdminA" in the "Comment preview" "Comment" property
    And I fill in "Public reply by AdminB" in editor "Comment"
    When I click on "Comment" in the "Comment button" "Comment" property
    Then I should see "Public comment by AdminA"
    And I should see "Public reply by AdminB"

Scenario: Public comment by non-owner, owner can private reply, another non-owner cannot private reply
    Given I log in as "AdminB" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    And I click on "Add comment"
    And I fill in "Public comment by AdminB" in editor "Comment"
    And I enable the switch "Make comment public"
    And I click on "Comment" in the "Comment button" "Comment" property
    And I log out
    And I log in as "AdminA" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    And I click on "Comments"
    And I click on "Reply" in "Public comment by AdminB" row
    And I disable the switch "Make comment public"
    And I fill in "Private reply by AdminA" in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property
    And I log out
    And I log in as "AdminC" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    And I click on "Comments"
    And I click on "Reply" in "Public comment by AdminB" row
    # I should not be able to make a private reply to a comment by someone other than the page owner
    And I should see "Public" in the "Make comment public status" "Comment" property
    When I fill in "Public reply by AdminC" in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property
    Then I should see "Public comment by AdminB"
    And I should not see "Private reply by AdminA"
    And I should see "Public reply by AdminC"

Scenario: Private comment by commenter, private reply by page owner, private counter-reply by page commenter
    Given I log in as "AdminB" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    And I click on "Add comment"
    And I fill in "Private comment by AdminB" in editor "Comment"
    And I disable the switch "Make comment public"
    And I click on "Comment" in the "Comment button" "Comment" property
    And I click on "More options"
    And I click on "Remove page from watchlist"
    And I log out
    And I log in as "AdminA" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    And I click on "Comments"
    And I click on "Reply" in "Private comment by AdminB" row
    # There should be no option to make a public reply to a private comment
    And I should see "Private" in the "Make comment public status" "Comment" property
    And I fill in "Private reply by AdminA" in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property
    And I log out
    And I log in as "AdminB" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    And I click on "Comments"
    # I should be able to see the AdminA's private reply to my private comment
    # (An exception to the general rule that only the AdminA can see private comments)
    And I should see "Private reply by AdminA"
    And I click on "Reply" in "Private reply by AdminA" row
    And I fill in "Private counter-reply by AdminB" in editor "Comment"
    When I click on "Comment" in the "Comment button" "Comment" property
    Then I should see "Private comment by AdminB"
    And I should see "Private reply by AdminA"
    And I should see "Private counter-reply by AdminB"
    # AdminB should receive a notification about AdminA's reply even though they unwatched the page
    And I choose inbox
    And I click on "New comment on Page AdminA_01"
    And I should see "Private reply by AdminA"

Scenario: No private replies to anonymous comments
    Given I go to portfolio page "Page AdminA_01"
    And I click on "Add comment"
    And I fill in "Name" with "Anonymous User"
    # No WYSIWYG editor for anonymous users
    And I fill in "Comment" with "Public comment by anonymous user"
    And I enable the switch "Make comment public"
    And I click on "Comment" in the "Comment button" "Comment" property
    # Public comments are now always moderated
    When I log in as "AdminA" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    And I click on "Comments"
    And I click on "Make comment public"
    And I log out
    When I log in as "AdminB" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    And I click on "Comments"
    And I click on "Reply" in "Public comment by anonymous user" row
    # I should not be able to make a private reply to a comment by someone other than the page owner
    Then I should see "Public" in the "Make comment public status" "Comment" property
    And I fill in "Public reply by AdminB" in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property
    And I should see "Public comment by anonymous user"
    And I should see "Public reply by AdminB"

Scenario: No replies to deleted comments
    Given I log in as "AdminA" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    And I click on "Add comment"
    And I fill in "I will delete this comment" in editor "Comment"
    And I enable the switch "Make comment public"
    When I click on "Comment" in the "Comment button" "Comment" property
    And I should see "I will delete this comment"
    And I delete the "I will delete this comment" row
    # No reply button, because I have deleted the comment
    Then I should not see "Reply"

Scenario: Deleted comments
    Given I log in as "AdminA" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    And I click on "Add comment"
    # Create a tree of comments like so:
    #
    # * Comment #1
    # ** Comment #1/1
    # *** Comment #1/2
    # * Comment #2
    #
    And I fill in "Comment 1." in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property
    And I should see "Comment 1."
    And I fill in "Comment 2." in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property
    And I should see "Comment 2."
    And I click on "Reply" in "Comment 1." row
    And I fill in "Comment 1-1." in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property
    And I should see "Comment 1-1."
    # TODO: fix "I click on" so it automatically scrolls if needed
    And I scroll to the base of id "commentreplyto20"
    And I click on "Reply" in "Comment 1-1." row
    And I fill in "Comment 1-2." in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property
    And I should see "Comment 1-2."

    # Deleting a threaded comment that has a reply, display
    # a placeholder for the reply's context
    #
    # * Comment #1
    # ** (Deleted placeholder)
    # *** Comment #1/2
    # * Comment #2
    #
    # TODO: Fix "I delete the row" so it automatically scrolls if needed
    When I scroll to the base of id "delete_comment20_delete_comment_submit"
    And I delete the "Comment 1-1." row
    Then I should not see "Comment 1-1."
    And I should see "Comment removed by the author"

    # Deleting a comment with no replies, hide the deleted
    # comment. (Recursively also hide any deleted
    # parents that now have no visible replies.)
    #
    # * Comment #1
    # * Comment #2
    #
    # TODO: Fix "I delete the row" so it automatically scrolls if needed
    When I scroll to the base of id "delete_comment21_delete_comment_submit"
    And I delete the "Comment 1-2." row
    Then I should not see "Comment 1-2."
    And I should not see "Comment removed by the author"
