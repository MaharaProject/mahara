@javascript @core @core_group @core_portfolio @core_collection

Feature: Show collection shared with a group on the group homepage (Bug 1655456)

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
     | userB | Kupuhipa1 | test02@example.com | Son | Nguyen | mahara | internal | member |
    And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | Group Y | userB | This is group Y | standard | ON | OFF | all | OFF | OFF | userA |  |

    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | Page 01 | This is the page 01 |  user | userA |
      | Page 02 | This is the page 02 |  user | userA |
      | Page 03 | This is the page 03 |  user | userA |
      | Page 04 | This is the page 04 |  user | userA |

    And the following "collections" exist:
      | title | description| ownertype | ownername | pages |
      | Collection 01 | This is the collection 01 |  user | userA  | Page 01, Page 02, Page 03, Page 04 |

Scenario: When a collection is shared and a page is deleted from the collection the second user should still see the collection at about tab

    # Log in as a normal user
    Given I log in as "userA" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Pete"
    And I should see "Group Y"
    # Share page to the group
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I follow "Collection 01"
    And I follow "Edit this page"
    And I follow "Share page"
    And I select "Group Y" from "accesslist[0][searchtype]"
    And I press "Save"
    # Delete a page from the collection
    Then I choose "Pages and collections" in "Portfolio" from main menu
    And I click on "Collection 01" panel menu
    And I click on "Manage" in "Collection 01" panel menu
    And I click on "Remove" in "Page 01" row
    And I log out

    # Log in as userB
   And I log in as "userB" with password "Kupuhipa1"
   And I choose "Groups" from main menu
   And I follow "Group Y"
   And I follow "Collection 01"
   And I should see "Page 02"
