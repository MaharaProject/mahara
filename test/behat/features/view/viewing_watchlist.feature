@javascript @core @core_view
Feature: Viewing pages that you have added to watchlist
In order to view a list of pages I am watching
As an admin
So I can access them from my dashboard

Background:
Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | userA | Password1 | test01@example.com | Pete | Mc | mahara | internal | member |

Scenario: Viewing a list of pages I watch from the dashboard (Bug 1444784)
 Given I log in as "admin" with password "Password1"
 When I am on homepage
 Then I should see "Pages I am following"
 And I should see "There are no pages on your watchlist."

Scenario: Viewing last updated time on watchlist items (Bug 1444784)
 Given I log in as "admin" with password "Password1"
 And I follow "Portfolio"
 And I press "Create page"
 And I fill in the following:
 | Page title | Test view |
 And I press "Save"
 And I follow "Display page"
 #FOLLOW THE DROP DOWN TOGGLE
 And I press "More..."
 And I follow "Add page to watchlist"
 And I should see "This page has been added to your watchlist."
# Creating pages that aren't watched
 And the following "pages" exist:
     | title | description| ownertype | ownername |
     | D's Page 01 | UserD's page 01 | user | userA |
     | D's Page 02 | UserD's page 02 | user | userA |
     | A's Page 01 | UserA's page 01 | user | userA |
     | B's Page 02 | UserB's page 02 | user | userA |
And I am on homepage
Then I should not see "D's Page 01"

