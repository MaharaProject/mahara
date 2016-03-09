@javascript @core @core_group
Feature: Delete groups
    In order to delete a group
    As an admin I need to create groups
    So I can verify they can be deleted successfully

Scenario: Creating groups and deleting them (Selenium)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Creating 4 types of groups
    When I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Admin Test Group"
    And I uncheck "Open"
    And I check "Friend invitations"
    And I press "Save group"
    # Verifying group saved
    And I should see "Group saved successfully"
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Admin VIP Group"
    And I uncheck "Open"
    And I check "Request"
    And I press "Save group"
    # Verifing group saved
    And I should see "Group saved successfully"
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Public discussion"
    And I press "Save group"
    # Verifing group saved
    And I should see "Group saved successfully"
    And I follow "Groups"
    And I follow "Create group"
    And I fill in "Group name" with "Controlled groups"
    And I uncheck "Open"
    And I check "Controlled"
    And I press "Save group"
    # Verifing group saved
    And I should see "Group saved successfully"
    # Deleting groups and verifying they are deleted
    Then I follow "Groups"
    And I follow "Admin Test Group"
    And I follow "Delete group"
    And I press "Yes"
    And I should see "Group deleted successfully"
    And I follow "Controlled groups"
    And I follow "Delete group"
    And I press "Yes"
    And I should see "Group deleted successfully"
    And I follow "Admin VIP Group"
    And I follow "Delete group"
    And I press "Yes"
    And I should see "Group deleted successfully"
    And I follow "Public discussion"
    And I follow "Delete group"
    And I press "Yes"
    And I should see "Group deleted successfully"
    # Verifying none of the deleted groups can be seen
    And I should not see "Admin Test Group"
    And I should not see "Controlled groups"
    And I should not see "Admin VIP Group"
    And I should not see "Public discussion"
