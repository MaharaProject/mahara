@javascript @core @core_view
Feature: Added ID's for text blocks
In order to change the settings of a block
As an admin
I need to be able to click on delete and config of a block

Scenario: Clicking ID's (Bug 1428456)
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Portfolio"
 And I follow "Profile page"
 And I follow "Edit this page"

 # Checking if we can add a block
 And I follow "Text"
 And I press "Add"
 And I wait "1" seconds
 And I set the following fields to these values:
 | Block title | Ulysses |
 | Block content | <p>Stately, plump Buck Mulligan came from the stairhead, bearing a bowl of lather on which a mirror and a razor lay crossed ...</p> |
 And I press "Save"
 Then I should see "Buck Mulligan"

 And I follow "Text"
 And I press "Add"
 And I wait "1" seconds
 And I set the following fields to these values:
 | Block title | The Sun Also Rises |
 | Block content | <p>Robert Cohn was once middleweight boxing champion of Princeton. Do not think that I am very much impressed by that as a boxing title, but it meant a lot to Cohn...</p> |
 And I press "Save"
 Then I should see "Robert Cohn"

 And I follow "Text"
 And I press "Add"
 And I wait "1" seconds
 And I set the following fields to these values:
 | Block title | 1984 |
 | Block content | <p>It was a bright cold day in April, and the clocks were striking thirteen. Winston Smith, his chin nuzzled into his breast in an effort to escape the vile wind...</p> |
 And I press "Save"
 Then I should see "Winston Smith"

 # Checking if we can edit a block
 When I configure the block "About me"
 And I set the following fields to these values:
 | Introduction text | <p>A James Joyce fan</p> |
 And I press "Save"
 Then I should see "James Joyce"

 # Checking that we can delete more than one block (Bug #1511536)
 # We need to leave and return to the page for this
 And I follow "Display page"
 And I follow "Edit this page"
 When I delete the block "The Sun Also Rises"
 Then I should not see "Robert Cohn"
 When I delete the block "1984"
 Then I should not see "Winston Smith"
 When I delete the block "Ulysses"
 Then I should not see "Buck Mulligan"

 # Checking if we can change page layout
 When I follow "Edit layout"
 And I follow "Create custom layout"
 And I press "Add a row"
 And I wait "1" seconds
 And I select "25 - 50 - 25" from "selectcollayoutrow_2"
 And I press "Add a row"
 And I wait "1" seconds
 And I select "5" from "selectnumcolsrow_3"
 And I select "20 - 20 - 20 - 20 - 20" from "selectcollayoutrow_3"
 And I press "Add a row"
 And I press "Create new layout"
 Then I should see "4 rows"
 And I press "Save"
 Then I should see "Page layout changed"
