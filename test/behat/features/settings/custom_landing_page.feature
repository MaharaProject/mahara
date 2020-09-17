@javascript @core @core_account @core_login
Feature: Set a custom landing page on login
In order to show a page other than the default dashboard on person login
As an admin
I can set the custom landing page (uses forum topic page)
As a user
I can see custom page on login

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

    And the following "groups" exist:
    | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | public |
    | GroupX | admin | GroupX owned by admin | standard | ON | ON | all | ON | ON | UserA | 1 |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page admin_01 | This is the landing page | user | admin |

    And the following "permissions" exist:
    | title | accesstype | accessname | allowcomments | approvecomments |
    | Page admin_01 | loggedin | loggedin | 0 | 0 |

Scenario: Set the custom landing page
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Configure site" from administration menu
    # I set the custom landing option
    And I expand the section "Site settings"
    And I enable the switch "Custom landing page"
    # Check if we can use forum topic as landing page
    When I fill in select2 input "siteoptions_homepageredirecturl" with "General" and select "General discussion (GroupX)"
    And I press "Update site options"
    And I log out

    # Now see if we land on forum page
    Given I log in as "UserA" with password "Kupuh1pa!"
    Then I should see "GroupX general discussion forum"
    And I log out

    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Configure site" from administration menu
    # I set the custom landing option
    And I expand the section "Site settings"
    # Check if we can use a page as landing page
    And I clear value "General discussion (GroupX)" from select2 field "siteoptions_homepageredirecturl"
    When I fill in select2 input "siteoptions_homepageredirecturl" with "Page admin_01" and select "Page admin_01 (Admin Account)"
    And I press "Update site options"
    And I log out

    # Now see if we land on user page
    Given I log in as "UserA" with password "Kupuh1pa!"
    Then I should see "This is the landing page"
    And I log out

    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Configure site" from administration menu
    # I set the custom landing option
    And I expand the section "Site settings"
    And I disable the switch "Custom landing page"
    And I press "Update site options"
    And I log out

    # Now see if we land on dashboard page
    Given I log in as "UserA" with password "Kupuh1pa!"
    Then I should see "Edit dashboard"
    And I log out
