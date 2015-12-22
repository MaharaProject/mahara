@javascript @core_administration @failed
Feature: To enable webservices via the webservices configuration form
In order to use webservices
As an admin
So I can benefit from the cross over of Moodle/Mahara


Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

Scenario: Turning master switch on
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 And I choose "Web services" in "Extensions"
 And I follow "Web services master switch"
 # Turning the master switch on
 And I set the following fields to these values:
 | Switch web services on or off: | 1 |
 When I go to "/webservice/testclient.php"
 And I should not see "The web service authentication plugin is disabled"
 And I log out
 # Logging in as student to try turn switch on
 When I log in as "userA" with password "Kupuhipa1"
 Then I should see "Pete Mc"
 # Checking the student cant access the link
 And "Administration" "link" should not be visible
 And "Web services" "link" should not be visible
