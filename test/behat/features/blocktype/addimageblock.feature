@javascript @core @blocktype @blocktype_addimageblock
Feature: Creating a new image block on a page
    in order to check that the image is visible
    after it is created

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

     And the following "pages" exist:
     | title | description| ownertype | ownername |
     | Page 1 | page P1 | user | userA |


Scenario: Create Image block
    Given I log in as "userA" with password "Kupuhipa1"
    And I choose "Pages and collections" in "Portfolio" from Main menu
    And I click on "Page 1" panel menu
    And I click on "Edit" in "Page 1" panel menu
    And I follow "Image"
    And I wait "1" seconds
    And I press "Add"
    And I wait "1" seconds
    Then I should see "Image: Configure"
    And I set the field "Block title" to "Image Block 1"
    And I follow "Image"
    And I attach the file "Image1.jpg" to "File"
    Then I should see "Image - Image1.jpg"
    And I set the field "Show description" to "1"
    And I press "Save"
    And I wait "2" seconds
    And I scroll to the id "main-column-container"
    And I follow "Display page"
    And I should see "Image1.jpg"
