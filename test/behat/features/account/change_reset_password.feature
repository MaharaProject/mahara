@javascript @core @core_administration
Feature: Changing password through settings
   In order to change the admin password
   As an admin I log in and go to settings
   So I can set up the admin's new password

Scenario: Changing admin password through settings option (Selenium)
    # Log in as admin
    Given I log in as "admin" with password "Password1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Navigating to settings
    And I follow "Settings"
    And I fill in the following:
    | Current password   | Password1 |
    | New password | mahara2 |
    | Confirm password | mahara2 |
    And I press "Save"
    # Verifying the preferences saved
    And I should see "Preferences saved"
    And I follow "Logout"
    # Logging in with the new password
    When I log in as "admin" with password "mahara2"
    # Verifying that the log in was a success
    And I should see "Welcome"
    # Navigating to the settings
    And I follow "Settings"
    And I fill in the following:
    | Current password   | mahara2 |
    | New password | Password1 |
    | Confirm password | Password1 |
    And I press "Save"
    And I follow "Logout"
    # Logging in as admin with the new passsword
    Then I log in as "admin" with password "Password1"
    # Verifying it worked
    And I should see "Welcome"
