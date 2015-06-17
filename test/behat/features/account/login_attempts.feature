@javascript @core @core_account
Feature: Limit password attempts to 5 tries
In order to make sure you can't make more than 5 bad password attempts at a time
As an admin/user
So I can prevent dictionary attacks on my passwords

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |

Scenario: Too many bad password attempts
# I should not see any error message on the first 5 attempts
When I log in as "userA" with password "wrongpassword"
And I log in as "userA" with password "wrongpassword"
And I log in as "userA" with password "wrongpassword"
And I log in as "userA" with password "wrongpassword"
And I log in as "userA" with password "wrongpassword"
Then I should not see "You have exceeded the maximum login attempts."
And I should see "Login"
# I've failed 5 times. Now even if I log in with the correct password I'm locked out.
When I log in as "userA" with password "Password1"
Then I should see "You have exceeded the maximum login attempts."
And I should see "Login"
# The cron should reset the limit, allowing me to log in again
When I trigger cron
And I log in as "userA" with password "Password1"
# I'm logged in!
Then I should see "Dashboard"
