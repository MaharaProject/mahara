@javascript @core @gdpr

Feature: Strict privacy switch
    As a new person logging in for the first time
    When strict privacy is enabled
    I should be required to accept the privacy statement

Scenario: Create account for which person logs in with strict privacy enabled
    Given I log in as "admin" with password "Kupuh1pa!"
    And I choose "Site options" in "Configure site" from administration menu
    And I expand "Institution settings" node
    # Need to disable multiple inst first, or set strict privacy doesn't work.
    And I disable the switch "People allowed multiple institutions"
    And I enable the switch "Strict privacy"
    # Check this worked as otherwise there is no point in continuing
    And the field "Strict privacy" matches value "1"
    And I press "Update site options"
    # Background adding of acconut doesn't work for this test
    And I choose "Add a person" in "People" from administration menu
    And I set the following fields to these values:
    | First name | Bob |
    | Last name | One |
    | Email | UserB@example.com |
    | Username | bob |
    | password | Kupuh1pa! |
    And I scroll to the top
    And I press "Create account"
    And I disable the switch "Force password change on next login"
    And I enable the switch "Disable email"
    And I press "Save changes"
    And I log out
    Given I log in as "bob" with password "Kupuh1pa!"
    Then I should see "Before entering your account, please read the information displayed below."
    # Try to ignore privacy statement
    And I choose "Pages and collections" in "Create" from main menu
    Then I should see "Before entering your account, please read the information displayed below."
    And I press "Save changes"
    Then I should see "If you do not consent to the privacy statement(s) or terms and conditions, your account will be suspended."
    Then I press "Cancel"
    # consent to privacy statement
    And I enable the switch "I consent to the privacy statement"
    And I enable the switch "I consent to the terms and conditions"
    And I press "Save changes"
    Then I should see "Welcome"
