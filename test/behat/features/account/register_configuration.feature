@javascript @core @core_account
Feature: Registration procedure
In order to check that a person can register
As an admin
So people can have access to their Mahara

 Scenario: Registering as a new student and checking swtich can flick back and forth (Bug 1431569)
 Given I log in as "admin" with password "Kupuhipa1"
 And the following "institutions" exist:
 | name | displayname | registerallowed | registerconfirm |
 | instone | Institution One | ON | ON |
 And I follow "Logout"
 And I follow "Register"
 And I fill in the following:
 | First name | Lightening |
 | Last name | McQueen |
 | Email address | fakeymcfakey@gmail.com |
 | Registration reason | I will absolutely make this institution more amazing!! |
 # we wait a human amount of time so the spam trap is avoided
 And I wait "4" seconds
 And I press "Register"
 And I follow "Login"
 And I log in as "admin" with password "Kupuhipa1"
 And I follow "Administration"
 And I choose "Pending registrations" in "Institutions"
 And I follow "Approve"
 # Checking switch matches the default value
 And the following fields match these values:
 | Institution staff | 0 |
 # Turning it to the opposite
 And I set the following fields to these values:
 | Institution staff | 1 |
 # Checking it can turn back to the default setting
 And I set the following fields to these values:
 | Institution staff | 0 |
And I press "Approve"
