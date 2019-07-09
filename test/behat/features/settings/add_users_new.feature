@javascript @core @core_administration
Feature: Configuration changes on add users page
In order to change configuration settings on the add users page
As an admin
So I can benefit from the use of different configuration changes

Background:
  Given the following "institutions" exist:
  | name | displayname | registerallowed | registerconfirm |
  | instone | Institution One | ON | OFF |
  | insttwo | Institution Two | ON | OFF |

  And the following "users" exist:
  | username | password | email | firstname | lastname | institution | authname | role |
  | StaffA | Kupuh1pa! | StaffA@example.com | Alexei | Staff | instone | internal | staff |

 Scenario: Admin to add an user (Bug 1703721)
  Given I log in as "admin" with password "Kupuh1pa!"
  And I choose "Add user" in "People" from administration menu
  And I set the following fields to these values:
  | First name | Bob |
  | Last name | One |
  | Email | UserB@example.com |
  | Username | instadmin |
  | password | Kupuh1pa! |
  | Institution administrator | 1 |
  And I select "Institution One" from "adduser_authinstance"
  And I scroll to the top
  And I press "General account options"
  And I set the following fields to these values:
  | Multiple journals | 1 |
  And I press "×" in the "Options dialog" property
  And I press "Create user"
  Then I should see "New user account created successfully"
  And I expand "Institution settings - Institution One" node
  And the field "Institution administrator" matches value "1"
 #Login as Institution admin
  And I should see "Log in as this user"
  And I scroll to the top
  And I follow "Log in as this user"
  And I should see "You are required to change your password before you can proceed."
  And I follow "log in anyway"
  And I choose "People" from administration menu
  And I follow "Bob"
  And I wait "1" seconds
  And I should see "Administrator of Institution One"
  And I click on "Show administration menu"
  And I should see "Groups" in the "Administration menu" property
  And I should not see "Extensions" in the "Administration menu" property
 #Checking  multiple journals
  And I choose "Journals" in "Create" from main menu
  And I should see "Create journal"
  And I log out
  # Test for logout confirmation
  And I should see "You have been logged out successfully"
 #login as staff user
  Given I log in as "StaffA" with password "Kupuh1pa!"
  And I click on "Show administration menu"
  And I should see "Reports" in the "Administration menu" property
  And I should not see "Groups" in the "Administration menu" property
 #Site admin role already tested in menu_navigation.feature file

Scenario: Create users by csv (Bug 1426983)
  Given I log in as "admin" with password "Kupuh1pa!"
 #Adding 50 Users by csv
  And I choose "Add users by CSV" in "People" from administration menu
  And I attach the file "50users_new.csv" to "CSV file"
  And I select "Institution One" from "uploadcsv_authinstance"
  And I press "Add users by CSV"
  Then I should see "Your CSV file was processed successfully"
  And I should see "New users added: 50."
 #Upload 20 users by csv by choosing the switch update users
  And I choose "Add users by CSV" in "People" from administration menu
  And I attach the file "20users_update.csv" to "CSV file"
  And I select "Institution One" from "uploadcsv_authinstance"
  And I enable the switch "Update users"
  And I press "Add users by CSV"
  Then I should see "Your CSV file was processed successfully"
  And I should see "Users updated: 20."
  And I log out
 #Check that we update the fields, password change and email recieved
  Given I log in as "user0005" with password "cH@ngeme3"
  And I should see "You are required to change your password before you can proceed."
  And I fill in "New password" with "dr@Gon123"
  And I fill in "Confirm password" with "dr@Gon123"
  And I press "Submit"
  And I should see "Your new password has been saved"
  And I should see "Institution membership confirmation"
  And I choose "Profile" from user menu
  And the "Student ID" field should contain "64000005"
  And I follow "Contact information"
  And the "Town" field should contain "Stewarts River"
  And the "Mobile phone" field should contain "0491 570 110"
  And I scroll to the center of id "profileform"
  And I follow "General"
  And the "Occupation" field should contain "Hairdresser"
  And I log out
 #login back as admin
  Given I log in as "admin" with password "Kupuh1pa!"
  And I choose "User search" in "People" from administration menu
 # Check that we can delete a user after upload (Bug #1558864)
  And I follow "user0005"
  And I follow "Suspend or delete this user"
  And I scroll to the id "delete"
  And I press and confirm "Delete user"
  And I should see "User deleted successfully"
