@javascript @core
Feature: Random clicking test
   In order to click on different links within Mahara
   As an admin I need to verify all the buttons/links are working
   So I can confirm the links/buttons are clickable for users

Scenario: Clicking randomly around Mahara (Bug: 1426983)
    # Log in as an Admin user
    Given I log in as "admin" with password "Kupuh1pa!"
    # Verifying log in was successful
    And I should see "Admin Account"
    # Checking Content Menu
    And I click on "Show main menu"
    And I choose "Profile" from account menu
    # Checking About me tabs
    And I click on "Contact information"
    And I scroll to the center of id "profileform"
    And I click on "Social media"
    And I click on "General"
    And I click on "About me"
    # Checking Resume submenu and tabs
    And I choose "Résumé" in "Create" from main menu
    And I click on "Education"
    And I click on "Employment"
    And I click on "Achievements"
    And I click on "Goals and skills"
    And I click on "Interests"
    And I click on "Introduction"
    # Checking messages
    And I choose inbox
    And I click on "Inbox" in the "Arrow-bar nav" "Nav" property
    And I click on "Sent"
    And I click on "Compose"
    # Checking Homepage
    And I click on "Show main menu"
    And I click on "Dashboard"
    And I should see "Admin Account"
