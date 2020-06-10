@javascript @core @core_administration
Feature: Make sure 'monitor' module is installed and we can access it
In order to view monitor module
As an admin
So I can benefit from knowing that Mahara site is running as expected

Background:
  Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

Scenario: View the monitor page as admin but not as normal user
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Monitor" in "Admin home" from administration menu
 Then I should see "Long running processes"
 And I log out
 Given I log in as "UserA" with password "Kupuh1pa!"
 And I go to "/module/monitor/monitor.php"
 Then I should see "You are forbidden from accessing the administration section"
