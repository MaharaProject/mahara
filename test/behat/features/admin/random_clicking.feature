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
    And I click on "Show Menu"
    When I follow "Content"
    # Checking About me tabs
    And I follow "Contact information"
    And I follow "Social media"
    And I follow "General"
    And I follow "About me"
    # Checking Profile picture submenu
    And I choose "Profile pictures" in "Content" from main menu
    # Checking Files Submenu
    And I choose "Files" in "Content" from main menu
    # Checking Journal submenu
    And I choose "Journals" in "Content" from main menu
    # Checking Resume submenu and tabs
    And I choose "Résumé" in "Content" from main menu
    And I follow "Education and employment"
    And I follow "Achievements"
    And I follow "Goals and skills"
    And I follow "Interests"
    And I follow "Introduction"
    # Checking Plans submenu
    And I choose "Plans" in "Content" from main menu
    # Checking Notes submenu
    And I choose "Notes" in "Content" from main menu
    # Checking Portfolio Menu and submenu
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I choose "Shared by me" in "Portfolio" from main menu
    And I choose "Shared with me" in "Portfolio" from main menu
    And I choose "Export" in "Portfolio" from main menu
    And I choose "Import" in "Portfolio" from main menu
    # Checking Groups Menus and submenu
    And I choose "Find groups" in "Groups" from main menu
    And I choose "My friends" in "Groups" from main menu
    And I choose "Find people" in "Groups" from main menu
    And I choose "Institution membership" in "Groups" from main menu
    And I choose "Topics" in "Groups" from main menu
    # Checking messages
    # And I click on "Show User Menu" # problem with chrome driver clicking on user icon instead
    # And I follow "mail"
    And I choose "mail" from user menu by id
    And I follow "Inbox" in the ".arrow-bar" "css_element"
    And I follow "Sent"
    And I follow "Compose"
    # Checking Homepage
    And I click on "Show Menu"
    And I follow "Dashboard"
    # Checking Administration
    # Checking Admin home Menu and submenu
    And I choose "Register" in "Admin home" from administration menu
    And I choose "Site statistics" in "Admin home" from administration menu
    And I follow "Logins"
    And I choose "Overview" in "Admin home" from administration menu
    # Checking Configure site Menu and submenu
    And I choose "Site options" in "Configure site" from administration menu
    And I choose "Static pages" in "Configure site" from administration menu
    And I choose "Menus" in "Configure site" from administration menu
    And I choose "Networking" in "Configure site" from administration menu
    And I choose "Licenses" in "Configure site" from administration menu
    And I choose "Pages and collections" in "Configure site" from administration menu
    And I choose "Share" in "Configure site" from administration menu
    And I choose "Files" in "Configure site" from administration menu
    And I choose "Cookie Consent" in "Configure site" from administration menu
    # Checking Users Menu and submenu
    And I choose "User search" in "Users" from administration menu
    And I choose "Suspended and expired users" in "Users" from administration menu
    And I choose "Site staff" in "Users" from administration menu
    And I choose "Site administrators" in "Users" from administration menu
    And I choose "Export queue" in "Users" from administration menu
    And I choose "Add user" in "Users" from administration menu
    And I choose "Add users by CSV" in "Users" from administration menu
    # Checking Groups
    And I choose "Group categories" in "Groups" from administration menu
    And I choose "Archived submissions" in "Groups" from administration menu
    And I choose "Add groups by CSV" in "Groups" from administration menu
    And I choose "Update group members by CSV" in "Groups" from administration menu
    And I choose "Administer groups" in "Groups" from administration menu
    # Checking Institutions Menu and submenus
    And I choose "Static pages" in "Institutions" from administration menu
    And I choose "Members" in "Institutions" from administration menu
    And I choose "Staff" in "Institutions" from administration menu
    And I choose "Administrators" in "Institutions" from administration menu
    And I choose "Admin notifications" in "Institutions" from administration menu
    And I choose "Profile completion" in "Institutions" from administration menu
    And I choose "Pages and collections" in "Institutions" from administration menu
    And I choose "Share" in "Institutions" from administration menu
    And I choose "Files" in "Institutions" from administration menu
    And I choose "Statistics" in "Institutions" from administration menu
    And I choose "Pending registrations" in "Institutions" from administration menu
    # Checking Extensions Menu and submenu
    And I choose "Plugin administration" in "Extensions" from administration menu
    And I choose "HTML filters" in "Extensions" from administration menu
    And I choose "Allowed iframe sources" in "Extensions" from administration menu
    And I choose "Clean URLs" in "Extensions" from administration menu
    # Checking Web services Menu and submenu
    And I choose "Configuration" in "Web services" from administration menu
    And I choose "External apps" in "Web services" from administration menu
    And I choose "Logs" in "Web services" from administration menu
    And I choose "Test client" in "Web services" from administration menu
    And I choose "Application connections" in "Web services" from administration menu
    And I choose "Connection manager" in "Web services" from administration menu
    # The test should be completed once if Return to site works successfully
    And I click on "Show Menu"
    And I follow "Dashboard"
    And I should see "Admin User"
