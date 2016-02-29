@javascript @core @core_artefact @core_content @artefact_comment
Feature: Threaded comments
   In order to see earliest/latest threaded comments to a page
   As a mahara user I should see threaded comments in the right order
   So I can easily follow these comments/feedbacks

Background:
    Given the following "institutions" exist:
     | name | displayname | commentthreaded | allowinstitutionpublicviews |
     | instone | inst1 | 1 | 1 |
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | pageowner | password | test01@example.com | Paige | Owner | instone | internal | admin |
    Given the following "pages" exist:
     | title | description | ownertype | ownername |
     | page1 | page1 | user | pageowner |

Scenario: Threaded comments should be displayed in correct order
    Given I log in as "pageowner" with password "password"
    And I go to portfolio page "page1"
    # Add 11 comments
    And I fill in "Comment #1" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I fill in "Comment #2" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I fill in "Comment #3" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I fill in "Comment #4" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I fill in "Comment #5" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I fill in "Comment #6" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I fill in "Comment #7" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I fill in "Comment #8" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I fill in "Comment #9" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I fill in "Comment #10" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I fill in "Comment #11" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I fill in "Comment #12" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    # Go to the first page
    And I scroll to the base of id "comment-form"
    And I follow "1"
    And I should see "Comment #1"
    And I should see "Comment #10"
    And "Comment #2" "text" should appear before "Comment #3" "text"
    And I should not see "Comment #11"
    # Go to the second page
    And I scroll to the base of id "comment-form"
    And I follow "2"
    And I should see "Comment #11"
    And "Comment #11" "text" should appear before "Comment #12" "text"
    And I should not see "Comment #10"
    # Reply to a comment
    And I scroll to the base of id "comment-form"
    And I follow "1"
    And I scroll to the base of id "main-nav"
    And I click on "Reply" in "Comment #1" row
    And I fill in "Comment #1/1" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And "Comment #1/1" "text" should appear before "Comment #2" "text"
    And I scroll to the id "feedbacktable"
    And I click on "Reply" in "Comment #1/1" row
    And I fill in "Comment #1/1/1" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I scroll to the id "feedbacktable"
    And I click on "Reply" in "Comment #1/1" row
    And I fill in "Comment #1/1/2" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I scroll to the id "feedbacktable"
    And I click on "Reply" in "Comment #1/1/1" row
    And I fill in "Comment #1/1/1/1" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I scroll to the id "feedbacktable"
    And I click on "Reply" in "Comment #1/1/1/1" row
    And I fill in "Comment #1/1/1/1/1" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I scroll to the id "feedbacktable"
    And I click on "Reply" in "Comment #1/1/1/1" row
    And I fill in "Comment #1/1/1/1/2" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I scroll to the id "feedbacktable"
    And I click on "Reply" in "Comment #1/1" row
    And I fill in "Comment #1/1/3" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I scroll to the base of id "feedbacktable"
    And I click on "Reply" in "Comment #1/1/2" row
    And I fill in "Comment #1/1/2/1" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And "Comment #1/1/1/1/1" "text" should appear before "Comment #1/1/1/1/2" "text"
    And "Comment #1/1/1/1/2" "text" should appear before "Comment #1/1/2" "text"
    And "Comment #1/1/2/1" "text" should appear before "Comment #1/1/3" "text"
