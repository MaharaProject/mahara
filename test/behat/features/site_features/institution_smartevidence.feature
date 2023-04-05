@javascript @core @core_portfolio
Feature: Work flow for institution member submitting collection with
    SmartEvidence and institution staff leaving feedback on the
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

   And the following "blocks" exist:
    | title                     | type     | page                   | retractable | updateonly | data                                                |
    | Portfolios shared with me | newviews | Dashboard page: UserA  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |
    | Portfolios shared with me | newviews | Dashboard page: UserB  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |

    # Background step requires an activation of a plugin
    # we are looking to simplify this
    # see - https://reviews.mahara.org/#/c/9032/

    # Admin user enable annotations module
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Plugin administration" in "Extensions" from administration menu
    And I click on "Show" in the "#activate_blocktype_annotation_submit_container" "css_element"
    # Make sure we have a matrix config form
    And I choose "SmartEvidence" in "Extensions" from administration menu
    And I click on "Import" in the "Arrow-bar nav" "Nav" property
    And I attach the file "example.matrix" to "Matrix file"
    And I click on "Upload matrix"
    # Enable Institutions to allow SmartEvidence
    And I choose "Settings" in "Institutions" from administration menu
    And I click on "Edit"
    And I enable the switch "SmartEvidence"
    And I click on "Submit"
    Then I log out

Scenario: SmartEvidence interaction by member / staff
    # Creating a collection of existing pages
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Create" in the "Create" "Views" property
    When I click on "Collection" in the dialog
    And I fill in the following:
    | Collection name | SmartEvidence Collection 1 |
    | Collection description | SmartEvidence Collection 1 description |
    And I select "Title of your framework" from "SmartEvidence framework"
    And I click on "Continue: Edit collection pages"
    And I click on "All"
    And I click on "Add pages"
    When I click on "Continue: Share"
    Then I click on "Return to portfolios"

    # Mahara member makes page visible to public
    And I click on "Manage sharing" in "SmartEvidence Collection 1" card access menu
    And I select "Public" from "accesslist[0][searchtype]"
    And I click on "Save"
    # Mahara member must make comment on competencies before it can be accessed by admin/staff
    When I click on "SmartEvidence Collection 1"
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
    And I click on "SmartEvidence Collection 1"
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
    And I click on "SmartEvidence Collection 1"
    # Mahara member clicks the next to view next page
    And I click on "Next page"
    When I click on "Place feedback"
    And I fill in "Mahara member placing feedback" in editor "Feedback"
    And I click on "Place feedback" in the "Feedback modal content" "Modal" property
    And I wait "1" seconds
    When I click on "Feedback (3)"
    # Mahara member should see 3 feedback annotations
    Then I should see "Staff annotation description"
    And I should see "Assessment: Doesn't meet the standard"
    And I should see "Mahara member placing feedback"
    # verify that user cannot delete Other user's annotations.
    And I should see "Edit" in the "Feedback annotation row 3" "Smartevidence" property
    And I should not see "Edit" in the "Feedback annotation row 2" "Smartevidence" property
