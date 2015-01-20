 @javascript @core @core_portfolio @registered_users
 Feature: Changing "Logged-in users" to "Registered users"
  In order to see "Registered users"
  As an admin
  So I can know that it can only be accessed by registered users

 Scenario: Changing "logged-in users" to "registered users" (Bug 1373095)
  Given I log in as "admin" with password "Password1"
  And I follow "Portfolio"
  When I choose "Shared by me" in "Portfolio"
  Then I should see "Registered users"
