@javascript @core @blocktype @blocktype_externalfeed
Feature: The external block should be added and configured in a page
    In order to make sure it is installed
    As a user I add the block to a page

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

    And the following "pages" exist:
     | title | description| ownertype | ownername |
     | Page 1 | page P1 | user | userA |

Scenario: Add some externalfeed blocks
    # Externalfeed block should be added in a page from several feed sources in
    # RSS or Atom format
    Given I log in as "userA" with password "Kupuhipa1"
    And I choose "Pages and collections" in "Portfolio"
    And I follow "Edit \"Page 1\""
    And I expand "External" node
    And I wait "1" seconds
    And I follow "External feed"
    And I press "Add"
    And I wait "1" seconds
    Then I should see "URL of a valid RSS or ATOM feed"
    And I set the field "Feed location" to "http://www.apple.com/main/rss/hotnews/hotnews.rss"
    And I press "Save"
    And I wait "2" seconds
    Then I should see "provided by Apple."
    And I follow "External feed"
    And I press "Add"
    And I wait "1" seconds
    And I set the field "Feed location" to "http://php.net/feed.atom"
    And I set the field "Items to show" to "2"
    And I enable the switch "Show feed items in full"
    And I press "Save"
    And I wait "2" seconds
    Then I should see "PHP.net"
