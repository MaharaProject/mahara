@javascript @core @core_view
Feature: Added ID's for text blocks
In order to change the settings of a block
As an admin
I need to be able to click on delete and config of a block

Background:
  # Skins need to be enabled
  Given the following site settings are set:
  | field | value |
  | skins | 1 |

Scenario: Clicking ID's (Bug 1428456)
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Portfolios" in "Create" from main menu
 And I click on "Profile page"
 And I scroll to the base of id "viewh1"
 And I click on "Edit"

 # Checking if we can add a block
 When I click on the add block button
 And I click on "Add" in the "Add new block" "Blocks" property
 And I click on blocktype "Text"
 And I set the following fields to these values:
 | Block title | Ulysses |
 | Block content | <p>Stately, plump Buck Mulligan came from the stairhead, bearing a bowl of lather on which a mirror and a razor lay crossed ...</p> |
 And I click on "Save"
 And I wait "1" seconds
 Then I should see "Buck Mulligan"
 When I click on the add block button
 And I click on "Add" in the "Add new block" "Blocks" property
 And I click on blocktype "Text"
 And I set the following fields to these values:
 | Block title | The Sun Also Rises |
 | Block content | <p>Robert Cohn was once middleweight boxing champion of Princeton. Do not think that I am very much impressed by that as a boxing title, but it meant a lot to Cohn...</p> |
 And I click on "Save"
 And I wait "1" seconds
 Then I should see "Robert Cohn"
 When I click on the add block button
 And I click on "Add" in the "Add new block" "Blocks" property
 And I click on blocktype "Text"
 And I set the following fields to these values:
 | Block title | 1984 |
 | Block content | <p>It was a bright cold day in April, and the clocks were striking thirteen. Winston Smith, his chin nuzzled into his breast in an effort to escape the vile wind...</p> |
 And I click on "Save"
 Then I should see "Winston Smith"
 And I scroll to the top

 # Checking if we can edit a block
 When I configure the block "About me"
 And I set the following fields to these values:
 | Introduction text | <p>A James Joyce fan</p> |
 And I click on "Save"
 Then I should see "James Joyce"

 # Checking that we can delete more than one block (Bug #1511536)
 # We need to leave and return to the page for this
 And I display the page
 And I click on "Edit"
 And I wait "1" seconds
 When I delete the block "The Sun Also Rises"
 Then I should not see "Robert Cohn"
 When I delete the block "1984"
 And I wait "1" seconds
 Then I should not see "Winston Smith"
 When I delete the block "Ulysses"
 And I wait "1" seconds
 Then I should not see "Buck Mulligan"

 # Checking we can add a block, make config changes, then delete the block
 # without it causing 'unsaved changes' popup when navigating away
 When I click on the add block button
 And I click on "Add" in the "Add new block" "Blocks" property
 And I click on blocktype "Text"
 And I set the following fields to these values:
 | Block title | Crime and punishment |
 | Block content | <p>On an exceptionally hot evening early in July a young man came out of the garret in which he lodged in S. Place and walked slowly, as though in hesitation, towards K. bridge...</p> |
 And I close the config dialog
 And I scroll to the top

Scenario: Profile and dashboard pages basic settings and skins can't be edited - Bug 1718806
 # Check we can edit basics for dashboard and profile page views
 # but not be able to change title or skin

 Given I log in as "admin" with password "Kupuh1pa!"

 # Profile page
 And I choose "Portfolios" in "Create" from main menu
 And I click on "Profile page"
 And I scroll to the base of id "viewh1"
 And I click on "Edit"
 And I click on "Configure" in the "Toolbar buttons" "Nav" property
 And I should not see "Basics"
 And I should see "Skin"
 And I click on "Save"
 And I should see "Page saved successfully"

 # Dashboard page
 And I choose "Dashboard" from main menu
 And I click on "Edit dashboard"
 And I click on "Configure" in the "Toolbar buttons" "Nav" property
 And I should not see "Basics"
 And I should see "Skin"
 And I click on "Save"
 And I should see "Page saved successfully"
