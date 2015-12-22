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
    Given the following "permissions" exist:
     | title | accesstype | accessname | allowcomments | approvecomments |
     | page1 | public | public | 1 | 0 |

Scenario: Adding and deleting public comments
    # Adding
    Given I go to portfolio page "page1"
    And I fill in "Name" with "Joe Anonymous"
    # No WYSIWYG editor for anonymous users
    And I fill in "Message" with "Public comment by anonymous user"
    And I enable the switch "Make public"
    And I press "Comment"
    And I log in as "pageowner" with password "password"
    And I go to portfolio page "page1"
    And I fill in "Comment by page owner" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I wait "1" seconds
    And I press "Comment"
    Then I should see "Joe Anonymous"
    And I should see "Public comment by anonymous user"
    And I should see "Comment by page owner"

    # Deleting
    Given I delete the "Public comment by anonymous user" row
    # If the comment is deleted, the comment author remains visible but the comment
    # text is replaced with "Comment removed by owner"
    Then I should not see "Public comment by anonymous user"
    And I should see "Comment removed by the owner"
    And I should see "Joe Anonymous"
    # Make sure only the selected comment was deleted"
    And I should see "Comment by page owner"

Scenario: Comments update the page's mtime
    Given I log in as "pageowner" with password "password"

    # Set New Views to only show me the most recently updated page
    And I follow "Edit dashboard"
    And I configure the block "Latest changes I can view"
    And I set the field "Maximum number of results to show" to "1"
    And I press "Save"

    # Public comment updates page last updated
    And I go to portfolio page "page1"
    And I fill in "Public comment" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I wait "1" seconds
    And I press "Comment"
    And I follow "Dashboard"
    Then I should see "page1" in the ".bt-newviews" element
    And I should not see "page2" in the ".bt-newviews" element

    # Private comment updates page last updated
    And I go to portfolio page "page2"
    And I fill in "Private comment" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I disable the switch "Make public"
    And I wait "1" seconds
    And I press "Comment"
    And I follow "Dashboard"
    Then I should see "page2" in the ".bt-newviews" element
    And I should not see "page1" in the ".bt-newviews" element
