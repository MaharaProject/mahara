@javascript @core @core_administration @core_webservices
Feature: To create and test a webservice service group
In order to use webservices
As an admin
So I can benefit from the cross over of Moodle/Mahara
and check correct group fields are returned

Background:
  Given the following "institutions" exist:
     | name | displayname | authname |
     | one | Institution 1 | webservice |
  And the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | serviceadmin | Kupuh1pa! | svad@example.com | Service | Admin | one | webservice | admin |
  And the following "groups" exist:
     | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff | institution |
     | Group A | admin | This is my group A | standard | ON | OFF | all | OFF | OFF | admin |  | one |

Scenario: Turning master switch on
 Given I log in as "admin" with password "Kupuh1pa!"
 And I choose "Configuration" in "Web services" from administration menu
 # Turning the master switch on
 And I enable the switch "Accept incoming web service requests:"
 And I enable the switch "REST:"
 # Create a new service group
 And I expand the section "Manage service groups"
 And I set the field "service" to "Test service"
 And I press "Add"
 And I scroll to the top
 And I wait "1" seconds
 And I click on "Edit" in "Test service" row
 And I set the field "Short name" to "testservice"
 And I enable the switch "Service"
 And I enable the switch "User token access"
 And I enable the switch in "mahara_group_get_groups_by_id" row
 And I enable the switch in "mahara_group_create_groups" row
 And I enable the switch in "mahara_group_update_groups" row
 And I press "Save"
 # Verify service group was made
 And I press "Back"
 And I should see "mahara_group_create_groups"
 And I collapse "Manage service groups" node
 # Create a new token
 And I wait "1" seconds
 And I expand the section "Manage service access tokens"
 And I fill in select2 input "webservices_token_generate_userid" with "Service" and select "Service Admin (serviceadmin)"
 And I press "Generate token"
 And I select "Institution 1" from "Institution"
 And I select "Test service" from "Service"
 # Hide the xmlrpc specific fields when not using them In the add/edit webservice users/tokens screens
 And  I should not see "Public key expires"
 # I should see the xmlrpc specific fields  when Enable web services security (XML-RPC Only) is toggled to yes
 When I enable the switch "Enable web services security (XML-RPC Only)"
 Then I should see "Public key expires"
 And I disable the switch "Enable web services security (XML-RPC Only)"
 And I press "Save"
 # Verify the token was made
 And I press "Back"
 And I should see "Edit" in the "#webservices_token_pseudofieldset" element
 # Test the token
 And I choose "Test client" in "Web services" from administration menu
 And I press "Next"
 And I select "Test service (Token)" from "Service"
 And I press "Next"
 And I select "mahara_group_get_groups_by_id" from "Functions"
 And I press "Next"
 And I fill in "groupa" for "shortname"
 And I select "Institution 1" from "Institution"
 And I fill in the wstoken for "Test service" owned by "Service Admin"

 # For some reason the submitting this form freezes behat
 # And I press "Execute"
 # Then I should see "editroles"
