@javascript @core @core_view
Feature: Viewing pages that you have added to watchlist
In order to view a list of pages I am watching
As an admin
So I can access them from my dashboard

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

And the following "institutions" exist:
 	| name | displayname | registerallowed | registerconfirm |
 	| pcnz | Institution One | ON | OFF |

And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01 | user | UserA |
    | Page UserA_02 | Page 02 | user | UserA |

And the following "permissions" exist:
    | title | accesstype |
    | Page UserA_01 | public |

And the following "blocks" exist:
    | title                     | type     | page                   | retractable | updateonly | data                                                |
    | Portfolios shared with me | newviews | Dashboard page: admin  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |

Scenario: Viewing a list of pages I watch from the dashboard (Bug 1444784)
 Given I log in as "admin" with password "Kupuh1pa!"
 When I am on homepage
 Then I should see "Pages I am watching"
 And I should see "There are no pages on your watchlist."
 # Viewing last updated time on watchlist items (Bug 1444784)
 And I follow "Page UserA_01"
 And I press "More options"
 And I follow "Add page to watchlist"
 And I should see "This page has been added to your watchlist."
 # Check we can see watched page and not an un-watched page
 And I am on homepage
 Then I should not see "Page UserA_02"
 And I should see "Page UserA_01" in the "Pages I am watching" "Blocks" property
