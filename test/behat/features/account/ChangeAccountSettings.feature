@javascript @core @account
Feature: Mahara users can change their account settings
  As a mahara user
  I need to change my account settings

  Background:
    Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |
  Scenario: Change password
    Given I log in as "userA" with password "Password1"
    And I follow "Settings"
    And I fill in "oldpassword" with "Password1"
    And I fill in "password1" with "Passwordnew"
    And I fill in "password2" with "Passwordnew"
    And I press "Save"
    And I wait "1" seconds
    Then I should see "Preferences saved"

  Scenario: Change notifications
    Given I log in as "userA" with password "Password1"
    And I follow "Settings"
    And I follow "Notifications"
    And I select "Email" from "activity_viewaccess"
    And I press "Save"
    And I wait "1" seconds
    Then I should see "Preferences saved"
