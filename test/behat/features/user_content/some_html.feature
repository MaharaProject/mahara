@javascript @core @blocktype @blocktype_HTML
Feature: Adding Some HTML to a page
    As a user when I add some HTML
    to a page I want to check it
    displays correctly

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |

Scenario:
    # Logging in as a user
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    # Configuring the block
    When I follow "Drag to add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on "Show more"
    And I click on "Show more"
    And I click on "Show more"
    And I click on "Some HTML" in the "Content types" property
    And I set the field "Block title" to "Some HTML"
    And I follow "File"
    And I attach the file "test_html.html" to "File"
    And I press "Save"
    #give time for the block to resize
    And I wait "1" seconds
    And I display the page
    #check content of HTML block shows content, but not html tags
    And I should see "Mahara does HTML"
    And I should not see "<h1>Mahara does HTML</h1>"
    And I should see images within the block "Some HTML"
    And I follow "mahara manual"
    And I wait "3" seconds
    And I switch to the new window
    Then I should see "This is the manual for Mahara"
    And I switch to the main window
