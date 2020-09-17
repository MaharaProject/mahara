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
    And I follow "Contact information"
    And I scroll to the center of id "profileform"
    And I follow "Social media"
    And I follow "General"
    And I follow "About me"
    # Checking Resume submenu and tabs
    And I choose "Résumé" in "Create" from main menu
    And I follow "Education and employment"
    And I follow "Achievements"
    And I follow "Goals and skills"
    And I follow "Interests"
    And I follow "Introduction"
    # Checking messages
    And I choose inbox
    And I follow "Inbox" in the "Arrow-bar nav" property
    And I follow "Sent"
    And I follow "Compose"
    # Checking Homepage
    And I click on "Show main menu"
    And I follow "Dashboard"
    And I should see "Admin Account"
