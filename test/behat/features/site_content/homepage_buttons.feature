@javascript @core @core_administration
Feature: Big buttons on home page
In order to navigate the site correctly
As a user
The big homepage buttons should navigate to their correct places

Scenario: Checking navigation of the big homepage buttons
 Given I log in as "admin" with password "Kupuh1pa!"
 And I click on "Develop your portfolio"
 Then I should see "Pages and collections" in the "h1 heading" property
 And I am on homepage
 And I click on "Control your privacy"
 Then I should see "Share" in the "h1 heading" property
 And I am on homepage
 And I click on "Find people and join groups"
 Then I should see "Find groups" in the "h1 heading" property
 And I am on homepage
 And I click on "Hide information box"
 Then I should see "You have hidden the information box."
 And I should not see "Find people and join groups"
 And I choose "Settings" in "Settings" from user menu
 And I enable the switch "Dashboard information"
 And I press "Save"
 And I am on homepage
 Then I should see "Find people and join groups"
