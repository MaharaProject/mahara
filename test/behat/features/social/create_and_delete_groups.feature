@javascript @core @core_group
Feature: Delete groups
    In order to delete a group
    As an admin I need to create groups
    So I can verify they can be deleted successfully

Scenario: Creating groups and deleting them (Selenium)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"
    # Create a Friend invitation only group
    And I choose "Groups" in "Engage" from main menu
    And I click on "Create group"
    And I fill in "Group name" with "Admin Test Group"
    And I disable the switch "Open"
    And I enable the switch "Friend invitations"
    And I click on "Save group"
    # Create Request only group
    And I choose "Groups" in "Engage" from main menu
    And I click on "Create group"
    And I fill in "Group name" with "Admin VIP Group"
    And I disable the switch "Open"
    And I enable the switch "Request"
    And I fill in "Start date" with "2015/06/15 03:00"
    And I fill in "End date" with "2015/06/15 03:30"
    And I click on "Save group"
    # Verify "Group info" block exists
    And I should see "Created" in the "Group info" "Blocks" property
    And I should see "Request membership" in the "Group info" "Blocks" property
    And I should see "Members" in the "Group info" "Blocks" property
    And I should see "Group administrators:" in the "Group info" "Blocks" property
    And I should see "Editable" in the "Group info" "Blocks" property
    And I should see "Between 15 June 2015, 3:00 and 15 June 2015, 3:30"
    # Create Open group
    And I choose "Groups" in "Engage" from main menu
    And I click on "Create group"
    And I fill in "Group name" with "Public discussion"
    And I enable the switch "Participation report"
    And I click on "Save group"
    # Create Controlled group
    And I choose "Groups" in "Engage" from main menu
    And I click on "Create group"
    And I fill in "Group name" with "Controlled groups"
    And I disable the switch "Open"
    And I enable the switch "Controlled"
    And I click on "Save group"
    # Check if the group report page is there
    And I choose "Groups" in "Engage" from main menu
    And I click on "Public discussion"
    And I click on "Report" in the "Arrow-bar nav" "Nav" property
    Then I should see "There are no pages shared with this group yet"
    # Checking groups exist and can be deleted
    And I choose "Groups" in "Engage" from main menu
    And I click on "Admin Test Group"
    And I click on "Delete \"Admin Test Group\""
    And I click on "Yes"
    And I click on "Controlled groups"
    And I click on "Delete \"Controlled groups\""
    And I click on "Yes"
    And I click on "Admin VIP Group"
    And I click on "Delete \"Admin VIP Group\""
    And I click on "Yes"
    And I click on "Public discussion"
    And I click on "Delete \"Public discussion\""
    And I click on "Yes"
    # Verifying none of the deleted groups can be seen
    And I should not see "Admin Test Group"
    And I should not see "Controlled groups"
    And I should not see "Admin VIP Group"
    And I should not see "Public discussion"
