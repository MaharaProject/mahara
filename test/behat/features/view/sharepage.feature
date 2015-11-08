@javascript @core @core_view @core_portfolio @bob1
Feature: The 'Share page' link on the "Edit content" screen

In order to be able to see the right view access selections I need to
add a page to a collection.

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | Page 1 | This is the page in collection | user | userA |
      | Page 2 | This is the solo page | user | userA |
    And the following "collections" exist:
      | title | description| ownertype | ownername | pages |
      | Collection One | collection C1 | user | userA | Page 1 |

Scenario: Testing that view access for views in collections are editable properly
    # Checking the right selected options display on view access
    Given I log in as "userA" with password "Kupuhipa1"
    And I choose "Pages" in "Portfolio"
    And I follow "Edit \"Page 1\""
    And I follow "Share page"
    Then I should see "Collection One"
    And I should not see "Page 1"
    
    And I choose "Pages" in "Portfolio"
    And I follow "Edit \"Page 2\""
    And I follow "Share page"
    Then I should see "Page 2"
    And I should not see "Collection One"
