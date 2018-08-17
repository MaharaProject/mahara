@javascript @core @core_administration
Feature: Confirm update to the privacy statement template on homepage
and dashboard to encourage institutions to write their own text.

Scenario: Admin user log in and confirm Legal templates are correct for
    1. Site privacy Statement
    2. Site terms and conditions
    Given I log in as "admin" with password "Kupuh1pa!"
    When  I choose "Legal" in "Settings" from user menu
    # verify user on correct page and correct intro text for page
    Then I should see "Displayed are the current privacy statements and terms and conditions."
    # verify Site privacy Statement section contains correct text
    And I should see "Add your privacy statement for the site in \"Administration menu\" → \"Configure site\""
    # verify first Legal link is displayed and links to correct page
    And I click on the "First Legal" property
    And I should see "Edit the privacy statement for the entire site. The version you edited last becomes the current privacy statement automatically."
    And I move backward one page
    # verify Site terms and conditions section contains correct text
    And I should see "Add your terms and conditions for the site in \"Administration menu\" → \"Configure site\""
    And I click on the "Second Legal" property
    # verify second Legal link is displayed and links to correct page
    Then I should see "Edit the terms and conditions for the entire site. The version you edited last becomes the current terms and conditions automatically."
