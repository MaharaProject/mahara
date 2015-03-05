@javascript @core @core_group
Feature: Sending notification message when someone leaves a feedback message in a group page
    In order to notify a user of a feedback message in a group page
    As an user I place feedback
    So another person can log in and see a notification message


Background:
    Given the following "users" exist:
     | username  | password  | email | firstname | lastname  | institution   | authname  |role   |
     | bob   | Password1   | bob@example.com   | Bob   | Bobby | mahara    | internal  | member    |
     | jen   | mahara1   | jen@example.com   | Jen   | Jenny | mahara | internal  | member    |

    Given the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
     | Test group 1 | admin | This is group 01 | standard | ON | ON | all | ON | ON | bob , jen  | jen |


    Given the following "pages" exist:
     | title | description| ownertype | ownername |
     | Testing group page 01 | This is the page 01 of the group 01 | group | Test group 1 |



Scenario: Leaving feedback on a group page (Bug 1426983)
    Given I log in as "bob" with password "Password1"
    And I follow "Groups"
    And I follow "Test group 1"
    And I follow "Pages(tab)"
    # And I click on "Pages"
    And I follow "Testing group page 01"
    And I follow "Place feedback"
    And I fill in "Testing feedback notifications" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Place feedback"
    # Log out as user 1
    And I follow "Logout"
    # Log in as "Admin" user
    When I log in as "admin" with password "Password1"
    # Checking notification display on the dashboard
    Then I should see "New feedback on Testing group page 01"
    # Checking notifications also appear in my inbox
    And I follow "Inbox"
    And I follow "New feedback on Testing group page 01"
    And I should see "Testing feedback notifications"
