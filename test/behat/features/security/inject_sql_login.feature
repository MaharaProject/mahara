@javascript @core @core_administration
 Feature: Attempt sql inject on login form
  In order to make sure JavaScript could not be passed to data base
  As a visitor
  So I can see that Mahara could not be hacked

 Scenario: SQL injection on the login page
   Given I set the following fields to these values:
   | Username | admin |
   | Password | ' OR 1=1;-- |
   When I click on "Login"
   Then I should see "You have not provided the correct credentials to log in. Please check your username and password are correct."
