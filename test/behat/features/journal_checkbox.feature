@javascript @core @core_profile_completion @journal
Feature: Checking the absence of the journal checkbox
 In order not to see the Journal checkbox
 As an admin
 So I can complete my profile without needing to tick it

Scenario: Checking the absence of the journal checkbox (Bug 1408438)
 Given I log in as "admin" with password "Password1"
 And I follow "Administration"
 And I follow "Institutions"
 When I choose "Profile completion" in "Institutions"
 And I follow "Journals"
 Then "Journal" "checkbox" should not exist

