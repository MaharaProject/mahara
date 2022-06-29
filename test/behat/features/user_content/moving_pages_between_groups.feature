@javascript @core @core_group
Feature: Move posts between forums within groups
    In order to move posts between forum
    As an admin create a group with a forum
    So I can create different posts and move them around

Background:
    And the following "groups" exist:
    | name             | owner | grouptype | editroles |
    | Testing Group 1  | admin | standard    | all       |

Scenario: Moving pages within a group (Bug 1426983)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"
    # Creating a group
    And I choose "Groups" in "Engage" from main menu
    And I click on "Testing Group 1"
    # Creating a forum
    And I click on "Forums" in the "Navigation" "Groups" property
    And I click on "New forum"
    And I set the following fields to these values:
        | Title    | Testing forum 1    |
    And I fill in "Testing forum" in editor "Description"
    And I click on "Save"
    And I click on "Forums" in the "Navigation" "Groups" property
    And I click on "General discussion"
    And I click on "New topic"
    And I set the following fields to these values:
        | Subject   | Testing topic 1   |
    And I fill in "Testing moving topic" in editor "Message"
    And I click on "Post"
    # Verifying post has been created
    And I click on "General discussion"
    And I should see "Testing topic 1"
    # Checking topic 1 checkbox
    And I scroll to the base of id "viewforum"
    And I check "Testing topic 1"
    And I select "Move to" from "action"
    And I should see "Testing forum 1"
    And I click on "Update selected topics"
    # Verifying topic has moved to Testing forum 1
    Then I click on "Forums"
    And I click on "Testing forum 1"
    And I should see "Testing topic 1"
    # Checking link can be followed
    And I click on "Testing topic 1"
    And I should see "Testing moving topic"
    And I should see "Posts: 1"
