@javascript @core @core_account
Feature: Mahara users can change their account settings
  As a mahara user
  I need to change my account settings

  Background:
   Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

  Scenario: Change notifications
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Notifications" in "Settings" from user menu
    And I select "Email" from "activity_viewaccess"
    When I press "Save"
    And I should see "Preferences saved"
    And I should not see "Delete account"
