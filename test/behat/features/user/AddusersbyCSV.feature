@javascript @core @admin @user
Feature: Mahara admins can add users via CSV files
  As a site or institution admin
  I can add users via CSV files

  Background:
    Given the following "institutions" exist:
      | name | displayname | registerallowed | registerconfirm |
      | instone | Institution One | ON | OFF |
      | insttwo | Institution Two | ON | OFF |
    And the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | admin |
      | userB | Password1 | test02@example.com | Son | Nguyen | instone | internal | admin |
      | userC | Password1 | test03@example.com | Jack | Smith | insttwo | internal | admin |
  Scenario: As a site admin, add users via CSV file
    Given I log in as "userA" with password "Password1"
    When I go to "admin/users/uploadcsv.php"
    And I wait "1" seconds
    Then I should see "Add users by CSV"
    When I attach the file "users.csv" to "uploadcsv_file"
    And I wait "10" seconds
    And I uncheck "uploadcsv_forcepasswordchange"
    And I uncheck "uploadcsv_emailusers"
    And I press "uploadcsv_submit"
    And I wait "1" seconds
    Then I should see "Your CSV file was processed successfully."
    Then I should see "New users added: 5."