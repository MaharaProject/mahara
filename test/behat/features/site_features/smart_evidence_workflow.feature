@javascript @core @core_portfolio
Feature: Smart evidence work flow from Institution member submitting to Institution staff making
    and adding comments to collection submission.

Background: Setting up test data for people and portfolio pages
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

    # Background step required
    # And the site has been made ready for smart evidence - Admin set up site ready for Smartevidence
    # annotations = enabled
    # Matrix config file is loaded
    # Enable Institutions to allow Smart Evidence
    # ********************************************************
     # Admin enables annotations module
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Plugin administration" in "Extensions" from administration menu
    And I press "activate_blocktype_annotation_submit"
    # confirm Smart evidence is also enabled
    And I should see "Hide" in the "Smartevidence" "Smartevidence" property
    # Make sure we have a matrix config form
    And I choose "SmartEvidence" in "Extensions" from administration menu
    And I follow "Import" in the "Arrow-bar nav" "Nav" property
    And I attach the file "example.matrix" to "Matrix file"
    And I press "Upload matrix"
    # Check that we have new framework
    Then I should see "Title of your framework"
    # Enable Institutions to allow Smart Evidencelcd
    And I choose "Settings" in "Institutions" from administration menu
    And I press "Edit"
    And I enable the switch "Allow SmartEvidence"
    And I press "Submit"
    Then I log out

Scenario: 1) Mahara member creates a collection of 3 pages and submits for marking
    2) Mahara admin/staff marks and comments on submission
    3) Mahara member places feedback in return
    4) Check that feedback comments made by Mahara admin/staff cannot be deleted
    # Creating a collection AND adding pages
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I press "Add"
    When I click on "Collection" in the dialog
    And I fill in the following:
    | Collection name | Smart Evidence Collection 1 |
    | Collection description | Smart Evidence Collection 1 description |
    And I select "Title of your framework" from "SmartEvidence framework"
    # Adding page 1, 2 & 3 to the collection
    And I press "Next: Edit collection pages"
    And I follow "All"
    And I press "Add pages"
    # Verifying that the pages were added
    Then I should see "Page UserA_01"
    And I should see "Page UserA_03"
    When I press "Next: Edit access"
    Then I should see "Edit access"

    # Mahara member makes to page visible to public
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Manage access" in "Smart Evidence Collection 1" card access menu
    And I select "Public" from "accesslist[0][searchtype]"
    And I click on "Save"
    # Verify author is on correct page
    And I should see "Smart Evidence Collection 1"

    # Mahara member must make comment on a competencies before it can be accessed by admin/staff
    When I follow "Smart Evidence Collection 1"
    # click the standard group 3.1 to make an annotation for page 1 column
    And I click on the matrix point "3,22"
    And I fill in "First annotation description" in first editor
    And I click on "Save"
    # click the standard group 3.2 to make an annotation for page 2 column
    And I click on the matrix point "4,22"
    And I fill in "First annotation description" in first editor
    And I click on "Save"
    # click the standard group 3.1 to make an annotation for page 3 column
    And I click on the matrix point "5,22"
    And I fill in "First annotation description" in first editor
    And I click on "Save"
    # Mahara member logs out so staff can log in and make an assessment
    And I log out

    # Log in as admin/staff grade the collection pages and make comments ( 3 pages 3.1sub level of the standard only)
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I wait "1" seconds
    And I should see "Smart Evidence Collection 1 "
    And I follow "Smart Evidence Collection 1"
    # Admin/staff selects the competencies ready for assessment and makes a Annotation
    And I click on the matrix point "3,22"
    And I fill in "Staff annotation description" in first editor
    And I click on "Place feedback"
    And I fill in "Another annotation description" in first editor
    And I disable the switch "Make public"
    And I click on "Place feedback"
    And I should see "This feedback is private."
    And I select "Partially meets the standard" from "Assessment"
    And I click on "Save"
    And I click on the matrix point "4,22"
    And I fill in "Staff annotation description" in first editor
    And I click on "Place feedback"
    And I select "Meets the standard" from "Assessment"
    And I click on "Save"
    And I click on the matrix point "5,22"
    And I fill in "Staff annotation description" in first editor
    And I click on "Place feedback"
    And I fill in "Another Staff annotation description" in first editor
    And I disable the switch "Make public"
    And I select "Doesn't meet the standard" from "Assessment"
    And I click on "Save"
    And I log out

    # Mahara member logs in, views annotations and confirms cannot delete annotations (Bug - 1781278)
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I should see "Smart Evidence Collection 1 "
    And I follow "Smart Evidence Collection 1"
    # Mahara member clicks the next to view next page
    And I press "Next page"
    # Mahara member places feedback
    When I follow "Place feedback"
    And I fill in "Mahara member placing feedback" in editor "Feedback"
    And I press "Place feedback"
    And I wait "1" seconds
    When I follow "Feedback (4)"
    # Mahara member should see 3 feedback annotations
    Then I should see "Staff annotation description"
    And I should see "Assessment: Partially meets the standard "
    And I should see "Mahara member placing feedback"
    And I should see "Make public"
    # Mahara member should see edit and delete for their own annotation feedback comment
    And I should see "Edit" in the "Feedback annotation row 4" "Smartevidence" property
    # verify that someone cannot delete other people's annotations.
    And I should not see "Edit" in the "Feedback annotation row 2" "Smartevidence" property
