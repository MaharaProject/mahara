 @javascript @core @core_administration
 Feature: Alphabetical ordering of notification settings
  In order to access notification settings more easily
  As an admin
  So I can see the notification settings in alphabetical order.

 Scenario: Admin logs in and checks notification settings (Bug 1388682)
  Given I log in as "admin" with password "Kupuhipa1"
  When I click on "Administration"
  And I choose "Site options" in "Configure site"
  And I click on "Notification settings"
  Then I should see "Contact us"
  And I should see "Feedback"
  And I should see "Group message"
  And I should see "Institution message"
  And I should see "Virus flag release"
  And I should see "Watchlist"
  And "Feedback" "text" should appear before "Group message" "text"
  And "Group message" "text" should appear before "Institution message" "text"
  And "Virus flag release" "text" should appear before "Watchlist" "text"
