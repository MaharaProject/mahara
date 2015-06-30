@javascript @core @core_administration
Feature: Accessing the webservices test client page
In order to be able to access the webservies menus successfully
As an admin
So I can benefit from the mahara/moodle cross over.

Scenario: Turning Master swtiches and protocol switches on checking test client page is accessible (Bug 1431540)
 Given I log in as "admin" with password "Password1"
 And I follow "Administration"
 And I choose "Web services" in "Extensions"
 # Turning the master switch on
 And I check "activate_webservices_enabled"
 # Turning the master switch off so the protocols are disabled
 And I uncheck "activate_webservices_enabled"
 # Turning the master switch back on
 And I check "activate_webservices_enabled"
 And I should see "You need to enable at least one Protocol"
 # Turning a protocol on
 And I check "activate_webservice_protos_soap_enabled"
 # Navigating to the test client page to see it's accessible
 When I go to "webservice/testclient.php"
 Then I should see "This is the interactive test client facility for web services."
 And I should not see "Select elements should have at least one option"
 And I should not see "The web service authentication plugin is disabled."
 # Unchecking a protocol and going to test client page to check it gives warning message
 And I am on homepage
 And I follow "Administration"
 And I go to "webservice/admin/index.php"
 # Turning protocol off
  And I uncheck "activate_webservice_protos_soap_enabled"
 # Going to webservices test client page
 When I go to "webservice/testclient.php"
 # The regression error that's appearing
 Then I should not see "Call stack (most recent first):"
 # What we do want to see
 And I should see "This is the interactive test client facility for web services. This enables you to select a function and then execute it live against the current system. Please be aware that ANY function you execute will run for real."
