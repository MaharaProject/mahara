@javascript @core @core_group @core_portfolio @core_collection

Feature: Show collection shared with a group on the group homepage (Bug 1655456)

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuhipa1 | UserB@example.org | Bob | User | mahara | internal | member |
     
    And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | GroupA | UserB | GroupA owned by UserB | standard | ON | OFF | all | OFF | OFF | UserA |  |

    And the following "pages" exist:
      | title | description | ownertype | ownername |
      | Page UserA_01 | Page 01 |  user | UserA |
      | Page UserA_02 | Page 02 |  user | UserA |
      | Page UserA_03 | Page 03 |  user | UserA |
      | Page UserA_04 | Page 04 |  user | UserA |

    And the following "collections" exist:
      | title | description | ownertype | ownername | pages |
      | Collection UserA_01 | Collection 01 |  user | UserA  | Page UserA_01, Page UserA_02, Page UserA_03, Page UserA_04 |

Scenario: When a collection is shared and a page is deleted from the collection the second user should still see the collection at about tab

    # Log in as a normal user
    Given I log in as "UserA" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Angela"
    And I should see "GroupA"
    # Share page to the group
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I follow "Collection UserA_01"
    And I follow "Edit this page"
    And I follow "Share"
    And I select "GroupA" from "accesslist[0][searchtype]"
    And I press "Save"
    # Delete a page from the collection
    Then I choose "Pages and collections" in "Portfolio" from main menu
    And I click on "Collection UserA_01" panel menu
    And I click on "Manage" in "Collection UserA_01" panel menu
    And I click on "Remove" in "Page UserA_01" row
    And I log out

    # Log in as UserB
   And I log in as "UserB" with password "Kupuhipa1"
   And I choose "Groups" from main menu
   And I follow "GroupA"
   And I follow "Collection UserA_01"
   And I should see "Page UserA_02"
