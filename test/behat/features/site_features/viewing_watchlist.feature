@javascript @core @core_view
Feature: Viewing pages that you have added to watchlist
In order to view a list of pages I am watching
As an admin
So I can access them from my dashboard

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | userA | Kupuhipa1 | test01@example.org | Pete | Mc | mahara | internal | member |

And the following "pages" exist:
    | title | description| ownertype | ownername |
    | A's Page 01 | UserA's page 01 | user | userA |
    | A's Page 02 | UserA's page 02 | user | userA |

And the following "permissions" exist:
    | title | accesstype |
    | A's Page 01 | public |

Scenario: Viewing a list of pages I watch from the dashboard (Bug 1444784)
 Given I log in as "admin" with password "Kupuhipa1"
 When I am on homepage
 Then I should see "Watched pages"
 And I should see "There are no pages on your watchlist."
 # Viewing last updated time on watchlist items (Bug 1444784)
 And I follow "A's Page 01"
 And I press "More..."
 And I follow "Add page to watchlist"
 And I should see "This page has been added to your watchlist."
 # Check we can see watched page and not an un-watched page
 And I am on homepage
 Then I should not see "A's Page 02"
 And I should see "A's Page 01" in the "ul#watchlistblock" "css_element"

