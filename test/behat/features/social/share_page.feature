@javascript @core
Feature: Share page with different people (Bug 1755688)
As a user
I want to control access to my pages
So that people I choose can see them

Background:
 Given the following "users" exist:
 | username | password | email | firstname | lastname | institution | authname | role |
 | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
 | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
 | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | mahara | internal | member |

 And the following "groups" exist:
 | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
 | GroupA | UserA | GroupA owned by UserA | standard | ON | OFF | all | OFF | OFF | UserB |  |
 | GroupB | admin | GroupB owned by admin | standard | ON | ON | all | ON | ON | UserA, UserB, UserC |  |

 And the following "pages" exist:
 | title | description | ownertype | ownername |
 | Page UserA_01 | Page 01 | user | UserA |
 | Page UserB_02 | Page 02 | user | UserB |
 | Page UserA_03 | Page 03 | user | UserA |
 | Page UserA_04 | Page 04 | user | UserA |

Scenario: Check share page with friends
 # Log in as a normal userA
 Given I log in as "UserA" with password "Kupuh1pa!"
 # Edit sharing permissions for Page 03
 And I choose "People" in "Engage" from main menu
 And I click on "Send friend request" in "Bob User" row
 And I set the field "Message" to "Love me, love me, say you do!"
 And I click on "Request friendship"
 And I log out
 And I log in as "UserB" with password "Kupuh1pa!"
 And I click on "pending friend"
 And I click on "Approve"
 And I choose "Portfolios" in "Create" from main menu
 And I click on "Manage sharing" in "Page UserB_02" card access menu
 And I select "Friends" from "General" in shared with select2 box
 And I click on "Save"
 And I log out
 Given I log in as "UserA" with password "Kupuh1pa!"
 And I choose "Shared with me" in "Share" from main menu
 And I check "Friends"
 And I uncheck "Me"
 And I uncheck "My groups"
 And I click on "Search"
 Then I should see "Page UserB_02"

Scenario: Check share page with Registered Users
 # Log in as a normal userA
 Given I log in as "UserA" with password "Kupuh1pa!"
 # Edit sharing permissions for Page 03
 And I choose "Portfolios" in "Create" from main menu
 And I click on "Manage sharing" in "Page UserA_03" card access menu
 And I select "Registered people" from "General" in shared with select2 box
 And I click on "Save"
 And I log out
 And I log in as "UserB" with password "Kupuh1pa!"
 And I choose "Shared with me" in "Share" from main menu
 And I check "Registered people"
 And I uncheck "Me"
 And I uncheck "Friends"
 And I uncheck "My groups"
 And I uncheck "Public"
 And I click on "Search"
 Then I should see "Page UserA_03"

Scenario: Check share page with groups and that copy options works
 # Log in as a normal userA
 Given I log in as "UserA" with password "Kupuh1pa!"
 # Edit sharing permissions for Page 03
 And I choose "Portfolios" in "Create" from main menu
 And I click on "Manage sharing" in "Page UserA_03" card access menu
 And I select "GroupA" from "Groups" in shared with select2 box
 And I select "GroupB" from "Groups" in shared with select2 box
 And I expand "Advanced options" node
 # enable copy switch
 And I enable the switch "Allow copying"
 And I should see "Retain view access on copied portfolios"
 And I click on "Save"
 And I log out
 And I log in as "UserB" with password "Kupuh1pa!"
 And I choose "Shared with me" in "Share" from main menu
 And I check "My groups"
 And I uncheck "Me"
 And I uncheck "Friends"
 And I click on "Search"
 And I click on "Page UserA_03"
 # check copy option is available
 And I click on "More options"
 Then I should see "Copy"
