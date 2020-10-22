@javascript @core
Feature: Show Creative commons license conditions in a page block
  In order to for others to know if they can use my page content
  As a user
  I want to display the license for my page in a page block

Background:
  Given the following "users" exist:
  | username | password | email | firstname | lastname | institution | authname | role |
  | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

  And the following "pages" exist:
  | title | description | ownertype | ownername |
  | Page UserA_01 | Page 01 | user | UserA |

Scenario:
  Given I log in as "UserA" with password "Kupuh1pa!"
  And  I choose "Pages and collections" in "Create" from main menu
  And I click on "Edit" in "Page UserA_01" card menu
  When I follow "Drag to add a new block" in the "blocktype sidebar" "Views" property
  And I press "Add"
  And I click on blocktype "Creative Commons license"
  And I set the field "Block title" to "Creative Commons license"
  # Note that #freecultureseal cannot be tested for as it exists in the page even if not seen
  And I enable the switch "Allow commercial uses of your work?"
  And I select "Yes, as long as others share alike" from "Allow modifications of your work?"
  And I select "4.0" from "License version"
  And I select "Automatically retract" from "Retractable"
  And I press "Save"
  And I display the page
  # Check the block is automatically retracted
  Then I should not see "Permissions beyond the scope of this license may be available"
  # Open the block and check it has the correct text
  And I expand "Creative Commons license" node
  Then I should see "Page UserA_01 by Angela User is licensed under a Creative Commons Attribution-Share Alike 4.0 Unported license."
  When I follow "Edit"
  # change to noncommercial and check message
  And I configure the block "Creative Commons license"
  And I disable the switch "Allow commercial uses of your work?"
  And I select "Yes" from "Allow modifications of your work?"
  And I press "Save"
  And I display the page
  And I expand "Creative Commons license" node
  And I should see "Page UserA_01 by Angela User is licensed under a Creative Commons Attribution-Noncommercial 4.0 Unported license."
