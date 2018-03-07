@javascript @core @core_institution @core_administration
Feature: Create Institution tags
   In order to create institution tags
   As an admin I need to go to Institution tags page
   So I can add institution tags

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm | tags |
    | instone | Institution One | ON | OFF | 1 |

Scenario: Creating institution tags
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"

    # Creating an Institution
    And I choose "Tags" in "Institutions" from administration menu
    And I follow "Create tag"
    And I set the field "Institution tag" to "One tag"
    And I press "Save"
    Then I should see "Institution tag saved"
