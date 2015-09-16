@javascript @core @core_artefact @core_content @artefact_comment
Feature: Writing and deleting comments

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | pageowner | password | test01@example.com | Paige | Owner | mahara | internal | admin |
    Given the following "pages" exist:
     | title | description | ownertype | ownername |
     | page1 | page1 | user | pageowner |

Scenario: Public comment by page owner, public reply by third party
    Given I log in as "pageowner" with password "password"
    And I go to portfolio page "page1"
    And I follow "Add comment"
    And I fill in "Comment 1" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I wait "1" seconds
    And I press "Comment"
    And I follow "Add comment"
    And I fill in "Comment 2" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"
    And I delete the "Comment 2" row
    Then I should see "Comment 1"
    And I should not see "Comment 2"
