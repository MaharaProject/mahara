@javascript @core
Feature: Registration procedure
    In order to check that a person can register
    As an admin
    So people can have access to their Mahara

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm |
    | instone | Institution One | ON | ON |

Scenario: Registering as a new student and checking switch can flick back and forth (Bug 1431569)
    Given I am on homepage
    And I follow "Register"
    And I fill in the following:
    | First name | Lightening |
    | Last name | McQueen |
    | Email address | fakeymcfakey@example.org |
    | Registration reason | I will absolutely make this institution more amazing!! |
    # we wait a human amount of time so the spam trap is avoided
    And I wait "4" seconds
    And I press "Register"
    # Check for conformation message
    Then I should be on "/register.php"
    And I should see "You have successfully submitted your application for registration. The institution administrator has been notified, and you will receive an email as soon as your application has been processed."

    # check for Expiry date column  and text after approval to make it a bit clearer about what is happening when
    Given I follow "Login"
    And I log in as "admin" with password "Kupuh1pa!"
    And I choose "Pending registrations" in "Institutions" from administration menu
    Then I should see "EXPIRES "
    And I should see the date "+2 weeks" in the "tbody tr td:nth-of-type(3)" element with the format "d F Y"
    When I follow "Approve"
    And I press "Approve"
    Then I should see "Approval sent, waiting for person to complete the registration process."
