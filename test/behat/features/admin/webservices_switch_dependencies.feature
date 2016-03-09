@javascript @core_administration @failed
Feature: Accessing the webservices test client page
In order to be able to access the webservies menus successfully
As an admin
So I can benefit from the mahara/moodle cross over.

Scenario: Turning Master swtiches and protocol switches on checking test client page is accessible (Bug 1431540)
 Given I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 And I go to "webservice/admin/index.php"
 # Turning the master switch on
 And I follow "Web services master switch"
 # Turning the master switch off so the protocols are disabled
 And I set the following fields to these values:
 | Switch web services on or off: | 0 |
 # Turning the master switch back on
 And I set the following fields to these values:
 | Switch web services on or off: | 1 |
 And I should see "You need to enable at least one Protocol"
 # Turning a protocol on
 And I set the following fields to these values:
 | activate_webservice_protos_soap_enabled | 1 |
 # Navigating to the test client page to see it's accessible
 When I go to "webservice/testclient.php"
 Then I should see "This is the interactive test client facility for web services."
 And I should not see "Select elements should have at least one option"
 And I should not see "The web service authentication plugin is disabled."
 # Unchecking a protocol and going to test client page to check it gives warning message
 And I am on homepage
 And I follow "Administration"
 And I go to "webservice/admin/index.php"
 And I follow "Switch protocols on or off"
 # Turning protocol off
 And I set the following fields to these values:
 | activate_webservice_protos_soap_enabled | 0 |
 # Going to webservices test client page
 When I go to "webservice/testclient.php"
 # The regression error that's appearing
 Then I should not see "Call stack (most recent first):"
 # What we do want to see
 And I should see "This is the interactive test client facility for web services. This enables you to select a function and then execute it live against the current system. Please be aware that ANY function you execute will run for real."
