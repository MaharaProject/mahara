@javascript @core @core_artefact @core_content @artefact_comment
Feature: Writing and deleting comments

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | pageowner | password | test01@example.com | Paige | Owner | mahara | internal | admin |
    Given the following "pages" exist:
     | title | description | ownertype | ownername |
     | page1 | page1 | user | pageowner |
     | page2 | page2 | user | pageowner |

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

Scenario: Comments update the page's mtime
    Given I log in as "pageowner" with password "password"

    # Set New Views to only show me the most recently updated page
    And I follow "Edit dashboard"
    And I configure the block "Latest changes I can view"
    And I set the field "Maximum number of results to show" to "1"
    And I press "Save"

    # Public comment updates page last updated
    And I go to portfolio page "page1"
    And I follow "Add comment"
    And I fill in "Public comment" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I wait "1" seconds
    And I press "Comment"
    And I follow "Dashboard"
    Then I should see "page1" in the ".bt-newviews" element
    And I should not see "page2" in the ".bt-newviews" element

    # Private comment updates page last updated
    And I go to portfolio page "page2"
    And I follow "Add comment"
    And I fill in "Private comment" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I uncheck "Make public"
    And I wait "1" seconds
    And I press "Comment"
    And I follow "Dashboard"
    Then I should see "page2" in the ".bt-newviews" element
    And I should not see "page1" in the ".bt-newviews" element
