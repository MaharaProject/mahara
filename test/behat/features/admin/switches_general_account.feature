@javascript @core @core_administration
Feature: Switches on general accounts
In order to change settings
As an admin
I need to be able to switch switches back and forth

Scenario: Turning switchboxes on and off in diff areas (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 # In the adding users area
 When I choose "Add user" in "Users"
 And I follow "General account options"
 # Cheking the default settings are correct first
 And the following fields match these values:
 | Friends control |  |
 | HTML editor | 1 |
 | Disable email | 0 |
 | Messages from other users |  |
 | Show controls to add and remove columns when editing a page | 0 |
 | Multiple journals | 0 |
 | Maximum tags in cloud | 20 |
 | Maximum number of groups to display | |
 | Sort groups | A to Z |
 | Dashboard information | 1 |
 # Changing the switches to their opposite setting
 And I set the following fields to these values:
 | HTML editor | 0 |
 | Disable email | 1 |
 | Show controls to add and remove columns when editing a page | 1 |
 | Multiple journals | 1 |
 | Dashboard information | 0 |
 And I fill in the following:
 | firstname   | white  |
 | lastname    | valiant    |
 | email       | wv@example.com |
 | username    | wval  |
 | password    | mahara1  |
 And I press "Create user"
 # In the Admin block adding users by CSV area
 And I choose "Add users by CSV" in "Users"
 And I follow "General account options"
 And the following fields match these values:
 | Friends control | |
 | HTML editor | 1 |
 | Disable email | 0 |
 | Show controls to add and remove columns when editing a page | 0 |
 | Multiple journals | 0 |
 | Maximum tags in cloud | 20 |
 | Maximum number of groups to display | |
 | Dashboard information | 1 |
 # Changing the switches to the oppisite setting
 And I set the following fields to these values:
 | HTML editor | 0 |
 | Disable email | 1 |
 | Show controls to add and remove columns when editing a page | 1 |
 | Multiple journals | 1 |
 | Dashboard information | 0 |
 And I attach the file "UserCSV.csv" to "CSV file"
 And I uncheck "Force password change"
 And I uncheck "Email users about their account"
 And I press "Add users by CSV"
 # Navigating to the account index
 And I follow "Logout"
 Then I log in as "Bob" with password "Mahara1"
 And I go to "account/index.php"
 # Checking the default settings are correct
 And the following fields match these values:
 | HTML editor | 0 |
 | Disable email | 1 |
 | Show controls to add and remove columns when editing a page | 1 |
 | Multiple journals | 1 |
 | Dashboard information | 0 |
 # Changing the switches to their opposite setting
 And I set the following fields to these values:
 | Friends control | New friends require my authorisation |
 | HTML editor | 1 |
 | Disable email | 0 |
 | Show controls to add and remove columns when editing a page | 0 |
 | Multiple journals | 0 |
 | Maximum tags in cloud | 15 |
 | Dashboard information | 1 |
 And I press "Save"
