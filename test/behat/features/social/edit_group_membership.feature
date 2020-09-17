@javascript @core @core_messages
Feature: Edit group membership
   In order to edit group membership
   As an admin I can edit membership via the 'People' page

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
    | UserC | Kupuh1pa! | UserC@example.org |Cecilia | User | mahara | internal | member |
    | UserD | Kupuh1pa! | UserD@example.org | Dmitri | User | mahara | internal | member |
    | UserE | Kupuh1pa! | UserE@example.org | Evonne | User | mahara | internal | member |
    | UserF | Kupuh1pa! | UserF@example.org | Fergus | User | mahara | internal | member |
    | UserG | Kupuh1pa! | UserG@example.org | Gabi | User | mahara | internal | member |
    | UserH | Kupuh1pa! | UserH@example.org | Hugo |User | mahara | internal | member |
    | UserI | Kupuh1pa! | UserI@example.org | Iria | User | mahara | internal | member |
    | UserJ | Kupuh1pa! | UserJ@example.org | Julius |User | mahara | internal | member |
    | UserK | Kupuh1pa! | UserK@example.org | Kristina | User | mahara | internal | member |
    | UserL | Kupuh1pa! | UserL@example.org | Liam | User | mahara | internal | member |

And the following "groups" exist:
    | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
    | GroupA | admin | GroupA owned by admin | standard | ON | ON | all | ON | ON |  UserA, UserC, UserD, UserE, UserF, UserG, UserH, UserK  | UserI, UserJ |
    | GroupB | admin | GroupB owned by admin | standard | ON | ON | all | ON | ON | UserC, UserD |  |

Scenario: User view members page, verify user list displayed and sorted by selection in sorted by field
    Given I log in as "UserA" with password "Kupuh1pa!"
    When I go to group "GroupA"
    And I follow "Members"
    Then I should see "Admin first" in the "[name='sortoption']" element
    And the "sorted by:" select box should contain "Admin first"
    And I should see "Admin Account" in the "#membersearchresults .list-group-item:nth-of-type(1) .list-group-item-heading" "css_element"
    And I should see "Iria User" in the "#membersearchresults .list-group-item:nth-of-type(2) .list-group-item-heading" "css_element"
    And I should see "Julius User" in the "#membersearchresults .list-group-item:nth-of-type(3) .list-group-item-heading" "css_element"
    And I should see "Angela User" in the "#membersearchresults .list-group-item:nth-of-type(4) .list-group-item-heading" "css_element"
    And I should see "Dmitri User" in the "#membersearchresults .list-group-item:nth-of-type(6) .list-group-item-heading" "css_element"
    When I select "Name Z to A" from "sorted by:"
    And I press "Search"
    Then I should see "Kristina User" in the "#membersearchresults .list-group-item:nth-of-type(1) .list-group-item-heading" "css_element"
    And I should see "Julius User" in the "#membersearchresults .list-group-item:nth-of-type(2) .list-group-item-heading" "css_element"
    And I should see "Iria User" in the "#membersearchresults .list-group-item:nth-of-type(3) .list-group-item-heading" "css_element"
    And I should see "Hugo User" in the "#membersearchresults .list-group-item:nth-of-type(4) .list-group-item-heading" "css_element"
    And I should see "Fergus User" in the "#membersearchresults .list-group-item:nth-of-type(6) .list-group-item-heading" "css_element"

Scenario: Check modal is working for the "Edit group memebership" on find people page (Bug 1513265)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "People" in "Engage" from main menu
    And I follow "2" in the "Find people results" property
    And I click on "Edit group membership" in "Liam User" row
    # allow the modal to open
    And I wait "1" seconds
    And I check "GroupA"
    And I follow "Apply changes"
    And I scroll to the top
    Then I should see "Invite sent"
