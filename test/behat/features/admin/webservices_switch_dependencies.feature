@javascript @core @core_administration
Feature: Accessing the webservices test client page
In order to be able to access the webservies menus successfully
As an admin
So I can benefit from the mahara/moodle cross over.

Scenario: Testing web service admin page on/off switches, and checking test client page is accessible (Bug 1431540)
 Given I log in as "admin" with password "Kupuhipa1"
 And I choose "Configuration" in "Web services" from Admin menu
 # Toggle Web service requester master switch
 And I disable the switch "Allow outgoing web service requests:"
 And I enable the switch "Allow outgoing web service requests:"
 # Turning the Web service provider master switch on
 And I enable the switch "Accept incoming web service requests:"
 # Turning the Web service provider master switch off so the protocols are disabled
 And I disable the switch "Accept incoming web service requests:"
 # Turning the Web service provider master switch back on
 And I enable the switch "Accept incoming web service requests:"
 And I should see "You need to enable at least one protocol"
 # Turning a protocol on
 And I enable the switch "SOAP:"
 # Navigating to the test client page to see it's accessible
 And I choose "Test client" in "Web services" from Admin menu
 Then I should see "This is the interactive test client facility for web services."
 And I should not see "Select elements should have at least one option"
 And I should not see "The web service authentication plugin is disabled."
 # Unchecking a protocol and going to test client page to check it gives warning message
 And I am on homepage
 And I go to "webservice/admin/index.php"
 # Turning protocol off
 And I disable the switch "SOAP:"
 # Going to webservices test client page
 When I go to "webservice/testclient.php"
 # The regression error that's appearing
 Then I should not see "Call stack (most recent first):"
 # What we do want to see
 And I should see "This is the interactive test client facility for web services. This enables you to select a function and then execute it live against the current system. Please be aware that ANY function you execute will run for real."
