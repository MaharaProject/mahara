@javascript @core_institution @core_administration
Feature: Create an Institution
   In order to create an institution
   As an admin I need to go Administration
   So I can add an institution

Scenario: Creating an institution (selenium test)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in successful
    And I should see "Admin User"
    # Creating an Institution
    When I follow "Administration"
    And I follow "Institutions"
    And I press "Add institution"
    And I fill in the following:
    | Institution name | institutionone  |
    | Institution display name | institution One |
    And I press "Submit"
    # Verifying the institution has been created
    Then I should see "Institution added successfully"
