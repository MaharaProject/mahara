@javascript @core
Feature: Random clicking test
   In order to click on different links within Mahara
   As an admin I need to verify all the buttons/links are working
   So I can confirm the links/buttons are clickable for users

Scenario: Clicking randomly around Mahara (Bug: 1426983)
    # Log in as an Admin user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Checking Content Menu
    When I follow "Content"
    # Checking About me tabs
    And I follow "Contact information"
    And I follow "Social media"
    And I follow "General"
    And I follow "About me"
    # Checking Profile picture submenu
    And I choose "Profile pictures" in "Content"
    # Checking Files Submenu
    And I choose "Files" in "Content"
    # Checking Journal submenu
    And I choose "Journals" in "Content"
    # Checking Resume submenu and tabs
    And I choose "Résumé" in "Content"
    And I follow "Education and employment"
    And I follow "Achievements"
    And I follow "Goals and skills"
    And I follow "Interests"
    And I follow "Introduction"
    # Checking Plans submenu
    And I choose "Plans" in "Content"
    # Checking Notes submenu
    And I choose "Notes" in "Content"
    # Checking Portfolio Menu and submenu
    And I choose "Collections" in "Portfolio"
    And I choose "Shared by me" in "Portfolio"
    And I choose "Shared with me" in "Portfolio"
    And I choose "Export" in "Portfolio"
    And I choose "Import" in "Portfolio"
    # Checking Groups Menus and submenu
    And I choose "Find groups" in "Groups"
    And I choose "My friends" in "Groups"
    And I choose "Find friends" in "Groups"
    And I choose "Institution membership" in "Groups"
    And I choose "Topics" in "Groups"
    # Checking messages
    And I follow "mail"
    And I follow "Inbox"
    And I follow "Sent"
    And I follow "Compose"
    # Checking Homepage
    And I follow "Dashboard"
    # Checking Administration
    And I follow "Administration"
    # Checking Admin home Menu and submenu
    And I choose "Register" in "Admin home"
    And I choose "Site statistics" in "Admin home"
    And I follow "Logins"
    And I choose "Overview" in "Admin home"
    # Checking Configure site Menu and submenu
    And I choose "Site options" in "Configure site"
    And I choose "Static pages" in "Configure site"
    And I choose "Menus" in "Configure site"
    And I choose "Networking" in "Configure site"
    And I choose "Licenses" in "Configure site"
    And I choose "Pages" in "Configure site"
    And I choose "Collections" in "Configure site"
    And I choose "Share" in "Configure site"
    And I choose "Files" in "Configure site"
    And I choose "Cookie Consent" in "Configure site"
    # Checking Users Menu and submenu
    And I choose "User search" in "Users"
    And I choose "Suspended and expired users" in "Users"
    And I choose "Site staff" in "Users"
    And I choose "Site administrators" in "Users"
    And I choose "Export queue" in "Users"
    And I choose "Add user" in "Users"
    And I choose "Add users by CSV" in "Users"
    # Checking Groups
    And I choose "Group categories" in "Groups (Administer groups)"
    And I choose "Archived submissions" in "Groups (Administer groups)"
    And I choose "Add groups by CSV" in "Groups (Administer groups)"
    And I choose "Update group members by CSV" in "Groups (Administer groups)"
    And I choose "Administer groups" in "Groups (Administer groups)"
    # Checking Institutions Menu and submenus
    And I choose "Static pages" in "Institutions"
    And I choose "Members" in "Institutions"
    And I choose "Staff" in "Institutions"
    And I choose "Administrators" in "Institutions"
    And I choose "Admin notifications" in "Institutions"
    And I choose "Profile completion" in "Institutions"
    And I choose "Pages" in "Institutions"
    And I choose "Collections" in "Institutions"
    And I choose "Share" in "Institutions"
    And I choose "Files" in "Institutions"
    And I choose "Statistics" in "Institutions"
    And I choose "Pending registrations" in "Institutions"
    # Checking Extensions Menu and submenu
    And I choose "Plugin administration" in "Extensions"
    And I choose "HTML filters" in "Extensions"
    And I choose "Allowed iframe sources" in "Extensions"
    And I choose "Clean URLs" in "Extensions"
    And I choose "Web services" in "Extensions"
    # The test should be completed once if Return to site works successfully
    And I follow "Return to site"
    And I should see "Admin User"