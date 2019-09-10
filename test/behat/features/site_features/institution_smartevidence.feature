@javascript @core @core_portfolio
Feature: Work flow for institution member submitting collection with
    smart evidence and institution staff leaving feedback on the
    submission.

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Alice | User | mahara | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | staff |

And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01 | user | UserA |
    | Page UserA_02 | Page 02 | user | UserA |
    | Page UserA_03 | Page 02 | user | UserA |

    # Background step requires an activation of a plugin
    # we are looking to simplify this
    # see - https://reviews.mahara.org/#/c/9032/

    # Admin user enable annotations module
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Plugin administration" in "Extensions" from administration menu
    And I press "activate_blocktype_annotation_submit"
    # Make sure we have a matrix config form
    And I choose "SmartEvidence" in "Extensions" from administration menu
    And I follow "Import" in the "Arrow-bar nav" property
    And I attach the file "example.matrix" to "Matrix file"
    And I press "Upload matrix"
    # Enable Institutions to allow SmartEvidence
    And I choose "Settings" in "Institutions" from administration menu
    And I press "Edit"
    And I enable the switch "Allow SmartEvidence"
    And I press "Submit"
    Then I log out

Scenario: SmartEvidence interaction by member / staff
    # Creating a collection of existing pages
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I follow "Add"
    When I click on "Collection" in the dialog
    And I fill in the following:
    | Collection name | Smart Evidence Collection 1 |
    | Collection description | Smart Evidence Collection 1 description |
    And I select "Title of your framework" from "SmartEvidence framework"
    And I press "Next: Edit collection pages"
    And I follow "All"
    And I press "Add pages"
    When I follow "Next: Edit access"
    Then I click on "Return to pages and collections"

    # Mahara member makes page visible to public
    And I click on "Manage access" in "Smart Evidence Collection 1" card access menu
    And I select "Public" from "accesslist[0][searchtype]"
    And I click on "Save"
    # Mahara member must make comment on competencies before it can be accessed by admin/staff
    When I follow "Smart Evidence Collection 1"
    # click the standard group 3.1 to make an annotation for page 1 column
    And I click on the matrix point "3,22"
    And I fill in "First annotation description" in first editor
    And I click on "Save"
    # click the standard group 3.1 to make an annotation for page 2 column
    And I click on the matrix point "4,22"
    And I fill in "First annotation description" in first editor
    And I click on "Save"
    # click the standard group 3.1 to make an annotation for page 3 column
    And I click on the matrix point "5,22"
    And I fill in "First annotation description" in first editor
    And I click on "Save"
    And I log out

    # Log in as admin/staff to assess the collection pages and make comments
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I wait "1" seconds
    And I follow "Smart Evidence Collection 1"
    # Admin/staff selects the competencies ready for assessment and makes an annotation
    # And sets the standard status
    And I click on the matrix point "3,22"
    And I fill in "Staff annotation description" in first editor
    And I click on "Place feedback"
    And I select "Doesn't meet the standard" from "Assessment"
    And I click on "Save"
    And I click on the matrix point "4,22"
    And I fill in "Staff annotation description" in first editor
    And I click on "Place feedback"
    And I select "Partially meets the standard" from "Assessment"
    And I click on "Save"
    And I click on the matrix point "5,22"
    And I fill in "Staff annotation description" in first editor
    And I click on "Place feedback"
    And I select "Meets the standard" from "Assessment"
    And I click on "Save"
    And I log out

    # Mahara member logs in, views annotations and confirms cannot delete annotations (Bug - 1781278)
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I follow "Smart Evidence Collection 1"
    # Mahara member clicks the next to view next page
    And I press "Next page"
    When I follow "Place feedback"
    And I fill in "Mahara member placing feedback" in editor "Feedback"
    And I press "Place feedback"
    And I wait "1" seconds
    When I follow "Feedback (3)"
    # Mahara member should see 3 feedback annotations
    Then I should see "Staff annotation description"
    And I should see "Assessment: Doesn't meet the standard"
    And I should see "Mahara member placing feedback"
    # verify that user cannot delete Other user's annotations.
    And I should see "Edit" in the "//*[starts-with(@id,'annotation_feedbacktable')]/div/div/div[2]/li[3]/div[1]/div" "xpath_element"
    And I should not see "Edit" in the "//*[starts-with(@id,'annotation_feedbacktable')]/div/div/div[2]/li[2]/div[1]/div" "xpath_element"
