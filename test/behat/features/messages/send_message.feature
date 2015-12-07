@javascript @core @core_messages
Feature: Select2 ajax test using sendmessage
   In order to retrieve data via ajax and select it
   As an admin I need to fill in a select2 box
   So I can confirm the value is selectable

Background:
    Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | userA | Kupuhipa1 | test01@example.com | Andrea | Andrews | mahara | internal | member |
    | userB | Kupuhipa1 | test02@example.com | Barry | Bishop | mahara | internal | member |
    | userC | Kupuhipa1 | test03@example.com | Catriona | Carson | mahara | internal | member |

Scenario: Selecting select2 option via ajax
    # Log in as an Admin user
    Given I log in as "admin" with password "Kupuhipa1"
    # Checking messages
    And I follow "mail"
    And I follow "Compose"
    And I fill in select2 input "sendmessage_recipients" with "userA" and select "Andrea Andrews (userA)"
    And I set the following fields to these values:
    | Subject | Test message |
    | Message | This is a test |
    And I press "Send message"
    Then I should see "Message sent"