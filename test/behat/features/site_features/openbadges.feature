@javascript @core @blocktype @blocktype_openbadgedisplayer
Feature: The openbadges block should be present
    In order to make sure it is installed
    As a user I add the block to a page

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01 | user | UserA |

Scenario: Open badges block
    # As the open badges block normally fetches user data from third
    # party site we can't currently test that part and ship the details in this test
    # So all we can do is check that the block exists and saves to a page
    Given I log in as "UserA" with password "Kupuhipa1"
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I click on "Page UserA_01" panel menu
    And I click on "Edit" in "Page UserA_01" panel menu
    And I expand "External" node
    And I scroll to the base of id "content-editor-foldable"
    And I follow "Open Badges"
    And I press "Add"
    Then I should see "Your email UserA@example.org is not found in the service"
    And I press "Save"
    Then I should see "No public badge collections / badges found."
