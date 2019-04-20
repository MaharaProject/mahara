@javascript @core @core_administration @core_webservices
Feature: Show better human readable tooltip for app token

Scenario: Admin set up preconditions for test and confirm that the title text is Delete "Manually created"
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Configuration" in "Web services" from administration menu
    And I enable the switch "Allow outgoing web service requests:"
    And I enable the switch "Accept incoming web service requests:"
    And I enable the switch "REST:"
    And I choose "Plugin administration" in "Extensions" from administration menu
    And I follow "Configuration for module mobileapi"
    And I enable the switch "Manual token generation"
    And I press "Save"
    When I choose "Connected apps" in "Settings" from user menu
    And I follow "Mahara Mobile" in the ".arrow-bar" "css_element"
    And I click on "Generate"
    # confirm that there is an element with title=Delete "Manually created"
    # This is done by pressing a button with that title as there is no definition for see element with that title attribute
    Then I press "Delete \"Manually created\""
