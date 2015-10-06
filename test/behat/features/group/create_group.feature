@javascript @core @core_group
Feature: Creating different types of groups
    In order to create different types of groups
    As an admin
    So I can check that they are accessible

Scenario: Creating a group and adding users to it (Selenium)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Create a Friend invitation only group
    When I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Admin Test Group"
    And I uncheck "Open"
    And I check "Friend invitations"
    And I press "Save group"
    And I should see "Journals" in the "div.arrow-bar" "css_element"
    # Create Request only group
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Admin VIP Group"
    And I uncheck "Open"
    And I check "Request"
    And I press "Save group"
    # Create Open group
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Public discussion"
    And I press "Save group"
    # Create Controlled group
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Controlled groups"
    And I uncheck "Open"
    And I check "Controlled"
    And I press "Save group"
    # Verify all groups has been created
    And I follow "Groups"
    Then I should see "Admin Test Group"
    And I should see "Admin VIP Group"
    And I should see "Controlled Group"
    And I should see "Public discussion"
