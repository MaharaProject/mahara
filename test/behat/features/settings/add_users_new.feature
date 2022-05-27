@javascript @core @core_administration
Feature: Configuration changes on "Add an account" page
In order to change configuration settings on the "Add an account" page
As an admin
So I can benefit from the use of different configuration changes

Background:
  Given the following "institutions" exist:
  | name | displayname | registerallowed | registerconfirm |
  | instone | Institution One | ON | OFF |
  | insttwo | Institution Two | ON | OFF |

  And the following "users" exist:
  | username      | password  | email              | firstname | lastname | institution | authname | role         |
  | StaffA        | Kupuh1pa! | StaffA@example.com | Alexei    | Staff    | instone     | internal | staff        |
  | SupportAdminB | Kupuh1pa! | SAB@example.com    | Betty     | Support  | instone     | internal | supportadmin |

Scenario: Masquerading as a support admin
  Given I log in as "SupportAdminB" with password "Kupuh1pa!"
  And I choose "People search" from administration menu
  # Check they can get to the user edit admin page from the people list
  And I click on "StaffA"
  And I click on "Log in as this person"
  And I click on "Become Betty Support again"
  And I choose "People search" from administration menu
  # Check they can get to the user edit admin page via the person's profile page
  And I click on "Alexei"
  And I click on "Log in as StaffA"
  And I click on "Become Betty Support again"
  # Check we can masquerade from the generic search for people results
  And I press "usf_submit"
  Then I should see "Masquerade" in the "Alexei Staff (StaffA)" row
  And I click on "Masquerade" in "Alexei Staff (StaffA)" row
  And I click on "Become Betty Support again"
  And I log out
  Given I log in as "StaffA" with password "Kupuh1pa!"
  And I choose "People search" from administration menu
  # Check they can not get to the user edit admin page via the person's profile page
  And I click on "Betty"
  Then I should not see "Log in as SupportAdminB"
  # Need to have a step to check that a link is not present on the page

Scenario: Admin to add a person (Bug 1703721)
  Given I log in as "admin" with password "Kupuh1pa!"
  And I choose "Add an account" from administration menu
  And I set the following fields to these values:
  | First name | Bob |
  | Last name | One |
  | Email | UserB@example.com |
  | Username | instadmin |
  | password | Kupuh1pa! |
  | Institution administrator | 1 |
  And I select "Institution One" from "adduser_authinstance"
  And I scroll to the top
  And I click on "General account options"
  And I set the following fields to these values:
  | Multiple journals | 1 |
  And I click on "×" in the "Options dialog" "Modal" property
  And I click on "Create account"
  Then I should see "New account created successfully"
  And I expand "Institution settings - Institution One" node
  And the field "Institution administrator" matches value "1"
  # Login as Institution admin
  And I should see "Log in as this person"
  And I click on "Log in as this person"
  And I should see "You are required to change your password before you can proceed."
  And I click on "log in anyway"
  And I choose "People search" from administration menu
  And I click on "Bob"
  And I wait "1" seconds
  And I should see "Administrator of Institution One"
  And I click on "Show administration menu"
  And I should see "Groups" in the "Administration menu" "Nav" property
  And I should not see "Extensions" in the "Administration menu" "Nav" property
  # Checking  multiple journals
  And I choose "Journals" in "Create" from main menu
  And I should see "Create journal"
  And I log out
  # Test for logout confirmation
  And I should see "You have been logged out successfully"
  # Login as staff member
  Given I log in as "StaffA" with password "Kupuh1pa!"
  And I click on "Show administration menu"
  And I should see "Reports" in the "Administration menu" "Nav" property
  And I should not see "Groups" in the "Administration menu" "Nav" property
  # Site admin role already tested in menu_navigation.feature file

Scenario: Create people by csv (Bug 1426983)
  Given I log in as "admin" with password "Kupuh1pa!"
  # Adding 50 people by csv
  And I choose "Add or update accounts by CSV" in "People" from administration menu
  And I attach the file "50users_new.csv" to "CSV file"
  And I select "Institution One" from "uploadcsv_authinstance"
  And I click on "Add or update accounts" in the "CSV submit" "Misc" property
  Then I should see "Your CSV file was processed successfully"
  And I should see "New accounts added: 50."
  # Upload 20 people by csv by choosing the switch update users
  And I choose "Add or update accounts by CSV" in "People" from administration menu
  And I attach the file "20users_update.csv" to "CSV file"
  And I select "Institution One" from "uploadcsv_authinstance"
  And I enable the switch "Update accounts"
  And I click on "Add or update accounts" in the "CSV submit" "Misc" property
  Then I should see "Your CSV file was processed successfully"
  And I should see "Accounts updated: 20."
  And I log out
  # Check that we update the fields, password change and email received
  Given I log in as "person0005" with password "cH@ngeme3"
  And I should see "You are required to change your password before you can proceed."
  And I fill in "New password" with "dr@Gon123"
  And I fill in "Confirm password" with "dr@Gon123"
  And I click on "Submit"
  And I should see "Your new password has been saved"
  And I choose "Profile" from account menu
  And the "Student ID" field should contain "64000005"
  And I click on "Contact information"
  And the "Town" field should contain "Stewarts River"
  And the "Mobile phone" field should contain "0491 570 110"
  And I scroll to the center of id "profileform"
  And I click on "General"
  And the "Occupation" field should contain "Minister for Māori Development"
  And I log out
  # Login back as admin
  Given I log in as "admin" with password "Kupuh1pa!"
  And I choose "People search" in "People" from administration menu
  # Check that we can delete an account after upload (Bug #1558864)
  And I click on "person0005"
  And I click on "Suspend or delete this account"
  And I scroll to the id "delete"
  And I press and confirm "Delete account"
  And I should see "Account deleted successfully"

Scenario: Check for error messages for the following expiry dates when uploading accounts via CSV
          a) expire date used is in the past
          b) expire date is wrong format
  Given I log in as "admin" with password "Kupuh1pa!"
  # Adding 7 people by csv with expiry date errors
  And I choose "Add or update accounts by CSV" in "People" from administration menu
  And I attach the file "7usersnew-errors.csv" to "CSV file"
  And I select "Institution One" from "uploadcsv_authinstance"
  And I click on "Add or update accounts" in the "CSV submit" "Misc" property
  Then I should see "There was an error with submitting this form. Please check the marked fields and try again."
  And I should see "Error on line 2: The expiry \"today\" cannot be in the past."
  And I should see "Error on line 3: The expiry \"2025-01--30\" is invalid. Please use a valid date format."
  And I should see "Error on line 4: The expiry \"2025/01-29\" is invalid. Please use a valid date format."
  And I should see "Error on line 5: The expiry \"Marych 27, 2025\" is invalid. Please use a valid date format."

Scenario: Adding people using different expiry date formats via CSV upload
          a) 2025-01-30 (Dashes)
          b) 2025/01/29 (forward slash)
          c) March 27, 2025 (month written full)
          d) 20-JUN-2025 (month abbv)
          e) Thu, May 8, 25 (day abbv and month written full)
  Given I log in as "admin" with password "Kupuh1pa!"
  # Adding 7 people by csv with corrected expiry date formats
  And I choose "Add or update accounts by CSV" in "People" from administration menu
  And I attach the file "7usersnew-correctdates.csv" to "CSV file"
  And I select "Institution One" from "uploadcsv_authinstance"
  And I click on "Add or update accounts" in the "CSV submit" "Misc" property
  Then I should see "Your CSV file was processed successfully."
  And I should see "New accounts added: 6."
