@javascript @core @core_login
Feature: Suckypasswords Test increase of array size
 In order to limit the crappy passwords people try to put in
 As an admin
 So I can make sure that my users/myself have decent passwords

Background:
 Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | Supercool | Kupuh1pa! | Supercool@example.org | Super | Cool | mahara | internal | member |

Scenario: Admin can't change password to anything not fitting password policy
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Preferences" in "Settings" from account menu
 And I fill in "Current password" with "Kupuh1pa!"
 And I fill in "New password" with "abc123"
 And I fill in "Confirm password" with "abc123"
 And I press "Save"
 And I should see "Password must be at least 8 characters long."
 And I fill in "Current password" with "Kupuh1pa!"
 And I fill in "New password" with "administrator"
 And I fill in "Confirm password" with "administrator"
 And I press "Save"
 And I should see "Password must contain upper and lowercase letters, numbers, symbols."
 And I fill in "Current password" with "Kupuh1pa!"
 And I fill in "New password" with "Admin@123"
 And I fill in "Confirm password" with "Admin@123"
 And I press "Save"
 And I should see "Your password is too easy"
 And I log out

Scenario: Student can't change password to anything not fitting password policy
 Given I log in as "Supercool" with password "Kupuh1pa!"
 And I choose "Preferences" in "Settings" from account menu
 And I fill in "Current password" with "Kupuh1pa!"
 And I fill in "New password" with "fastdog"
 And I fill in "Confirm password" with "fastdog"
 And I press "Save"
 And I should see "Password must be at least 8 characters long."
 And I fill in "Current password" with "Kupuh1pa!"
 And I fill in "New password" with "supercool"
 And I fill in "Confirm password" with "supercool"
 And I press "Save"
 And I should see "Passwords are case sensitive and must be different from your username."
 And I fill in "Current password" with "Kupuh1pa!"
 And I fill in "New password" with "administrator"
 And I fill in "Confirm password" with "administrator"
 And I press "Save"
 And I should see "Password must contain upper and lowercase letters, numbers, symbols."
 And I fill in "Current password" with "Kupuh1pa!"
 And I fill in "New password" with "P@ssw0rd"
 And I fill in "Confirm password" with "P@ssw0rd"
 And I press "Save"
 And I should see "Your password is too easy"
 And I log out
