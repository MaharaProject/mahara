@javascript @core @core_administration @core_webservices
Feature: To enable webservices via the webservices configuration form
In order to use webservices
As an admin
So I can benefit from the cross over of Moodle/Mahara


Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

Scenario: Turning master switch on
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Configuration" in "Web services" from administration menu
 # Turning the master switch on
 And I enable the switch "Accept incoming web service requests:"
 And I choose "Test client" in "Web services" from administration menu
 And I should not see "The web service authentication plugin is disabled"
 And I log out
 # Logging in as student to try turn switch on
 When I log in as "UserA" with password "Kupuh1pa!"
 Then I should see "Angela User"
 # Checking the student can't access the link
 And "Administration" "link" should not be visible
 And "Web services" "link" should not be visible
