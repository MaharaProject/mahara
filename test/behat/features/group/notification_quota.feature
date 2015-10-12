@javascript @core @core_group
Feature: Notification when a user is about to reach his quota
    In order to verify notification when reaching a quota
    As an admin create users
    So I can change their quota limit and verify notification

Background:
    Given the following "users" exist:
    | username  | password  | email | firstname | lastname  | institution   | authname  |role   |
    | bob   | Kupuhipa1   | bob@example.com   | Bob   | Bobby | mahara    | internal  | member    |

Scenario Outline: When quota notification threshold is changed, send notifications to users who are now over threshold (Bug 1367539)
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Admin User"
    # Modifying user quota quota to 2MB
    And I follow "Administration"
    And I follow "Extensions"
    And I follow "Configuration for artefact file"
    And I follow "Default user quota"
    # Clearning the tex box first to enter 2 MB
    And I fill in "Default quota" with ""
    And I fill in "Default quota" with "2"
    # Update already existing users
    And I check "Update user quotas"
    # Modifying user notification threshold to multiple %
    And I fill in "Quota notification threshold" with "<threshold>"
    And I press "Save"
    # Verifying changes were made
    And I should see "Settings saved"
    # Log out as "Admin user"
    And follow "Logout"
    # Log in as user 1
    When I log in as "bob" with password "Kupuhipa1"
    # Verifying log in was successful
    And I should see "Bob Bobby"
    # Upload files to reach quota threshold of 50%
    And I choose "Files" in "Content"
    And I attach the file "Image1.jpg" to "Upload file"
    And I attach the file "Image2.png" to "Upload file"
    And I attach the file "Image3.png" to "Upload file"
    # Verifying notification for reaching user quota threshold have been received
    And I am on homepage
    And I follow "mail"
    # Regression testing for previous errors
    And I should not see "Call stack"
    And I should see "Your file storage is almost full"

  Examples:
| threshold |
| 50 |
| 30 |
| 25 |
