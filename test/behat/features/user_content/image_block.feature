@javascript @core @blocktype @blocktype_addimageblock
Feature: Creating/deleting an image block
    As a user
    I want to add and remove image blocks from my page
    So I can control the content

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

     And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01| user | UserA |

Scenario: Create and delete image block
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property
    And I set the field "Block title" to "Image Block 1"
    And I click on blocktype "Image"
    Then I should see "Image Block 1: Edit"
    And I expand the section "Image"
    And I attach the file "Image1.jpg" to "File"
    Then I should see "Image - Image1.jpg"
    And I enable the switch "Show description"
    And I click on "Save" in the "Submission" "Modal" property
    And I display the page
    And I should see "Image1.jpg"
    # delete image block
    And I click on "Edit"
    And I delete the block "Image Block 1"
    And I display the page
    Then I should not see "Image Block 1"
