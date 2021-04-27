@javascript @core @core_group
Feature: Delete groups
    In order to delete a group
    As an admin I need to create groups
    So I can verify they can be deleted successfully

Scenario: Creating groups and deleting them (Selenium)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"
    # Create a Contact invitation only group
    And I choose "Groups" in "Engage" from main menu
    And I follow "Create group"
    And I fill in "Group name" with "Admin Test Group"
    And I disable the switch "Open"
    And I enable the switch "Contact invitations"
    And I press "Save group"
    # Create Request only group
    And I choose "Groups" in "Engage" from main menu
    And I follow "Create group"
    And I fill in "Group name" with "Admin VIP Group"
    And I disable the switch "Open"
    And I enable the switch "Request"
    And I fill in "Start date" with "2015/06/15 03:00"
    And I fill in "End date" with "2015/06/15 03:30"
    And I press "Save group"
    # Verify "Group info" block exists
    And ".bt-groupinfo" "css_element" should be visible
    And I should see "Created" in the ".bt-groupinfo" "css_element"
    And I should see "Request membership" in the ".bt-groupinfo" "css_element"
    And I should see "Members" in the ".bt-groupinfo" "css_element"
    And I should see "Group administrators:" in the ".bt-groupinfo" "css_element"
    And I should see "Editable" in the ".bt-groupinfo" "css_element"
    And I should see "Between 15 June 2015, 3:00 and 15 June 2015, 3:30"
    # Create Open group
    And I choose "Groups" in "Engage" from main menu
    And I follow "Create group"
    And I fill in "Group name" with "Public discussion"
    And I enable the switch "Participation report"
    And I press "Save group"
    # Create Controlled group
    And I choose "Groups" in "Engage" from main menu
    And I follow "Create group"
    And I fill in "Group name" with "Controlled groups"
    And I disable the switch "Open"
    And I enable the switch "Controlled"
    And I press "Save group"
    # Check if the group report page is there
    And I choose "Groups" in "Engage" from main menu
    And I follow "Public discussion"
    And I follow "Report" in the ".arrow-bar" "css_element"
    Then I should see "There are no pages shared with this group yet"
    # Checking groups exist and can be deleted
    And I choose "Groups" in "Engage" from main menu
    And I follow "Admin Test Group"
    And I follow "Delete \"Admin Test Group\""
    And I press "Yes"
    And I follow "Controlled groups"
    And I follow "Delete \"Controlled groups\""
    And I press "Yes"
    And I follow "Admin VIP Group"
    And I follow "Delete \"Admin VIP Group\""
    And I press "Yes"
    And I follow "Public discussion"
    And I follow "Delete \"Public discussion\""
    And I press "Yes"
    # Verifying none of the deleted groups can be seen
    And I should not see "Admin Test Group"
    And I should not see "Controlled groups"
    And I should not see "Admin VIP Group"
    And I should not see "Public discussion"
