@javascript @core @core_institution @core_administration
Feature: Create an Institution
   In order to create an institution
   As an admin I need to go Administration
   So I can add an institution

Scenario: Creating an institution (selenium test)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"

    # Creating an Institution
    And I choose "Settings" in "Institutions" from administration menu
    And I click on "Add institution"
    And I fill in the following:
    | Institution name | Institution One |
    And I enable the switch "Allow institution tags"
    And I click on "Submit"
    # Verifying the institution has been created
    Then I should see "Institution added successfully"

    # Adding some authentication options
    And I select "webservice" from "authlistDummySelect"
    And I click on "Add" in the "Authentication" "Institutions" property
    And I click on "Submit"

    # Moving authentication option upwards
    And I click on "Edit" in "Institution One" row
    And I scroll to the base of id "authlistDummySelect"
    And I click on "Move up"
    And I click on "Submit"

    # Removing the first authentication option
    And I click on "Edit" in "Institution One" row
    And I scroll to the base of id "authlistDummySelect"
    And I click on "Delete" in "Web services" row

    # Adding an institution logo
    And I attach the file "Image2.png" to "Logo"
    And I click on "Submit"

    # Checking that institution tags is available
    And I choose "Tags" in "Institutions" from administration menu
    And I should see "Institution tags"

    # Delete the institution
    And I choose "Settings" in "Institutions" from administration menu
    And I click on "Delete" in "Institution One" row
    And I click on "Yes"
    Then I should see "Institution deleted successfully"
    And I should not see "Tags" in the "Submenu" "Institutions" property
