@javascript @core @core_group
Feature: Limit the number of groups an institution may have
    In order to limit groups by institution
    As a group member
    I can only make a group if the limit has not been reached

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |
    | instone | Institution One | ON | OFF |

    And the following "users" exist:
    | username | password  | email             | firstname | lastname | institution | authname | role   |
    | UserA    | Kupuh1pa! | UserA@example.org | Angela    | User     | instone     | internal | member |

    And the following "groups" exist:
    | name   | owner | description           | grouptype | open | invitefriends | editroles | submittableto | allowarchives | institution |
    | GroupA | UserA | GroupA owned by UserA | standard  | ON   | OFF           | all       | ON            | OFF           | instone     |
    | GroupB | UserA | GroupB owned by UserA | standard  | ON   | OFF           | all       | OFF           | OFF           | instone     |
    | GroupC | UserA | GroupC owned by UserA | course    | ON   | OFF           | all       | ON            | OFF           | instone     |
    | GroupD | UserA | GroupD owned by UserA | standard  | ON   | OFF           | all       | ON            | OFF           | instone     |

    # Group creation need to be for all
    And the following site settings are set:
    | field | value |
    | creategroups | all |

Scenario: Set group limit for an institution
    # Log in as an admin user
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Settings" in "Institutions" from administration menu
    And I click on "Edit" in "Institution One" row
    And I set the following fields to these values:
    | Maximum number of groups allowed | 5 |
    And I click on "Submit"
    And I choose "Add groups by CSV" in "Groups" from administration menu
    # Attaching the groups via CSV
    And I set the following fields to these values:
    | Institution | Institution One |
    And I attach the file "groups.csv" to "CSV file"
    When I click on "Add groups by CSV" in the "CSV submit" "Misc" property
    Then I should see "Adding this many groups exceeds the group limit for your institution"
    And I log out

    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I click on "Create group"
    And I set the following fields to these values:
    | Group name        | GroupE                |
    | Group description | GroupE owned by UserA |
    And I click on "Save group"
    And I should see "Group saved successfully"
    And I click on "Edit \"GroupE\""
    And I set the following fields to these values:
    | Group description | This is GroupE owned by UserA |
    And I click on "Save group"
    And I choose "Groups" in "Engage" from main menu
    And I click on "Create group"
    Then I should see "Groups cannot be added to this institution because the maximum number of groups allowed in the institution has been reached"
