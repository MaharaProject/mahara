@javascript @core @core_institution @core_isolated_institution
Feature: Isolated Institutions functionality
By turning on isolated institutions
1) all institutions on the site are isolated
2) people from different institutions cannot
    a) see each others' profiles
    b) share portfolios
    c) join groups set up by non-institution members
    d) send messages
    e) become friends
3) only site administrators can contact everyone
4) being a member in multiple institutions is not possible
5) all self-registrations need to be confirmed by an institution or site administrator
6) public groups can only be created by site administrators
7) the "Online users" side block can only show institution members at maximum
8) profile pages are not available to all registered users
9) The site administrator can turn on the site setting "See own groups only" in Administration menu →  Configure site →  Site options →  Group settings. This will allow regular institution members to only see groups in which they are a member and other people who are members in the same groups, restricting the contact they can have with others.
10) Institution administrators decide in the institution settings whether the online users side block shall be displayed with just the institution members or not.
11) When an institution member gains access to a profile URL from another institution member, they cannot see the page at all and receive the “Access denied” message, preventing them from even seeing the restricted profile as they should not be able to find out anything about a member of another institution.

Background:
    Given the following site settings are set:
    | field                | value |
    | isolatedinstitutions | 1     |

    And the following "institutions" exist:
    | name    | displayname     | registerallowed | registerconfirm | allowinstitutionpublicviews |
    | instone | Institution One | ON              | OFF             | 1                           |
    | insttwo | Institution Two | ON              | OFF             | 1                           |

    And the following "users" exist:
    | username | password  | email             | firstname | lastname | institution | authname | role   |
    | UserA    | Kupuh1pa! | UserA@example.org | Alice     | User     | instone     | internal | admin  |
    | UserB    | Kupuh1pa! | UserB@example.org | Bob       | User     | instone     | internal | staff  |
    | UserC    | Kupuh1pa! | UserC@example.org | Carol     | User     | instone     | internal | member |
    | UserD    | Kupuh1pa! | UserD@example.org | Danny     | User     | instone     | internal | member |
    | UserE    | Kupuh1pa! | UserE@example.org | Earl      | User     | instone     | internal | member |
    | UserF    | Kupuh1pa! | UserF@example.org | Fred      | User     | insttwo     | internal | admin  |
    | UserG    | Kupuh1pa! | UserG@example.org | Gail      | User     | insttwo     | internal | staff  |
    | UserH    | Kupuh1pa! | UserH@example.org | Hennry    | User     | insttwo     | internal | member |
    | UserI    | Kupuh1pa! | UserI@example.org | Ian       | User     | insttwo     | internal | member |
    | UserJ    | Kupuh1pa! | UserJ@example.org | Jake      | User     | insttwo     | internal | member |

    And the following "groups" exist:
    | name   | owner | description           | institution | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members             | staff |
    | GroupA | UserA | GroupA owned by UserA | instone     | standard  | ON   | OFF           | all       | ON            | OFF           | UserB, UserC, UserD | UserE |
    | GroupB | UserA | GroupB owned by UserA | instone     | standard  | ON   | OFF           | all       | ON            | OFF           |                     |       |
    | GroupF | UserF | GroupF owned by UserF | insttwo     | standard  | ON   | OFF           | all       | ON            | OFF           | UserF, UserG, UserH | UserI |
    | GroupG | UserF | GroupG owned by UserF | insttwo     | standard  | ON   | OFF           | all       | ON            | OFF           |                     |       |

    And the following "pages" exist:
    | title         | description | ownertype | ownername |
    | Page UserA_01 | Page 01     | user      | UserA     |
    | Page UserB_01 | Page 01     | user      | UserB     |
    | Page UserC_01 | Page 01     | user      | UserC     |

    And the following "permissions" exist:
    | title         | accesstype |
    | Page UserA_01 | public     |
    | Page UserB_01 | public     |
    | Page UserC_01 | public     |

Scenario: Users from different institutions cannot see other institution users in the "People" page
    Given I log in as "UserC" with password "Kupuh1pa!"
    # User should not be allowed to join another institution
    When I choose "Institution membership" in "Engage" from main menu
    Then I should see "You are a member of Institution One"
    And I should not see "Request membership of an institution"

    # Searching people
    When I choose "People" in "Engage" from main menu
    # Default search setting - All groups
    Then I should see "Alice User"
    And I should see "Bob User"
    And I should not see "Fred User"
    And I should not see "Jake User"
    # Selecting everyone should not display Users from other institutions
    When I select "Everyone" from "Filter"
    And I press "Search"
    Then I should see "Alice User"
    And I should not see "Jake User"

    # Searching groups
    # Users can not join groups set up by non-institution members
    When I choose "Groups" in "Engage" from main menu
    Then I should see "GroupA"
    And I should not see "GroupB"
    And I should not see "GroupF"
    # User selects Groups I'm in
    When I select "Groups I'm in" from "Filter"
    And I press "Search"
    Then I should see "GroupA"
    And I should not see "GroupB"
    And I should not see "GroupF"
    # User selects Groups I own
    When I select "Groups I own" from "Filter"
    And I press "Search"
    Then I should see "No groups found"
    # User selects Groups I can join
    When I select "Groups I can join" from "Filter"
    And I press "Search"
    Then I should see "GroupB"
    And I should not see "GroupF"
    # User selects Groups I'm not in
    When I select "Groups I'm not in" from "Filter"
    And I press "Search"
    Then I should see "GroupB"
    And I should not see "GroupF"

    # Users can not send messages via the inbox to people outside their institution
    When I follow "Inbox"
    And I follow "Compose"
    # Check we can select user in institution
    And I fill in select2 input "sendmessage_recipients" with "UserB" and select "Bob User"
    # Check we cannot select user not institution
    And I fill in select2 input "sendmessage_recipients" with "UserF" and is not found

    # Check that one can see profile page for an institution member and not for one who isn't
    # For the profile pages to exist we need to login as these users first
    And I log out
    And I log in as "UserB" with password "Kupuh1pa!"
    And I log out
    And I log in as "UserF" with password "Kupuh1pa!"
    And I log out
    And I log in as "UserC" with password "Kupuh1pa!"
    When I go to the profile page of "UserB"
    Then I should see "Staff of Institution One"
    When I go to the profile page of "UserF"
    Then I should see "Access denied"
