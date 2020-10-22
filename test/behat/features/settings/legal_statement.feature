@javascript @core @core_administration
Feature: Confirm update to the privacy statement template on homepage
and dashboard to encourage institutions to write their own text.

Scenario: Admin user log in and confirm Legal templates are correct for
    1. Site privacy Statement
    2. Site terms and conditions
    Given I log in as "admin" with password "Kupuh1pa!"
    When  I choose "Legal" in "Settings" from account menu
    # verify user on correct page and correct intro text for page
    Then I should see "Displayed are the current privacy statements and terms and conditions."
    # verify Site privacy Statement section contains correct text
    And I should see "Add your privacy statement for the site in \"Administration menu\" → \"Configure site\""
    # verify first Legal link is displayed and links to correct page
    And I click on the "First Legal" "Legal" property
    And I should see "Edit the privacy statement for the entire site. The version you edited last becomes the current privacy statement automatically."
    And I move backward one page
    # verify Site terms and conditions section contains correct text
    And I should see "Add your terms and conditions for the site in \"Administration menu\" → \"Configure site\""
    And I move forward one page
    # Privacy statements in logged out page should show latest version (Bug 1797812)
    And I click on the "Privacy statement Edit icon" "Legal" property
    And I fill in "Version" with "V2.0"
    And I fill in "V 2.0 privacy statement for the site " in first editor
    And I press "Save changes"
    Then I should see "V2.0" in the "Admin Account" row
    And I should see "1.0" in the "System User" row
    And I wait "1" seconds
    And I should see "Page saved"
    When I click on "Terms and conditions"
    # Terms and conditions statements in logged out page should show latest version (Bug 1797812)
    And I click on the "Terms and conditions Edit icon" "Legal" property
    And I fill in "Version" with "V2.0"
    And I fill in "V 2.0 terms and conditions for the site" in first editor
    And I press "Save changes"
    Then I should see "V2.0" in the "Admin Account" row
    And I should see "1.0" in the "System User" row
    And I should see "Page saved"
    When I click on "Legal" in the "Footer" "Footer" property
    And I should see "V 2.0 terms and conditions for the site"
    Then I should see "V 2.0 privacy statement for the site"
