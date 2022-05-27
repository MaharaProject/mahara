@javascript @core @core_account @core_login
Feature: Limit password attempts to 5 tries
In order to make sure you can't make more than 5 bad password attempts at a time
As an admin/user
So I can prevent dictionary attacks on my passwords

Background:
  Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
  And I am on homepage
  And I click on "Lost username / password"

Scenario: Too many bad password attempts
  # I should not see any error message on the first 5 attempts
  When I log in as "UserA" with password "wrongpassword"
  # Check for error message for first attempt invalid login
  Then I should see "You have not provided the correct credentials to log in. Please check your username and password are correct."
  And I should see "You are unable to login"
  And I log in as "UserA" with password "wrongpassword"
  And I log in as "UserA" with password "wrongpassword"
  And I log in as "UserA" with password "wrongpassword"
  And I log in as "UserA" with password "wrongpassword"
  Then I should not see "You have exceeded the maximum login attempts."
  And I should see "Login"

  # I've failed 5 times. Now even if I log in with the correct password I'm locked out.
  When I log in as "UserA" with password "Kupuh1pa!"
  Then I should see "You have exceeded the maximum login attempts."
  And I should see "Login"

  # The cron should reset the limit, allowing me to log in again
  When I trigger the cron
  And I log in as "UserA" with password "Kupuh1pa!"
  # I'm logged in!
  Then I should see "Dashboard"
  And I log out

  And I am on homepage
  And I click on "Lost username / password"

Scenario: Asking for a username reminder (Bug 1460911)
  When I fill in "Email address or username" with "UserA@example.org"
  And I click on "Send request"
  Then I should see "You should receive an email shortly with a link that you can use to change the password for your account."

Scenario: Asking for a password reset (Bug 1460911)
  When I fill in "Email address or username" with "UserA"
  And I click on "Send request"
  Then I should see "You should receive an email shortly with a link that you can use to change the password for your account."

Scenario: Trying a username or password that doesn't exist (Bug 1460911)
  When I fill in "Email address or username" with "nosuchuser"
  And I click on "Send request"
  Then I should see "If you do not receive an email, either the details you entered are incorrect or you normally use external authentication to access the site"

Scenario: Student can't change password to anything on suckypasswords list (Bug #844457)
  Given I log in as "UserA" with password "Kupuh1pa!"
  And I choose "Preferences" in "Settings" from account menu
  And I fill in "Current password" with "Kupuh1pa!"
  And I fill in "New password" with "abc123"
  And I fill in "Confirm password" with "abc123"
  And I click on "Save"
  And I should see "Password must be at least 8 characters long"
  And I fill in "Current password" with "Kupuh1pa!"
  And I fill in "New password" with "P@ssw0rd"
  And I fill in "Confirm password" with "P@ssw0rd"
  And I click on "Save"
  Then I should see "Your password is too easy"
  And I log out
