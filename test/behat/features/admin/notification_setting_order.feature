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
  Then I should see "Contact us" in the "tr#siteoptions_activity_contactus_container" element
  And I should see "Feedback" in the "tr#siteoptions_activity_feedback_artefact_comment_container" element
  And I should see "Group message" in the "tr#siteoptions_activity_groupmessage_container" element
  And I should see "Institution message" in the "tr#siteoptions_activity_institutionmessage_container" element
  And I should see "Virus flag release" in the "tr#siteoptions_activity_virusrelease_container" element
  And I should see "Watchlist" in the "tr#siteoptions_activity_watchlist_container" element
  And "tr#siteoptions_activity_feedback_artefact_comment_container" "css_element" should appear before "tr#siteoptions_activity_groupmessage_container" "css_element"
  And "tr#siteoptions_activity_groupmessage_container" "css_element" should appear before "tr#siteoptions_activity_institutionmessage_container" "css_element"
  And "tr#siteoptions_activity_virusrelease_container" "css_element" should appear before "tr#siteoptions_activity_watchlist_container" "css_element"
