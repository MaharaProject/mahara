@javascript @core @blocktype @blocktype_online_users
Feature: "People online"" side block is displayed on right hand side of pages
    and displays all people that have been online within the last 10 minutes
    So I can know who is on line or online within the last 10 minutes

Background:
    And the following site settings are set:
    | field                | value |
    | isolatedinstitutions | 0     |

    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |
    | instone | Institution One | ON | OFF |

    Given the following "users" exist:
    | username | password  | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | instone  | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org | Bob | User | instone  | internal | member |
    | UserC | Kupuh1pa! | UserCV@example.org | Carol | User | instone | internal | member |
    | UserD | Kupuh1pa! | UserD@example.org | Dave | User | instone  | internal | member |
    | UserE | Kupuh1pa! | UserE@example.org | Earl | User | instone  | internal | member |
    | UserF | Kupuh1pa! | UserF@example.org | Fred | User | instone  | internal | member |
    | UserG | Kupuh1pa! | UserG@example.org | Gail | User | mahara  | internal | member |
    | UserH | Kupuh1pa! | UserH@example.org | Henry | User | mahara  | internal | member |
    | UserI | Kupuh1pa! | UserI@example.org | Ian | User | mahara  | internal | member |
    | UserJ | Kupuh1pa! | UserJ@example.org | Jake | User | mahara  | internal | member |
    | UserO | Kupuh1pa! | UserP@example.org | Olive | User | mahara  | internal | member |

    # Users A-O log in and log out. User D logs in and views the "People online" block
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I log out
    And I log in as "UserB" with password "Kupuh1pa!"
    And I log out
    And I log in as "UserC" with password "Kupuh1pa!"
    And I log out
    And I log in as "UserD" with password "Kupuh1pa!"
    And I log out
    And I log in as "UserE" with password "Kupuh1pa!"
    And I log out
    And I log in as "UserF" with password "Kupuh1pa!"
    And I log out
    And I log in as "UserG" with password "Kupuh1pa!"
    And I log out
    And I log in as "UserH" with password "Kupuh1pa!"
    And I log out
    And I log in as "UserI" with password "Kupuh1pa!"
    And I log out
    And I log in as "UserJ" with password "Kupuh1pa!"
    And I log out

Scenario: log in as the latest person and check the following
    1) Person should see last 10 others online within the last 10 minutes
    2) when person follows, show all people that are online
    3) Person can click the name of somebody else and be redirected to their profile page
    When I log in as "UserO" with password "Kupuh1pa!"
    #Person should see last 10 people online within the last 10 minutes
    Then I should see "Olive User" in the "Online users block" "Blocks" property
    And I should see "Jake User" in the "Online users block" "Blocks" property
    And I should see "Ian User" in the "Online users block" "Blocks" property
    And I should see "Henry User" in the "Online users block" "Blocks" property
    And I should see "Gail User" in the "Online users block" "Blocks" property
    And I should see "Fred User" in the "Online users block" "Blocks" property
    And I should see "Earl User" in the "Online users block" "Blocks" property
    And I should see "Dave User" in the "Online users block" "Blocks" property
    And I should see "Carol User" in the "Online users block" "Blocks" property
    And I should see "Bob User" in the "Online users block" "Blocks" property
    And I should not see "Angela User" in the "Online users block" "Blocks" property
    # when person follows show people online,
    # 1) Person should be redirected to "People online" page
    # 2) Person should see all people online within the last 10 minutes
    # 3) Pagination occurs when there are more than 10 people in the table
    # 4) The table is ordered in Alphabetical order
    When I follow "Show people online"
    Then I am on "/user/online.php"
    And I should see "People online" in the "H1 heading" "Common" property
    And I should see "Earl User"
    And I should see "Dave User"
    And I should see "Carol User"
    And I should see "Bob User"
    And I should see "Angela User"
    # PCNZ customisation - custom display name without displaying username for regular accounts matches the profile box at the bottom
    Then I should not see "Olive User" in the "Search results" "Online_users" property
    When I jump to page "2" of the list "onlinelist_pagination"
    Then I should see "Olive User"
    # Person can click a person's name and be redirected to their profile page
    When I follow "Olive User" in the "Search results" "Online_users" property
    Then I should see "Olive User"
    And I should see "About me"
    And I log out

Scenario: Site adminsets Inst setting to show only Inst members instone member logs in and only sees inst members
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Settings" in "Institutions" from administration menu
    And I click on "Edit" in "instone" row
    And I select "Institution only" from "Show who is online"
    When I press "Submit"
    Then I should see "Institution updated successfully."
    And I log out
    # Institution member logs in - Verify person only sees other institution members
    Given I log in as "UserA" with password "Kupuh1pa!"
    Then I should see "Carol User"
    And I should not see "Gail User"
