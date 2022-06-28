@javascript @core @blocktype @blocktype_openbadgedisplayer
Feature: The openbadges block should be present
    In order to make sure it is installed
    As a user I add the block to a page

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |
    | pcnz | Institution One | ON | OFF |

    And the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "pages" exist:
     | title | description | ownertype | ownername |
     | Page UserA_01 | Page 01 | user | UserA |

Scenario: Open badges block
    # As the open badges block normally fetches user data from third
    # party site we can't currently test that part and ship the details in this test
    # So all we can do is check that the block exists and saves to a page
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    # PCNZ customisation - no edit/copy
    And I click on "Page UserA_01"
    Then I press "Edit"
    When I click on the add block button
    And I press "Add"
    And I click on blocktype "Open Badges"
    And I press "Save"

    # Need to add delays as Mahara needs to talk to external site.
    # unreliable connection to server produces errors
    # And I wait "2" seconds
    # Then I should see "Your email UserA@example.org is not found in the service"
    # And I press "Save"
    # And I wait "5" seconds
    # Then I should see "No public badge collections / badges found."
