@javascript @core @core_view @core_portfolio @menu
Feature: Checking the correct menu items are available for each person
In order to make sure the correct menu items are available
So people can access features in Mahara that they are allowed to interact with.

Background:
Given the following "institutions" exist:
     | name | displayname | registerallowed | registerconfirm |
     | instone | Institution One | ON | OFF |

Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org  | Bob | Staff | mahara | internal |  staff  |
     | UserC | Kupuh1pa! | UserC@example.org | Cecilia | Staff | instone | internal | staff  |
     | AdminA | Kupuh1pa! | AdminA@example.org  | Angela | Admin | instone | internal | admin  |

Scenario: Checking menu items are available as a student (Bug 1467368)
 Given I log in as "UserA" with password "Kupuh1pa!"
 # Checking the main menu navigation headings
 When I click on "Show main menu"
 And I wait "1" seconds
 And I follow "Dashboard"
 Then I should not see "Administration" in the "Administration menu" "Nav" property
 And I should not see "Site information" in the "Administration menu" "Nav" property
 And I click on "Show main menu"
 And I should see "Create" in the "Main menu" "Nav" property
 And I should see "Engage" in the "Main menu" "Nav" property
 And I should see "Manage" in the "Main menu" "Nav" property
 # Checking the sub navigation in Create
 When I follow "Create"
 Then I should see "Pages and collections" in the "Create sub-menu" "Nav" property
 And I should see "Files" in the "Create sub-menu" "Nav" property
 And I should see "Journals" in the "Create sub-menu" "Nav" property
 And I should see "Résumé" in the "Create sub-menu" "Nav" property
 And I should see "Plans" in the "Create sub-menu" "Nav" property
 And I should see "Notes" in the "Create sub-menu" "Nav" property
 # Checking the sub navigation in Share
 When I press "Share"
 And I should see "Shared by me" in the "Share sub-menu" "Nav" property
 And I should see "Shared with me" in the "Share sub-menu" "Nav" property
 # Checking the sub navigation in Engage
 When I follow "Engage"
 Then I should see "Groups" in the "Engage sub-menu" "Nav" property
 And I should see "People" in the "Engage sub-menu" "Nav" property
 And I should see "Discussion topics" in the "Engage sub-menu" "Nav" property

 # Checking the sub navigation in Manage
 When I follow "Manage"
 And I should see "Export" in the "Manage sub-menu" "Nav" property
 And I should see "Import" in the "Manage sub-menu" "Nav" property

Scenario: Checking menu items are available as site staff (Bug 1467368)
 Given I log in as "UserB" with password "Kupuh1pa!"
 Then I should not see "Administration" in the "Main menu" "Nav" property
 # The one major difference a site staff has is site info link that leads to other links
 And I click on "Show administration menu"
 And I wait "1" seconds
 And I follow "People search"
 And I click on "Show administration menu"
 Then I follow "Reports"

Scenario: Checking menu items are available as site admin (Bug 1467368)
 Given I log in as "admin" with password "Kupuh1pa!"
 # Checking the sub navigation in Administration
 And I click on "Show administration menu"
 And I wait "1" seconds
 Then I should see "Admin home" in the "Administration menu" "Nav" property
 And I should see "Configure site" in the "Administration menu" "Nav" property
 And I should see "People" in the "Administration menu" "Nav" property
 And I should see "Groups" in the "Administration menu" "Nav" property
 And I should see "Institutions" in the "Administration menu" "Nav" property
 And I should see "Extensions" in the "Administration menu" "Nav" property
 And I should see "Web services" in the "Administration menu" "Nav" property
 # Checking the sub navigation in Admin home
 When I press "Show menu for Admin home"
 Then I should see "Overview" in the "Admin home sub-menu" "Nav" property
 And I should see "Register" in the "Admin home sub-menu" "Nav" property
 # Checking the sub navigation in Configure site
 When I press "Show menu for Configure site"
 Then I should see "Site options" in the "Configure site sub-menu" "Nav" property
 And I should see "Static pages" in the "Configure site sub-menu" "Nav" property
 And I should see "Menus" in the "Configure site sub-menu" "Nav" property
 And I should see "Legal" in the "Configure site sub-menu" "Nav" property
 And I should see "Networking" in the "Configure site sub-menu" "Nav" property
 And I should see "Licenses" in the "Configure site sub-menu" "Nav" property
 And I should see "Pages and collections" in the "Configure site sub-menu" "Nav" property
 And I should see "Journals" in the "Configure site sub-menu" "Nav" property
 And I should see "Share" in the "Configure site sub-menu" "Nav" property
 And I scroll to the base of id "navadmin"
 And I should see "Files" in the "Configure site sub-menu" "Nav" property
 And I should see "Cookie Consent" in the "Configure site sub-menu" "Nav" property
 # Checking the sub navigation in People
 When I press "Show menu for People"
 Then I should see "People search" in the "Users sub-menu" "Nav" property
 And I should see "Suspended and expired accounts" in the "Users sub-menu" "Nav" property
 And I should see "Site staff" in the "Users sub-menu" "Nav" property
 And I should see "Site administrators" in the "Users sub-menu" "Nav" property
 And I should see "Export queue" in the "Users sub-menu" "Nav" property
 And I should see "Add a person" in the "Users sub-menu" "Nav" property
 And I should see "Add people by CSV" in the "Users sub-menu" "Nav" property
 # Checking the sub navigation in Groups
 When I press "Show menu for Groups" in the "Administration menu" "Nav" property
 Then I should see "Administer groups" in the "Admin Groups sub-menu" "Nav" property
 And I should see "Group categories" in the "Admin Groups sub-menu" "Nav" property
 And I should see "Default group settings" in the "Admin Groups sub-menu" "Nav" property
 And I should see "Archived submissions" in the "Admin Groups sub-menu" "Nav" property
 And I should see "Add groups by CSV" in the "Admin Groups sub-menu" "Nav" property
 And I should see "Update group members by CSV" in the "Admin Groups sub-menu" "Nav" property
 # Checking the sub administration in Institutions
 When I press "Show menu for Institutions"
 Then I should see "Settings" in the "Institutions sub-menu" "Nav" property
 And I should see "Static pages" in the "Institutions sub-menu" "Nav" property
 And I should see "Legal" in the "Institutions sub-menu" "Nav" property
 And I should see "Members" in the "Institutions sub-menu" "Nav" property
 And I should see "Staff" in the "Institutions sub-menu" "Nav" property
 And I should see "Administrators" in the "Institutions sub-menu" "Nav" property
 And I should see "Admin notifications" in the "Institutions sub-menu" "Nav" property
 And I should see "Profile completion" in the "Institutions sub-menu" "Nav" property
 And I should see "Pages and collections" in the "Institutions sub-menu" "Nav" property
 And I should see "Journals" in the "Institutions sub-menu" "Nav" property
 And I scroll to the base of id "navadmin"
 And I should see "Share" in the "Institutions sub-menu" "Nav" property
 And I should see "Files" in the "Institutions sub-menu" "Nav" property
 And I should see "Pending registrations" in the "Institutions sub-menu" "Nav" property
 # Checking Reports menu
 And I should see "Reports"
 # Checking the sub navigation in Extensions
 When I press "Show menu for Extensions"
 Then I should see "Plugin administration" in the "Extensions sub-menu" "Nav" property
 And I should see "HTML filters" in the "Extensions sub-menu" "Nav" property
 And I should see "Allowed iframe sources" in the "Extensions sub-menu" "Nav" property
 And I should see "Clean URLs" in the "Extensions sub-menu" "Nav" property
 And I should see "SmartEvidence" in the "Extensions sub-menu" "Nav" property
 # Checking the sub navigation in Web services
 When I press "Web services"
 Then I should see "Configuration" in the "Web services sub-menu" "Nav" property
 And I should see "Application connections" in the "Web services sub-menu" "Nav" property
 And I should see "Connection manager" in the "Web services sub-menu" "Nav" property
 And I should see "External apps" in the "Web services sub-menu" "Nav" property
 And I should see "Logs" in the "Web services sub-menu" "Nav" property
 And I should see "Test client" in the "Web services sub-menu" "Nav" property

