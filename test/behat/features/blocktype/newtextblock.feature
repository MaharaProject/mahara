@javascript @core @blocktype @blocktype_newtextblock
Feature: Creating a new text block on a page
    the block
    should be visible on the page after it is created

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

     And the following "pages" exist:
     | title | description| ownertype | ownername |
     | Page 1 | page P1 | user | userA |


Scenario: Create Text block
    Given I log in as "userA" with password "Kupuhipa1"
    And I choose "Pages" in "Portfolio"
    And I follow "Edit \"Page 1\""
    And I follow "Text"
    And I wait "1" seconds
    And I press "Add"
    And I wait "1" seconds
    Then I should see "Text: Configure"
    And I set the field "Block title" to "Text Block 1"
    And I set the field "Block content" to "Here is a new block."
    And I press "Save"
    And I wait "2" seconds
    Then I should see "Here is a new block" in the "div#column-container" element
    And I should see "Text block 1" in the "div#column-container" element
    And I scroll to the id "main-nav"
    And I click on "Display page"
    Then I should see "Here is a new block"
    And I should see "Text block 1"
