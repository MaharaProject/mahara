@javascript @core @core_artefact @core_content @artefact_comment
Feature: Writing and deleting comments

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | admin |
    Given the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01 | user | UserA |
     | Page UserA_02 | Page 02 | user | UserA |
    Given the following "permissions" exist:
     | title | accesstype | accessname | allowcomments | approvecomments |
     | Page UserA_01 | public | public | 1 | 0 |
     | Page UserA_02 | loggedin | loggedin | 1 | 0 |

Scenario: Adding and deleting public comments
    # Adding
    Given I go to portfolio page "Page UserA_01"
    # The label for message text area - anonymous people
    And I fill in "Name" with "Joe Anonymous"
    # No TinyMCE editor for anonymous people
    And I fill in "Comment" with "Public comment by anonymous person"
    And I enable the switch "Make comment public"
    And I press "Comment"
    And I log in as "UserA" with password "Kupuh1pa!"
    And I go to portfolio page "Page UserA_01"
    # The label for message text area - logged in people
    And I fill in "Comment by page owner" in editor "Comment"
    And I press "Comment"
    Then I should see "Joe Anonymous"
    And I should see "Public comment by anonymous person"
    And I should see "Comment by page owner"

    # Deleting
    Given I delete the "Public comment by anonymous person" row
    # If a comment with replies is deleted, we hide its contents but leave a placeholder
    # in place to give context to the replies
    Then I should not see "Public comment by anonymous person"
    And I should see "Comment removed by the owner"
    And I should see "Joe Anonymous"
    # Make sure only the selected comment was deleted"
    And I should see "Comment by page owner"
    # If a comment without replies is deleted, we hide it. And then if its parent was
    # deleted and is showing a placeholder, we now hide it as well, because it is no
    # longer needed for context.
    Given I delete the "Comment by page owner" row
    # Reload the page to get rid of the status message
    And I go to portfolio page "Page UserA_01"
    Then I should not see "Comment by page owner"
    And I should not see "Comment removed"


Scenario: Add comments block to page
    Given I go to portfolio page "Page UserA_01"
    # The label for message text area - anonymous people
    And I fill in "Name" with "Joe Anonymous"
    # No TinyMCE editor for anonymous people
    And I fill in "Comment" with "Public comment by anonymous person"
    And I enable the switch "Make comment public"
    And I press "Comment"
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    And I wait "1" seconds
    # Add a comments block so that comments will now be at the top of the page
    When I follow "Drag to add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on "Show more"
    And I click on "Show more"
    And I click on "Comments" in the "Content types" property
    Then I should see "Comments for this page will be displayed here rather than at the bottom of the page."
    And I display the page
    Then I should see "Joe Anonymous"
    And I should see "Public comment by anonymous person"

Scenario: Comments update the page's mtime
    Given I log in as "admin" with password "Kupuh1pa!"

    # Set New Views to only show me the most recently updated page
    And I follow "Edit dashboard"
    And I configure the block "Latest changes I can view"
    And I set the field "Maximum number of results to show" to "1"
    And I press "Save"

    # Public comment updates page last updated
    And I go to portfolio page "Page UserA_01"
    And I fill in "Public comment" in editor "Comment"
    And I press "Comment"
    And I choose "Dashboard" from main menu
    And I scroll to the base of id "column-container"
    Then I should see "Page UserA_01" in the ".bt-newviews" element
    And I should not see "Page UserA_02" in the ".bt-newviews" element

    # Private comment updates page last updated
    And I go to portfolio page "Page UserA_02"
    And I fill in "Private comment" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I choose "Dashboard" from main menu
    And I scroll to the id "column-container"
    And I wait "1" seconds
    Then I should see "Page UserA_02" in the ".bt-newviews" element
    And I should not see "Page UserA_01" in the ".bt-newviews" element
