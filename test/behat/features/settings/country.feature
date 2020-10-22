@javascript @core @core_administration @core_settings
Feature: Set country as a required profile field
    As an admin
    I want to set the country field to be required
    As a user
    I see New Zealand as default option when required to fill in country for profile

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

Scenario: Admin user sets country to be mandatory
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Site options" in "Configure site" from administration menu
    And I expand all fieldsets
    # Verify that 'Country' displays the value 'No country selected'.
    Then I should see "No country selected"
    # Enable country in Mandatory fields and save
    When I choose "Plugin administration" in "Extensions" from administration menu
    And I click on "Configuration for artefact internal"
    # And I check "Country"
    And I click on the "Country mandatory field" "Profile" property
    And I press "Save"
    And I log out
    # click submit and verify user is logged in with no error messages
    When I log in as "UserA" with password "Kupuh1pa!"
    Then I should see "Fields marked by '*' are required."
    And I should see "New Zealand"
    When I press "Submit"
    Then I should see "Required profile fields set"
