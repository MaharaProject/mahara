@javascript @core
Feature: People self deletion requires current password

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

Scenario: Existing person wants to delete their account
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Preferences" in "Settings" from account menu
    And I wait "1" seconds
    When I follow "Delete account"
    Then I am on "account/delete.php"
    And I should see "If you delete your account, all your content will be deleted permanently. You cannot get it back. Your profile information and your portfolios will no longer be visible to other people. The content of any forum posts you have written will still be visible, but your name will no longer be displayed."
    And I should see "Fields marked by '*' are required."
    And I should see "Current password *"
    When I set the field "Current password *" to "Kupuh1pa!"
    And I press "Delete account"
    Then I should see "Your account has been deleted."
