@javascript @core
Feature: Embedded media block
  As a person
  I want to add an embedded media block to my page
  So I can include audio and video content

Background:
  Given the following "users" exist:
  | username | password | email | firstname | lastname | institution | authname | role |
  | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

  And the following "pages" exist:
  | title | description | ownertype | ownername |
  | Page UserA_01 | Page 01 | user | UserA |

Scenario: Embed and play mp4
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I choose "Pages and collections" in "Create" from main menu
  And I click on "Edit" in "Page UserA_01" card menu
  When I click on the add block button
  And I press "Add"
  And I click on blocktype "Embedded media"
  And I press "Media"
  And I attach the file "testvid3.mp4" to "File"
  And I press "Save"
  And I display the page
  And I press "Play Video"
  # check remaining time is displayed
  And I wait "3" seconds
  And I should see "0:00" in the "Videojs time remaining" "Misc" property

Scenario: Embed and play mp3
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I choose "Pages and collections" in "Create" from main menu
  And I click on "Edit" in "Page UserA_01" card menu
  When I click on the add block button
  And I press "Add"
  And I click on blocktype "Embedded media"
  And I press "Media"
  And I set the field "Block title" to "mahara.mp3"
  And I attach the file "mahara.mp3" to "File"
  And I press "Save"
  And I display the page
  And I should see "mahara.mp3"
  And I press "Play Video"
  # check pause and play buttons work
  And I press "Pause"
  And I press "Play"

Scenario: Change settings and embed 3gp
  #change settings to allow 3gp
  Given I log in as "admin" with password "Kupuh1pa!"
  And I go to the "blocktype" plugin "file/internalmedia" configuration
  And I enable the switch "3GPP media file"
  And I press "Save"
  And I log out
  # Log in as author and check 3gp is uploadable
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I choose "Pages and collections" in "Create" from main menu
  And I click on "Edit" in "Page UserA_01" card menu
  When I click on the add block button
  And I press "Add"
  And I click on blocktype "Embedded media"
  And I press "Media"
  And I attach the file "testvid1.3gp" to "File"
  And I press "Save"
  And I display the page
  # 3gp has no compatible add-ons to play in browser - still true in FF but not in Chrome
  # And I should see "No compatible source was found for this media"

Scenario: Embed unsupported file type
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I choose "Pages and collections" in "Create" from main menu
  And I click on "Edit" in "Page UserA_01" card menu
  When I click on the add block button
  And I press "Add"
  And I click on blocktype "Embedded media"
  And I press "Media"
  # mkv is not supported
  And I attach the file "testvid2.mkv" to "File"
  Then I should see "The file you uploaded was not the correct type for this block."
