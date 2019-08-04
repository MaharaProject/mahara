@javascript @core @blocktype @blocktype_notes
Feature: Add a note which copies the contents
from an existing note using the "Use content from another note" option

Background:

Given the following "users" exist:
  | username | password | email | firstname | lastname | institution | authname | role |
  | UserA | Kupuh1pa! | UserA@example.com | Angela | User | mahara | internal | member |
And the following "pages" exist:
  | title | description| ownertype | ownername |
  | Page UserA_01 | Page 01| user | UserA |

Scenario: Use content from another note (Bug 1710988)
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I follow "Page UserA_01"
  And I follow "Edit"
  When I follow "Add a new block" in the "blocktype sidebar" property
  And I press "Add"
  And I click on "Show more"
  And I click on "Show more"
  And I click on "Note" in the "Content types" property
  And I set the following fields to these values:
  | Block title | Note block 1 |
  | Block content | This is a test |
  And I press "Save"
  When I follow "Add a new block" in the "blocktype sidebar" property
  And I press "Add"
  And I click on "Show more"
  And I click on "Show more"
  And I click on "Note" in the "Content types" property
  And I follow "Use content from another note"
  And I select the radio "Note block 1"
  # Set title after selection as selection updates the title with original one
  And I set the following fields to these values:
  | Block title | Note block 2 |
  And I press "Save"
  And I should see "This is a test" in the block "Note block 2"
