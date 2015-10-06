@javascript @core @core_administration
Feature: To enable webservices via the webservices configuration form
In order to use webservices
As an admin
So I can benefit from the cross over of Moodle/Mahara


Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

Scenario: Turning master swtich on
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 And I choose "Web services" in "Extensions"
 # Turning the master switch on
 And I check "activate_webservices_enabled"
 When I go to "/webservice/testclient.php"
 And I should not see "The web service authentication plugin is disabled"


Scenario: Logging in as student to try turn switch on
 Given I log in as "userA" with password "Kupuhipa1"
 And I should see "Pete Mc"
 # Checking the student cant access the link
 And "Administration" "link" should not be visible
 Then "Web services" "link" should not be visible