Scenario: Checking menu items are available as Institution Administrator (Bug 1467368)
 Given I log in as "AdminA" with password "Kupuh1pa!"
 # checking the sub navigation in Administration
 And I click on "Show administration menu"
 And I should not see "Configure site" in the "Administration menu" "Nav" property
 And I should not see "Extensions" in the "Administration menu" "Nav" property
 # Checking the sub navigation in People
 And I press "Show menu for People"
 Then I should not see "Site staff" in the "Users sub-menu" "Nav" property
 And I should not see "Site administrators" in the "Users sub-menu" "Nav" property
 And I should see "People search" in the "Users sub-menu" "Nav" property
 And I should see "Suspended and expired accounts" in the "Users sub-menu" "Nav" property
 And I should see "Export queue" in the "Users sub-menu" "Nav" property
 And I should see "Add a person" in the "Users sub-menu" "Nav" property
 And I should see "Add people by CSV" in the "Users sub-menu" "Nav" property
 # Checking the sub navigation in Groups
 And I press "Show menu for Groups" in the "Administration menu" "Nav" property
 Then I should not see "Administer groups" in the "Admin Groups sub-menu" "Nav" property
 And I should not see "Group categories" in the "Admin Groups sub-menu" "Nav" property
 And I should see "Archived submissions" in the "Admin Groups sub-menu" "Nav" property
 And I should see "Add groups by CSV" in the "Admin Groups sub-menu" "Nav" property
 And I should see "Update group members by CSV" in the "Admin Groups sub-menu" "Nav" property
 # Checking the sub navigation in Institutions
 And I press "Show menu for Institutions"
 Then I should see "Profile completion" in the "Institutions sub-menu" "Nav" property
 And I should see "Settings" in the "Institutions sub-menu" "Nav" property
 And I should see "Static pages" in the "Institutions sub-menu" "Nav" property
 And I should see "Legal" in the "Institutions sub-menu" "Nav" property
 And I should see "Members" in the "Institutions sub-menu" "Nav" property
 And I should see "Staff" in the "Institutions sub-menu" "Nav" property
 And I should see "Administrators" in the "Institutions sub-menu" "Nav" property
 And I should see "Admin notifications" in the "Institutions sub-menu" "Nav" property
 And I should see "Pages and collections" in the "Institutions sub-menu" "Nav" property
 And I should see "Share" in the "Institutions sub-menu" "Nav" property
 And I should see "Files" in the "Institutions sub-menu" "Nav" property
 And I scroll to the base of id "navadmin"
 And I should see "Pending registrations" in the "Institutions sub-menu" "Nav" property
 # Checking Reports menu
 And I should see "Reports"

 #Checking the acconut menu navigation headings
 Scenario: Checking account menu items
 Given I log in as "UserA" with password "Kupuh1pa!"
 And I click on the "Account menu" "Nav" property
 Then I should see "Profile"
 And I should see "Profile pictures"
 And I should see "Settings"
 When I press "Show menu for Settings"
 Then I should see "Preferences"
 And I should see "Legal"
 And I should see "Notifications"
 And I should see "Connected apps"
 And I should see "Institution membership"
 And I should see "Logout"
