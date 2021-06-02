@javascript @core @blocktype @blocktype_externalfeed
Feature: The external block should be added and configured in a page
    In order to make sure it is installed
    As a user I add the block to a page

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01| user | UserA |

Scenario: Add some externalfeed blocks
    # Externalfeed block should be added in a page from several feed sources in
    # RSS or Atom format
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    When I click on the add block button
    And I press "Add"
    And I click on blocktype "External feed"
    Then I should see "URL of a valid RSS or ATOM feed"
    And I fill in "Feed location" with "http://www.apple.com/main/rss/hotnews/hotnews.rss"
    And I fill in "Items to show" with "2"
    And I enable the switch "Show feed items in full"
    And I press "Save"
    Then I should see "provided by Apple."