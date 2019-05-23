@javascript @core
Feature: Configure navigation block
  As a user
  I want to add a navigation block to my page
  So I can easily get from that page to another

Background:
  Given the following "users" exist:
  | username | password | email | firstname | lastname | institution | authname | role |
  | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

  Given the following "pages" exist:
  | title | description | ownertype | ownername |
  | Page UserA_01 | Page 01 | user | UserA |
  | Page UserA_02 | Page 02 | user | UserA |
  | Page UserA_03 | Page 03 | user | UserA |
  | Page UserA_04 | Page 04 | user | UserA |

  And the following "collections" exist:
  | title | description | ownertype | ownername | pages |
  | Collection UserA_01 | This is collection A | user | UserA | Page UserA_01, Page UserA_02 |
  | Collection UserA_02 | This is collection B | user | UserA | Page UserA_03, Page UserA_04 |

Scenario:
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I choose "Pages and collections" in "Create" from main menu
  And I follow "Collection UserA_01"
  And I follow "Edit"
  When I follow "Add a new block" in the "blocktype sidebar" property
  And I press "Add"
  And I click on "Show more"
  And I click on "Show more"
  And I click on "Navigation" in the "Content types" property
  And I select "Collection UserA_02" from "Collection"
  And I click on "Set a block title"
  And I set the field "Block title" to "Nav for collection B"
  And I enable the switch "Add to all pages"
  And I select "Automatically retract" from "Retractable"
  And I press "Save"
  And I display the page
  # test retractable setting
  Then I should see "Nav for collection B"
  And I press "Next page"
  And I should not see "Page UserA_04"
  When I expand "Nav for collection B" node
  # test link works
  And I follow "Page UserA_04"
  And I should see "Page 04"
  And I should see "You are on page 2/2"
