@javascript @core @core_administration

Feature: Check footer settings and headings correctly displayed
As an admin I want to know
Given I set the footer headings to be visible
When a user logs in, they see the correct headings.

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

  Scenario: User can see correct headings in footer by default
  #log in as a normal user
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I click on "Legal" in the "Footer" property
  And I should see "Displayed are the current privacy statements and terms and conditions."
  And I am on homepage
  And I click on "About"
  And I am on homepage
  And I click on "Contact us"
  And I am on homepage
  # test click is possible - opens a new window, which we're not checking at this stage
  And I click on "Help"
  And I switch to the main window

  Scenario: Admin changes settings which are visible to user
  #log in as admin account
  Given I log in as "admin" with password "Kupuh1pa!"
  And I choose "Menus" in "Configure site" from administration menu
  And I disable the following switches:
     | Legal |
     | About |
     | Contact us |
     | External manual |
  And I press "Save changes"
  And I log out
  #Go back to homepage as UserA and check if settings saved
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I should not see "Legal"
  And I should not see "About"
  And I should not see "Contact us"
  And I should not see "External manual"
