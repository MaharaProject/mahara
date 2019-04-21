@javascript @core @view @versioning
Feature: Creating versions of a page
    As a user
    I want to be able to view older versions of my page
    So I can control the content

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

     And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01| user | UserA |

Scenario: Add blocks and create versions
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    And I follow "Text"
    And I press "Add"
    And I set the field "Block title" to "Text block version 1"
    And I set the field "Block content" to "Here is the first version of the block."
    And I press "Save"
    And I display the page
    And I press "More options"
    And I follow "Save to timeline"
    And I should see "Saved to timeline"
    And I follow "Edit"
    And I configure the block "Text block version 1"
    And I set the field "Block title" to "Text block version 2"
    And I set the field "Block content" to "Here is the second version of the block."
    And I press "Save"
    And I display the page
    And I press "More options"
    And I follow "Save to timeline"
    And I press "More options"
    And I follow "Timeline"
    And I follow "Go to the next version"
    And I wait "1" seconds
    Then I should see "Here is the second version of the block"
