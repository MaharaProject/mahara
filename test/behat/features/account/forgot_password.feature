@javascript @core @core_account
Feature: Lost username / password
In order to request a reminder of my password or username
As an admin/user
So I can recover an account after forgetting my username or password

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
Given I log in as "admin" with password "Kupuhipa1"
And I follow "Administration"
And I follow "Configure site"
And I follow "Email settings"
And I fill in the following:
    | noreplyaddress | noreply@localhost |
And I press "Update site options"
And I log out
And I am on homepage
And I follow "Lost username / password"

Scenario: Asking for a username reminder (Bug 1460911)
When I fill in "Email address or username" with "test01@example.com"
And I press "Send request"
Then I should see "You should receive an email shortly with a link you can use to change the password for your account."

Scenario: Asking for a password reset (Bug 1460911)
When I fill in "Email address or username" with "userA"
And I press "Send request"
Then I should see "You should receive an email shortly with a link you can use to change the password for your account."

Scenario: Trying a username or password that doesn't exist (Bug 1460911)
When I fill in "Email address or username" with "nosuchuser"
And I press "Send request"
Then I should see "The email address or username you entered does not match any users for this site"
