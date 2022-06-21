@javascript @core @core_account
Feature: Mahara people can change their account settings
  As a mahara person
  I need to change my account settings
    1) person can change account notifications settings
    --- a. Person selects "Email" from "New page access"
    --- b. Person selects "None" from "Comment"
    --- c. Person cannot select "None" from "Message from other people"
    --- d. Person cannot select "None" from "System message"

    2) Person can change account preferences settings
    --- a. Person changes "Password" functionality
    --- b. Person changes "Username"
    --- c. Person changes "Contact control" to "Nobody may add me as a contact"

  Background:
   Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

  Scenario: Person changes notifications settings
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Notifications" in "Settings" from account menu
    And I select "Email" from "activity_viewaccess"
    And I select "Inbox" from "Comment"
    And I select "None" from "Feedback on annotations"
    And I select "Inbox" from "Group message"
    And I select "Email digest" from "Institution message"
    And I select "Inbox" from "Message from other people"
    And "None" "option" in the "Message from other people" "Account" property should not be visible
    And "None" "option" in the "System message" "Account" property should not be visible
    And I select "Email" from "New forum post"
    And I select "Email digest" from "Peer assessment"
    And I select "Inbox" from "System message"
    And I select "Inbox" from "Wall post"
    And I select "Inbox" from "Watchlist"
    When I press "Save"
    And I should see "Preferences saved"
    And I should not see "Delete account"

Scenario: Person changes preference settings
    Given I log in as "UserA" with password "Kupuh1pa!"
    When I choose "Preferences" in "Settings" from account menu
    Then I should see "Preferences" in the "Page heading" "Common" property
    And I should see "New password" in the "Preferences heading 1" "Account" property
    When I fill in "Current password" with "Kupuh1pa!"
    And I fill in "New password" with "Password123!"
    And I fill in "Confirm password" with "Password123!"
    And I press "Save"
    Then I should see "Preferences saved"
    And I should see "Change username" in the "Preferences heading 2" "Account" property
    When I fill in "New username" with "UserAA"
    And I press "Save"
    Then I should see "There was an error with submitting this form. Please check the marked fields and try again."
    And I fill in "Password123!" for "accountprefs_oldpasswordchangeuser"
    And I press "Save"
    And I should see "Preferences saved"
    And a "Friends control radio" "Account" property should exist
    And I press "Save"
    Then I should see "Preferences saved"
