@javascript @core
Feature: Profile block displays correctly; add/delete profile picture,
As a user
I want to add a profile block to my page
And ensure it displays my name, email and profile picture correctly
Additionally, this is a test for adding/deleting a profile picture

Background:
Given the following "users" exist:
  | username | password | email | firstname | lastname | institution | authname | role |
  | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

And the following "pages" exist:
  | title | description| ownertype | ownername |
  | Page UserA_01 | Page 01 | user | UserA |

Scenario: Profile block displays my information correctly (Bug 1677929)
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I choose "Profile pictures" from user menu
  # Add profile image
  And I attach the file "Image2.png" to "Profile picture"
  And I set the field "Image title" to "Angela"
  And I press "Upload"
  And I select the radio "Set default for \"Angela\""
  And I press "Set default"
  Then I should see "Default profile picture set successfully"
  # Add profile block to page
  And I choose "Pages and collections" in "Create" from main menu
  And I follow "Page UserA_01"
  And I follow "Edit"
  When I follow "Add a new block" in the "blocktype sidebar" property
  And I press "Add"
  And I click on "Show more"
  And I click on "Show more"
  And I click on "Show more"
  And I click on "Profile information" in the "Content types" property
  And I set the field "Block title" to "Profile information"
  # Choose information to display
  And I check "First name"
  And I check "Last name"
  And I set the field "Angela" to "1"
  And I set the field "UserA@example.org" to "1"
  And I press "Save"
  # Confirm information displays on page
  And I display the page
  Then I should see "First name: Angela"
  And I should see "Last name: User"
  And I should see "Email address: UserA@example.org"
  # This checks an image is displayed, not the specific image
  And I should see images within the block "Profile information"
  # Delete the profile picture
  And I choose "Profile pictures" from user menu
  And I select the radio "Mark \"Angela\" for deletion"
  And I press "Delete"
  Then I should see "Profile picture deleted"
