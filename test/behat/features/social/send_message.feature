@javascript @core @core_messages
Feature: Select2 ajax test using sendmessage
   In order to retrieve data via ajax and select it
   As an admin I need to fill in a select2 box
   So I can confirm the value is selectable

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

Scenario: Selecting select2 option via ajax (Bug #1520011)
    # Log in as an Admin user
    Given I log in as "admin" with password "Kupuh1pa!"
    # Send a message
    And I choose inbox
    And I press "Compose"
    Then I should see "Recipients *"
    And I should see "Subject *"
    And I should see "Message *"
    And I fill in select2 input "sendmessage_recipients" with "Angela" and select "Angela User"
    And I set the following fields to these values:
    | Subject | Test message with < & > |
    | Message | This is a test with > & < |
    And I press "Send message"
    Then I should see "Message sent"

    # Checking message
    When I follow "Sent"
    And I follow "Test message"
    Then I should see "Test message with < & >"
    And I should see "This is a test with > & <"
