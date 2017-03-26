@javascript @core @core_view @core_portfolio
Feature: Checking the correct menu items are available for each user
In order to make sure the correct menu items are available
As every user
So users can access features in Mahara.

Background:
Given the following "institutions" exist:
     | name | displayname | registerallowed | registerconfirm |
     | instone | instone | ON | OFF |


Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | example04@example.com | student | student | mahara | internal | member |
     | userB | Kupuhipa1 | example01@example.com  | site | staff | mahara | internal |  staff  |
     | instone | Kupuhipa1 | example05@example.com | instone | instone | instone | internal | staff  |
     | instone1 | Kupuhipa1 | example06@example.com  | instone1 | instone1 | instone | internal | admin  |



Scenario: Checking menu items are available as a student (Bug 1467368)
 Given I log in as "userA" with password "Kupuhipa1"
 # Checking the main menu navigation headings
 When I click on "Show Menu"
 And I follow "Dashboard"
 Then I should not see "Administration" in the "#main-nav-admin" "css_element"
 And I should not see "Site information" in the "#main-nav-admin" "css_element"
 And I click on "Show Menu"
 And I should see "Content" in the "#main-nav" "css_element"
 And I should see "Portfolio" in the "#main-nav" "css_element"
 And I should see "Groups" in the "#main-nav" "css_element"
 # Checking the sub navigation in Content
 When I follow "Content"
 And I click on "Show Menu"
 Then I should see "Profile" in the ".content" "css_element"
 And I should see "Profile pictures" in the ".content" "css_element"
 And I should see "Files" in the ".content" "css_element"
 And I should see "Journals" in the ".content" "css_element"
 And I should see "Résumé" in the ".content" "css_element"
 And I should see "Plans" in the ".content" "css_element"
 And I should see "Notes" in the ".content" "css_element"
# Checking the sub navigation in Portfolio
 When I follow "Portfolio"
 And I click on "Show Menu"
 Then I should see "Pages and collections" in the ".myportfolio" "css_element"
 And I should see "Shared by me" in the ".myportfolio" "css_element"
 And I should see "Shared with me" in the ".myportfolio" "css_element"
 And I should see "Export" in the ".myportfolio" "css_element"
 And I should see "Import" in the ".myportfolio" "css_element"
# Checking the sub navigation in Groups
 When I follow "Groups"
 And I click on "Show Menu"
 Then I should see "My groups" in the ".groups" "css_element"
 And I should see "Find groups" in the ".groups" "css_element"
 And I should see "My friends" in the ".groups" "css_element"
 And I should see "Find people" in the ".groups" "css_element"
 And I should see "Institution membership" in the ".groups" "css_element"
 And I should see "Topics" in the ".groups" "css_element"


Scenario: Checking menu items are available as site staff (Bug 1467368)
 Given I log in as "userB" with password "Kupuhipa1"
 Then I should not see "Administration" in the "#main-nav" "css_element"
# The one major difference a site staff has is site info link that leads to other links
 And I click on "Show Administration Menu"
 And I follow "User search"
 And I click on "Show Administration Menu"
 And I follow "Site statistics"
 And I click on "Show Administration Menu"
 Then I follow "Institution statistics"


Scenario: Checking menu items are available as Admin User (Bug 1467368)
 Given I log in as "admin" with password "Kupuhipa1"
# checking the sub navigation in Administration
 And I click on "Show Administration Menu"
 Then I should see "Admin home" in the "#main-nav-admin" "css_element"
 And I should see "Configure site" in the "#main-nav-admin" "css_element"
 And I should see "Users" in the "#main-nav-admin" "css_element"
 And I should see "Groups" in the "#main-nav-admin" "css_element"
 And I should see "Institutions" in the "#main-nav-admin" "css_element"
 And I should see "Extensions" in the "#main-nav-admin" "css_element"
# Checking the sub navigation in Admin home
 When I press "Show Menu" in the ".adminhome" "css_element"
 Then I should see "Overview" in the ".adminhome" "css_element"
 And I should see "Register" in the ".adminhome" "css_element"
 And I should see "Site statistics" in the ".adminhome" "css_element"
# Checking the sub navigation in Configure site
 When I press "Show Menu" in the ".configsite" "css_element"
 Then I should see "Site options" in the ".configsite" "css_element"
 And I should see "Static pages" in the ".configsite" "css_element"
 And I should see "Menus" in the ".configsite" "css_element"
 And I should see "Networking" in the ".configsite" "css_element"
 And I should see "Licenses" in the ".configsite" "css_element"
 And I should see "Pages and collections" in the ".configsite" "css_element"
 And I should see "Share" in the ".configsite" "css_element"
 And I should see "Files" in the ".configsite" "css_element"
 And I should see "Cookie Consent" in the ".configsite" "css_element"
