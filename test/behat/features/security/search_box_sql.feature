@javascript @core @core_administration
Feature: Check search is free from sql vulnerability
In order to check the main search field is secure
As an admin I want to inject sql into the search field
So I can check the field doesn't error


Scenario: sql injection attempt on search field
  Given I log in as "admin" with password "Kupuh1pa!"
  When I set the following fields to these values:
   | Search for people | 'or 1=1;-- |
  And I press "Go"
  Then I should see "No search results found"
