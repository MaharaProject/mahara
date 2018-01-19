@javascript @core @core_account
Feature: Lost username / password
In order to request a reminder of my password or username
As an admin/user
So I can recover an account after forgetting my username or password

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuhipa1 | UserA@example.org | Angela | User | mahara | internal | member |
And I am on homepage
And I follow "Lost username / password"

Scenario: Asking for a username reminder (Bug 1460911)
When I fill in "Email address or username" with "UserA@example.org"
And I press "Send request"
Then I should see "You should receive an email shortly with a link you can use to change the password for your account."

Scenario: Asking for a password reset (Bug 1460911)
When I fill in "Email address or username" with "UserA"
And I press "Send request"
Then I should see "You should receive an email shortly with a link you can use to change the password for your account."

Scenario: Trying a username or password that doesn't exist (Bug 1460911)
When I fill in "Email address or username" with "nosuchuser"
And I press "Send request"
Then I should see "If you do not receive an email either the details you entered are incorrect or you normally use external authentication to access the site"
