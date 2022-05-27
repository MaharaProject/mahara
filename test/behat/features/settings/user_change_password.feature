@javascript @core @core_account
Feature: Creating people and an institution enrolling people and changing their passwords
    In order to change passwords successfully
    As an admin create accounts and create an institution
    So I can log in as those people and change their password successfully

Scenario: Creating an institution assigning members and changing their passwords
    # Log in as Admin
    Given I log in as "admin" with password "Kupuh1pa!"
    # Creating an Institution
    And I choose "Settings" in "Institutions" from administration menu
    And I click on "Add institution"
    And I fill in the following:
    | Institution name   | Institution One  |
    And I click on "Submit"
    # Creating account 1
    And I choose "Add an account" in "People" from administration menu
    And I fill in the following:
    | firstname   | bob  |
    | lastname    | bobby    |
    | email       | bob@example.org |
    | username    | bob  |
    | password    | Mahara#1  |
    And I select "Institution One" from "Institution"
    And I enable the switch "Institution administrator"
    And I scroll to the base of id "adduser_submit"
    And I click on "Create account"
    # Creating account 2
    And I choose "Add an account" in "People" from administration menu
    And I fill in the following:
    | firstname   | Jen  |
    | lastname    | Jenny    |
    | email       | jen@example.org |
    | username    | jen  |
    | password    | Mahara#1  |
    And I select "Institution One" from "Institution"
    And I scroll to the base of id "adduser_submit"
    And I click on "Create account"
    # Log out as admin
    And I log out
    # Log in as account 1
    When I log in as "bob" with password "Mahara#1"
    And I fill in the following:
    | New password | Mahara1sGreat@  |
    | Confirm password | Mahara1sGreat@ |
    | Primary email | bob@example.com |
    And I click on "Submit"
    # Verifying password was changed successfully
    And I should see "Your new password has been saved"
    # Changing password
    And I choose "Preferences" in "Settings" from account menu
    And I fill in the following:
    | Current password   | Mahara1sGreat@ |
    | New password   | MaharaIsC00l! |
    | Confirm password   | MaharaIsC00l! |
    When I click on "Save"
    # Verifying password was changed
    And I should see "Preferences saved"
    # Log out as account 1
    And I log out
    # Log in as account 2
    And I log in as "jen" with password "Mahara#1"
    And I fill in the following:
    | New password | Mahara1sGreat@ |
    | Confirm password | Mahara1sGreat@ |
    | Primary email | jen@example.com |
    And I click on "Submit"
    # Verifying password was changed
    And I should see "Your new password has been saved"
    # Changing password
    And I choose "Preferences" in "Settings" from account menu
    And I fill in the following:
    | Current password   | Mahara1sGreat@ |
    | New password   | MaharaIsC00l! |
    | Confirm password   | MaharaIsC00l! |
    And I click on "Save"
    # Verifying password was changed
    And I should see "Preferences saved"
