@javascript @core @core_administration
Feature: Allow user csv upload to ignore non-essential mandatory fields
    In order to add users by csv
    As an admin follow through add users by csv
    So I can change the mandatory fields

Scenario: Create users by csv (Bug 1426983)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Adding Users by CVS
    When I follow "Administration"
    And I choose "Add users by CSV" in "Users"
    And I attach the file "UserCSV.csv" to "CSV file"
    And I uncheck "Force password change"
    And I uncheck "Email users about their account"
    And I press "Add users by CSV"
    Then I should see "Your CSV file was processed successfully"
    And I should see "New users added: 4."
