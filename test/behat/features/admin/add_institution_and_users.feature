@javascript @core @core_administration
Feature: Creating an institution then adding users in different roles
   In order to add users to the institution
   As an admin I need to create an user and give institution privileges
   So I can add it to the institution

Scenario: Creating an institution and users
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Creating an Institution
    And I follow "Administration"
    And I follow "Institutions"
    And I press "Add institution"
    And I fill in the following:
    | Institution name   | institutionone  |
    | Institution display name    | institution One |
    And I press "Submit"
    # Verifying Institution was created successfully
    And I should see "Institution added successfully"
    # Creating user 1 as Institution administrator"
    Then I follow "Users"
    And I choose "Add user" in "Users"
    And I fill in the following:
    | firstname   | bob  |
    | lastname    | bobby    |
    | email       | bob@example.com |
    | username    | bob  |
    | password    | mahara1  |
    And I select "institution One" from "Institution"
    And I check "Institution administrator"
    And I press "Create user"
    # Creating user 2 as a normal Institution member
    And I follow "Users"
    And I choose "Add user" in "Users"
    And I fill in the following:
    | firstname   | Jen  |
    | lastname    | Jenny    |
    | email       | jen@example.com |
    | username    | jen  |
    | password    | mahara1  |
    And I select "institution One" from "Institution"
    And I press "Create user"
    # Verifying user was added successfully
    And I should see "New user account created successfully"
