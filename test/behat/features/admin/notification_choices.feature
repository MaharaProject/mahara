@javascript @core @core_administration
Feature: Improvements to Notification Systems
In order to select multiple types of notifications
As an admin
I need to go to Notifications settings and see multiple options available

Background:
    Given the following "users" exist:
    | username  | password  | email | firstname | lastname  | institution   | authname  | role  |
    | Bob   | mahara1   | bob@example.com   | Bob   | Bobby | mahara    | internal  | member    |

Scenario: Confirm that multiple notification choices are available (Bug #1299993)
    # Log in as admin
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Navigating to notification settings
    When I follow "Administration"
    And I follow "Configure site"
    And I follow "Notification settings"
    # Verifying "None" option is not available for these notifications
    And the "System message" field should not contain "None"
    And the "Message from other users" field should not contain "None"
    # Verifying all options are available for the rest of Admin notifications
    And I select "Email" from "Contact us"
    And I select "Email digest" from "Contact us"
    And I select "Inbox" from "Contact us"
    And I select "None" from "Contact us"
    And I select "Email" from "Objectionable content"
    And I select "Email digest" from "Objectionable content"
    And I select "Inbox" from "Objectionable content"
    And I select "None" from "Objectionable content"
    And I select "Email" from "Repeat virus upload"
    And I select "Email digest" from "Repeat virus upload"
    And I select "Inbox" from "Repeat virus upload"
    And I select "None" from "Repeat virus upload"
    And I select "Email" from "Virus flag release"
    And I select "Email digest" from "Virus flag release"
    And I select "Inbox" from "Virus flag release"
    And I select "None" from "Virus flag release"
    And I select "Email" from "Objectionable content in forum"
    And I select "Email digest" from "Objectionable content in forum"
    And I select "Inbox" from "Objectionable content in forum"
    And I select "None" from "Objectionable content in forum"
    And I press "Update site options"
    # Log out as "Admin User"
    And I follow "Logout"
    # Logging in as user1
    Then I log in as "bob" with password "mahara1"
    # Verifying log in was successful
    And I should see "Bob Bobby"
    # Navigating to notification settings
    And I follow "Settings"
    And I follow "Notifications"
    # Verifying the "None" option is not available for the following notifications
    And the "System message" field should not contain "None"
    And the "Message from other users" field should not contain "None"
    And the "System message" field should not contain "None"
    # Verifying all options are selectable for the following user notifications
    And I select "Email" from "System message"
    And I select "Email digest" from "System message"
    And I select "Inbox" from "System message"
    And I select "Email" from "Message from other users"
    And I select "Email digest" from "Message from other users"
    And I select "Inbox" from "Message from other users"
    And I select "Email" from "Watchlist"
    And I select "Email digest" from "Watchlist"
    And I select "Inbox" from "Watchlist"
    And I select "None" from "Watchlist"
    And I select "Email" from "New page access"
    And I select "Email digest" from "New page access"
    And I select "Inbox" from "New page access"
    And I select "None" from "New page access"
    And I select "Email" from "Institution message"
    And I select "Email digest" from "Institution message"
    And I select "Inbox" from "Institution message"
    And I select "None" from "Institution message"
    And I select "Email" from "Group message"
    And I select "Email digest" from "Group message"
    And I select "Inbox" from "Group message"
    And I select "None" from "Group message"
    And I select "Email" from "Feedback"
    And I select "Email digest" from "Feedback"
    And I select "Inbox" from "Feedback"
    And I select "None" from "Feedback"
    And I select "Email" from "New forum post"
    And I select "Email digest" from "New forum post"
    And I select "Inbox" from "New forum post"
    And I select "None" from "New forum post"
    And I press "Save"
