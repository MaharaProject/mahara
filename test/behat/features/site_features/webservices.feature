@javascript @core @core_administration @core_webservices
Feature: To create and test a webservice service group
In order to use webservices
As an admin
So I can benefit from the cross over of Moodle/Mahara
and check correct group fields are returned

Background:
  Given the following "institutions" exist:
     | name | displayname   | authname   |
     | one  | Institution 1 | webservice |
  And the following "users" exist:
     | username     | password  | email             | firstname | lastname | institution | authname   | role   |
     | serviceadmin | Kupuh1pa! | svad@example.com  | Service   | Admin    | one         | webservice | admin  |
     | UserA        | Kupuh1pa! | UserA@example.org | Angela    | User     | mahara      | internal   | member |
  And the following "groups" exist:
     | name    | owner | description        | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff | institution |
     | Group A | admin | This is my group A | standard  | ON   | OFF           | all       | OFF           | OFF           | admin   |       | one         |

Scenario: As administrator I can
 1) Check enabling Requester and Provider master switches requires a protocol
 2) Create and verify service groups
 3) Enable Mahara mobile and set a manually created access token and confirm that the manually created token can be deleted
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Configuration" in "Web services" from administration menu
    And I enable the switch "Allow outgoing web service requests:"
    And I enable the switch "Accept incoming web service requests:"
    # Verify that error message is displayed if no protocols are selected
    And I should see "You need to enable at least one protocol"
    And I enable the switch "REST:"
    And I expand the section "Manage service groups"
    And I set the field "service" to "Test service"
    And I click on "Add" in the "#webservices_function_groups_add" "css_element"
    And I click on "Edit" in "Test service" row
    And I set the field "Short name" to "testservice"
    And I enable the switch "Service"
    And I enable the switch "User token access"
    And I enable the switch in "mahara_group_get_groups_by_id" row
    And I enable the switch in "mahara_group_create_groups" row
    And I enable the switch in "mahara_group_update_groups" row
    And I click on "Save"
    # Verify service group was made
    And I should see "mahara_group_create_groups"
    And I collapse "Manage service groups" node
    And I wait "1" seconds
    And I expand the section "Manage service access tokens"
    And I fill in select2 input "webservices_token_generate_userid" with "Service" and select "Service Admin (serviceadmin)"
    And I click on "Generate token"
    And I select "Institution 1" from "Institution"
    And I select "Test service" from "Service"
    # Verify I should see the xmlrpc specific fields  when Enable web services security (XML-RPC Only) is toggled to yes
    When I enable the switch "Enable web services security (XML-RPC Only)"
    Then I should see "Public key expires"
    # Hide the xmlrpc specific fields when not using them In the add/edit webservice users/tokens screens
    And I disable the switch "Enable web services security (XML-RPC Only)"
    Then I should not see "Public key expires"
    And I click on "Save"
    # Verify token was made and Test the token
    And I scroll to the id "webservices_token_pseudofieldset"
    And I should see "Edit" in the "Manage service access tokens" "Webservices" property
    And I choose "Test client" in "Web services" from administration menu
    Then I should see "This is the interactive test client facility for web services."
    # Verify Text on Web service test client | Web services configuration page with and without a protocol
    And I should not see "The web service authentication plugin is disabled."
    And I click on "Next"
    And I select "Test service (Token)" from "Service"
    And I click on "Next"
    And I select "mahara_group_get_groups_by_id" from "Functions"
    And I click on "Next"
    And I fill in "groupa" for "shortname"
    And I select "Institution 1" from "Institution"
    And I fill in the wstoken for "Test service" owned by "Service Admin"
    ###
    # NOTE: Pressing "Execute" here fails as behat is not set up to handle webservice calls
    ###
    And I choose "Plugin administration" in "Extensions" from administration menu
    And I click on "Configuration for module mobileapi"
    And I enable the switch "Manual token generation"
    And I click on "Save"
    When I choose "Connected apps" in "Settings" from account menu
    And I click on "Mahara Mobile" in the "Arrow-bar nav" "Nav" property
    And I click on "Generate"
    Then I should see "Manually created"
    When I click on "Delete"
    Then I should see "You have not granted access to any applications"

 Scenario: As a student I can
  1) Check that I can't access the webservice administration area
  2) Generate a manual token once administrator has allowed this
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Plugin administration" in "Extensions" from administration menu
    And I click on "Configuration for module mobileapi"
    And I enable the switch "Auto-configure mobile apps API"
    And I enable the switch "Manual token generation"
    And I click on "Save"
    And I log out
    When I log in as "UserA" with password "Kupuh1pa!"
    And I go to "webservice/admin/index.php"
    Then I should see "You are forbidden from accessing the administration section."
    When I choose "Connected apps" in "Settings" from account menu
    And I click on "Mahara Mobile" in the "Arrow-bar nav" "Nav" property
    And I click on "Generate"
    Then I should see "Manually created"
    When I click on "Delete"
    Then I should see "You have not granted access to any applications"
