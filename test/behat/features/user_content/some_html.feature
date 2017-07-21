@javascript @core @blocktype @blocktype_HTML
Feature: Adding Some HTML to a page
    As a user when I add some HTML
    to a page I want to check it
    displays correctly

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |

Scenario:
    # Logging in as a user
    Given I log in as "UserA" with password "Kupuhipa1"
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I click on "Page UserA_01" panel menu
    And I click on "Edit" in "Page UserA_01" panel menu
    # Configuring the block
    And I expand "Media" node
    And I wait "1" seconds
    And I follow "Some HTML" in the "blocktype sidebar" property
    And I press "Add"
    And I follow "File"
    And I attach the file "test_html.html" to "File"
    And I press "Save"
    And I display the page
    #check content of HTML block shows content, but not html tags
    Then I should see "mahara manual"
    And I should not see "<a href=\"http://manual.mahara.org\">"
    And I should see "There is a cat in a table"
    And I should not see "</h2></strong>"
    And I should not see "<title>Sample HTML file</title>"
    And I should see images within the block "Some HTML"
    And I follow "mahara manual"
    And I wait "2" seconds
    Then I should see "This is the user manual for Mahara"
