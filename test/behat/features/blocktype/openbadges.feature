@javascript @core @blocktype @blocktype_openbadgedisplayer
Feature: The openbadges block should be present
    In order to make sure it is installed
    As a user I add the block to a page

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

    And the following "pages" exist:
     | title | description| ownertype | ownername |
     | Page 1 | page P1 | user | userA |

Scenario: Open badges block
    # As the open badges block normally fetches user data from third
    # party site we can't currently test that part and ship the details in this test
    # So all we can do is check that the block exists and saves to a page
    Given I log in as "userA" with password "Kupuhipa1"
    And I choose "Pages" in "Portfolio"
    And I follow "Edit \"Page 1\""
    And I expand "External" node
    And I wait "1" seconds
    And I follow "Open Badges"
    And I press "Add"
    And I wait "5" seconds
    Then I should see "Your email is not found in the service"
    And I press "Save"
    Then I should see "No public badge collections/badges found."
