@javascript @core @core_administration

Feature: The admin homepage
Test the forms on the admin homepage
As an admin
So I can make sure I can close/open site or clear cache

Scenario: Closing the site
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Overview" from administration menu
 # Verifying I'm on the right page
 And I should see "Site registration"
 And I scroll to the base of id "close_site"
 And I click on "Close"
 And I should see "Reopen site"
 And I scroll to the base of id "close_site"
 And I click on "Open"
 And I should see "Close site"
 # Checking that the caches can be emptied
 And I scroll to the base of id "clear_caches"
 And I click on "Clear caches"
 And I should see "All caches were cleared"
