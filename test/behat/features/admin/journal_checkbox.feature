@javascript @core @core_administration
Feature: Checking the absence of the journal checkbox in Profile completion settings
  In order not to take into account of Journal in the profile completion bar
  As an admin
  So I should not see the checkbox "Journal" in the Profile completion settings

Scenario: Checking the absence of the journal checkbox (Bug 1408438)
  Given I log in as "admin" with password "Kupuhipa1"
  And I follow "Administration"
  And I choose "Profile completion" in "Institutions"
  And I click on "Journals"
  # Checking the checkbox has been removed
  Then "tr#progressbarform_progressbaritem_blog_blog_container" "css_element" should not exists

