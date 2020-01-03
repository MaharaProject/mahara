 @javascript @core @core_portfolio @registered_users
 Feature: Changing "Logged-in users" to "Registered people"
  In order to see "Registered people"
  As an admin
  So I can know that it can only be accessed by registered users

 Scenario: Changing "logged-in users" to "registered people" (Bug 1373095 and Bug 1858143)
  Given I log in as "admin" with password "Kupuh1pa!"
  When I choose "Shared by me" in "Share" from main menu
  Then I should see "Registered people"
