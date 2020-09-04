@javascript @core @core_group @core_portfolio @core_collection

Feature: Show collection shared with a group on the group homepage (Bug 1655456)

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
     
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
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Verifying log in was successful
    And I should see "Angela" in the page
    And I should see "GroupA" in the page
    # Share page to the group
    And I choose "Pages and collections" in "Create" from main menu
    And I follow "Collection UserA_01"
    And I follow "Edit"
    And I follow "Share" in the "Toolbar buttons" property
    And I select "GroupA" from "accesslist[0][searchtype]"
    And I press "Save"
    # Delete a page from the collection
    Then I choose "Pages and collections" in "Create" from main menu
    And I click on "Manage" in "Collection UserA_01" card menu
    And I click on "Remove" in "Page UserA_01" row
    And I log out

    # Log in as UserB
    And I log in as "UserB" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I follow "GroupA"
    And I follow "Collection UserA_01"
    And I should see "Page UserA_02" in the page
