@javascript @core @core_artefact @core_content @artefact_comment
Feature: Threaded comments
   In order to see earliest/latest threaded comments to a page
   As a mahara user I should see threaded comments in the right order
   So I can easily follow these comments

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |
    | pcnz | Institution One | ON | OFF |

    And the following "institutions" exist:
     | name | displayname | commentthreaded | allowinstitutionpublicviews |
     | instone | Institution One | 1 | 1 |

    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | AdminA | Kupuh1pa! | AdminA@example.org | Angela | Admin | instone | internal | admin |

    Given the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page AdminA_01 | page1 | user | AdminA |

Scenario: Threaded comments should be displayed in correct order
    Given I log in as "AdminA" with password "Kupuh1pa!"
    And I go to portfolio page "Page AdminA_01"
    # Add 11 comments
    And I press "Add comment"
    And I fill in "Comment #1" in editor "Comment"
    And I press "Comment"
    And I fill in "Comment #2" in editor "Comment"
    And I press "Comment"
    And I fill in "Comment #3" in editor "Comment"
    And I press "Comment"
    And I fill in "Comment #4" in editor "Comment"
    And I press "Comment"
    And I fill in "Comment #5" in editor "Comment"
    And I press "Comment"
    And I fill in "Comment #6" in editor "Comment"
    And I press "Comment"
    And I fill in "Comment #7" in editor "Comment"
    And I press "Comment"
    And I fill in "Comment #8" in editor "Comment"
    And I press "Comment"
    And I fill in "Comment #9" in editor "Comment"
    And I press "Comment"
    And I fill in "Comment #10" in editor "Comment"
    And I press "Comment"
    And I fill in "Comment #11" in editor "Comment"
    And I press "Comment"
    And I fill in "Comment #12" in editor "Comment"
    And I press "Comment"
    And I go to portfolio page "Page AdminA_01"

    # Go to the first page
    And I press "Comments"
    And I should see "Comment #1"
    And I should see "Comment #10"
    And "Comment #2" "text" should appear before "Comment #3" "text"
    And I should not see "Comment #11"
    # Go to the second page
    And I scroll to the base of id "feedback_pagination"
    And I follow "2"
    And I should see "Comment #11"
    And "Comment #11" "text" should appear before "Comment #12" "text"
    And I should not see "Comment #10"

    # Reply to a comment
    And I scroll to the base of id "feedback_pagination"
    And I follow "1"
    And I click on "Reply" in "Comment #1" row
    And I fill in "Comment #1/1" in editor "Comment"
    And I press "Comment"
    And "Comment #1/1" "text" should appear before "Comment #2" "text"
    And I scroll to the id "feedbacktable"
    And I click on "Reply" in "Comment #1/1" row
    And I fill in "Comment #1/1/1" in editor "Comment"
    And I press "Comment"
    And I scroll to the id "feedbacktable"
    And I click on "Reply" in "Comment #1/1" row
    And I fill in "Comment #1/1/2" in editor "Comment"
    And I press "Comment"
    And I scroll to the id "feedbacktable"
    And I click on "Reply" in "Comment #1/1/1" row
    And I fill in "Comment #1/1/1/1" in editor "Comment"
    And I press "Comment"
    And I scroll to the id "feedbacktable"
    And I click on "Reply" in "Comment #1/1/1/1" row
    And I fill in "Comment #1/1/1/1/1" in editor "Comment"
    And I press "Comment"
    And I scroll to the id "feedbacktable"
    And I click on "Reply" in "Comment #1/1/1/1" row
    And I fill in "Comment #1/1/1/1/2" in editor "Comment"
    And I press "Comment"
    And I scroll to the id "feedbacktable"
    And I click on "Reply" in "Comment #1/1" row
    And I fill in "Comment #1/1/3" in editor "Comment"
    And I press "Comment"
    And I scroll to the base of id "feedbacktable"
    And I click on "Reply" in "Comment #1/1/2" row
    And I fill in "Comment #1/1/2/1" in editor "Comment"
    And I press "Comment"
    And I go to portfolio page "Page AdminA_01"
    And "Comment #1/1/1/1/1" "text" should appear before "Comment #1/1/1/1/2" "text"
    And "Comment #1/1/1/1/2" "text" should appear before "Comment #1/1/2" "text"
    And "Comment #1/1/2/1" "text" should appear before "Comment #1/1/3" "text"