# Checking the sub navigation in Users
 When I press "Show Menu" in the ".configusers" "css_element"
 Then I should see "User search" in the ".configusers" "css_element"
 And I should see "Suspended and expired users" in the ".configusers" "css_element"
 And I should see "Site staff" in the ".configusers" "css_element"
 And I should see "Site administrators" in the ".configusers" "css_element"
 And I should see "Export queue" in the ".configusers" "css_element"
 And I should see "Add user" in the ".configusers" "css_element"
 And I should see "Add users by CSV" in the ".configusers" "css_element"
# Checking the sub navigation in Groups
 When I press "Show Menu" in the ".managegroups" "css_element"
 Then I should see "Administer groups" in the ".managegroups" "css_element"
 And I should see "Group categories" in the ".managegroups" "css_element"
 And I should see "Archived submissions" in the ".managegroups" "css_element"
 And I should see "Add groups by CSV" in the ".managegroups" "css_element"
 And I should see "Update group members by CSV" in the ".managegroups" "css_element"
# Checking the sub navigation in Institutions
 When I press "Show Menu" in the ".manageinstitutions" "css_element"
 Then I should see "Institutions" in the ".manageinstitutions" "css_element"
 And I should see "Static pages" in the ".manageinstitutions" "css_element"
 And I should see "Members" in the ".manageinstitutions" "css_element"
 And I should see "Staff" in the ".manageinstitutions" "css_element"
 And I should see "Administrators" in the ".manageinstitutions" "css_element"
 And I should see "Admin notifications" in the ".manageinstitutions" "css_element"
 And I should see "Profile completion" in the ".manageinstitutions" "css_element"
 And I should see "Pages and collections" in the ".manageinstitutions" "css_element"
 And I should see "Share" in the ".manageinstitutions" "css_element"
 And I should see "Files" in the ".manageinstitutions" "css_element"
 And I should see "Statistics" in the ".manageinstitutions" "css_element"
 And I should see "Pending registrations" in the ".manageinstitutions" "css_element"
# Checking the sub navigation in Extensions
 When I press "Show Menu" in the ".configextensions" "css_element"
 Then I should see "Plugin administration" in the ".configextensions" "css_element"
 And I should see "HTML filters" in the ".configextensions" "css_element"
 And I should see "Allowed iframe sources" in the ".configextensions" "css_element"
 And I should see "Clean URLs" in the ".configextensions" "css_element"


Scenario: Checking menu items are available as Institution Administrator (Bug 1467368)
 Given I log in as "instone1" with password "Kupuhipa1"
# checking the sub navigation in Administration
 And I click on "Show Administration Menu"
 And I should not see "Configure site" in the "#main-nav-admin" "css_element"
 And I should not see "Extensions" in the "#main-nav-admin" "css_element"
# Checking the sub navigation in Users
 And I press "Show Menu" in the ".configusers" "css_element"
 Then I should not see "Site staff" in the ".configusers" "css_element"
 And I should not see "Site administrators" in the ".configusers" "css_element"
 And I should see "User search" in the ".configusers" "css_element"
 And I should see "Suspended and expired users" in the ".configusers" "css_element"
 And I should see "Export queue" in the ".configusers" "css_element"
 And I should see "Add user" in the ".configusers" "css_element"
 And I should see "Add users by CSV" in the ".configusers" "css_element"
# Checking the sub navigation in Groups
 And I press "Show Menu" in the ".managegroups" "css_element"
 Then I should not see "Administer groups" in the ".managegroups ul" "css_element"
 And I should not see "Group categories" in the ".managegroups" "css_element"
 And I should see "Archived submissions" in the ".managegroups" "css_element"
 And I should see "Add groups by CSV" in the ".managegroups" "css_element"
 And I should see "Update group members by CSV" in the ".managegroups" "css_element"
# Checking the sub navigation in Institutions
 And I press "Show Menu" in the ".manageinstitutions" "css_element"
 Then I should not see "Profile completion" in the ".manageinstitutions" "css_element"
 And I should see "Settings" in the ".manageinstitutions" "css_element"
 And I should see "Static pages" in the ".manageinstitutions" "css_element"
 And I should see "Members" in the ".manageinstitutions" "css_element"
 And I should see "Staff" in the ".manageinstitutions" "css_element"
 And I should see "Administrators" in the ".manageinstitutions" "css_element"
 And I should see "Admin notifications" in the ".manageinstitutions" "css_element"
 And I should see "Pages and collections" in the ".manageinstitutions" "css_element"
 And I should see "Share" in the ".manageinstitutions" "css_element"
 And I should see "Files" in the ".manageinstitutions" "css_element"
 And I should see "Statistics" in the ".manageinstitutions" "css_element"
 And I should see "Pending registrations" in the ".manageinstitutions" "css_element"
