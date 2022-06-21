@javascript @core @core_view
Feature: Unsubscribing from watchlist via link in email
In order to unsubscribe from watchlist for page I am watching
As a user
I follow unsubscription link in email

Background:
Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |

And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01 | user | UserA |
    | Page UserA_02 | Page 02 | user | UserA |

And the following "permissions" exist:
    | title | accesstype |
    | Page UserA_01 | loggedin |

And the following "blocks" exist:
    | title                     | type     | page                   | retractable | updateonly | data                                                |
    | Portfolios shared with me | newviews | Dashboard page: UserB  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |

Scenario: Viewing a list of pages I watch from the dashboard (Bug 1444784)
 Given I log in as "UserB" with password "Kupuh1pa!"
 And I choose "Notifications" in "Settings" from account menu
 And I select "Email" from "Watchlist"
 And I press "Save"
 And I am on homepage
 And I follow "Page UserA_01"
 And I press "More options"
 And I follow "Add page to watchlist"

 And I should see "This page has been added to your watchlist."
 And I trigger cron
 # Testing the unsubscribe link in the email sent
 And I unsubscribe from "Page UserA_01" owned by "UserB"
 And I should see "You have unsubscribed successfully"
 And I am on homepage
 And I follow "Page UserA_01"
 And I press "More options"
 And I should see "Add page to watchlist"
