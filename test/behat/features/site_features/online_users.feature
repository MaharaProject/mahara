@javascript @core @blocktype @blocktype_online_users
Feature: Online users side block is displayed on right hand side of pages
    and displays all users that have been online within the last 10 minutes
    So I can know who is on line or online within the last 10 minutes

Background:
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

    # Users A-O log in and log out. User D logs in and views the Online users block
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

Scenario: log in as the latest user and check the following
    1) User should see last 10 users online within the last 10 minutes
    2) when user follows show all online users
    3) User can click a user name and be redirected to the user profile page
    When I log in as "UserO" with password "Kupuh1pa!"
    #User should see last 10 users online within the last 10 minutes
    Then I should see "Olive User" in the "Online users block" property
    And I should see "Jake User" in the "Online users block" property
    And I should see "Ian User" in the "Online users block" property
    And I should see "Henry User" in the "Online users block" property
    And I should see "Gail User" in the "Online users block" property
    And I should see "Fred User" in the "Online users block" property
    And I should see "Earl User" in the "Online users block" property
    And I should see "Dave User" in the "Online users block" property
    And I should see "Carol User" in the "Online users block" property
    And I should see "Bob User" in the "Online users block" property
    And I should not see "Angela User" in the "Online users block" property
    # when user follows show all online users,
    # 1) User should be redirected to show all users page
    # 2) User should see all users online within the last 10 minutes
    # 3) Pagination occurs when there are more than 10 users in the table
    # 4) The table is ordered in Alphabetical order
    When I follow "Show all online users"
    Then I am on "/user/online.php"
    And I should see "Online users" in the "H1 heading" property
    And I should see "Earl User"
    And I should see "Dave User"
    And I should see "Carol User"
    And I should see "Bob User"
    And I should see "Angela User"
    And I should not see "Olive User (UserO)"
    When I jump to page "2" of the list "onlinelist_pagination"
    Then I should see "Olive User (UserO)"
    # User can click a user name and be redirected to the user profile page
    When I follow "Olive User (UserO)"
    Then I should see "Olive User"
    And I should see "About me"
    And I log out

Scenario: Site adminsets Inst setting to show only Inst members instone member logs in and only sees inst members
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Settings" in "Institutions" from administration menu
    And I click on "Edit" in "instone" row
    And I select "Institution only" from "Show online users"
    When I press "Submit"
    Then I should see "Institution updated successfully."
    And I log out
    # Institution member logs in - Verify user only sees other institution members
    Given I log in as "UserA" with password "Kupuh1pa!"
    Then I should see "Carol User"
    And I should not see "Gail User"
