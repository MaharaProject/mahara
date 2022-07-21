@javascript @core @core_view @core_portfolio
Feature: The "Portfolio -> Shared with me" screen
As a Mahara account holder
I want to see the Pages & Collections that have been shared with me
displayed in latest comment update order
So that I can manage my response to change

Background:
    Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
      | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
      | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | mahara | internal | member |

    And the following "groups" exist:
      | name | owner | description | grouptype | open | invitefriends | editroles |
      | GroupA | UserC | GroupA owned by UserC | standard | ON | OFF | all |

    And the following "pages" exist:
      | title | description | ownertype | ownername |
      | Page UserA_01 | Page 01 | user | UserA |
      | Page UserA_02 | Page 02 | user | UserA |
      | Page UserA_03 | Page 03 | user | UserA |
      | Page UserB_01 | Page 01 | user | UserB |
      | Page mahara_01 | Institution page | institution | mahara |
      | Page GroupA_01 | GroupA page | group | GroupA |

    And the following "collections" exist:
      | title | description | ownertype | ownername | pages |
      | Collection UserA_01 | Collection 01 | user | UserA | Page UserA_01, Page UserA_02 |

    And the following "permissions" exist:
      | title | accesstype | accessname | allowcomments |
      | Collection UserA_01 | user | UserB | 1 |
      | Page UserA_03 | user | UserB | 1 |
      | Page UserB_01 | user | UserA | 1 |
      | Page mahara_01 | loggedin | loggedin | 1 |
      | Page GroupA_01 | loggedin | loggedin | 1 |

  And the following "blocks" exist:
      | title                     | type     | page                  | retractable | updateonly | data                                                |
      | Portfolios shared with me | newviews | Dashboard page: UserA | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |

Scenario: Testing that views & collections are collated properly
    # Putting some comments on the pages
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Page UserA_01" in "Collection UserA_01" card collection
    And I click on "Add comment"
    And I fill in "I am on UserA_01 page" in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property

    And I choose "Portfolios" in "Create" from main menu
    And I click on "Page UserA_02" in "Collection UserA_01" card collection
    And I click on "Add comment"
    And I fill in "I am on UserA_02 page" in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property

    And I choose "Portfolios" in "Create" from main menu
    And I click the card "Page UserA_03"
    And I click on "Add comment"
    And I fill in "I am on Page UserA_03" in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property

    When I log out
    And I log in as "UserB" with password "Kupuh1pa!"
    And I choose "Shared with me" in "Share" from main menu
    Then I should see "Page UserA_03"
    # I should see collections & individual pages
    And I should see "Collection UserA_01 (2 pages)"
    # I should not see pages in collections
    And I should not see "Page UserA_02"
    # I should see the latest comment from Collection UserA_01 only
    And I should see "I am on UserA_02 page"
    And I should not see "I am on UserA_01 page"
    And I should see "I am on Page UserA_03"

    # Allow people to see institution/group pages
    When I check "Registered people"
    And I click on "Search"
    Then I should see "Page mahara_01"
    And I should see "Page GroupA_01"
    # Part1: Check the initial order of the shared items - display order is most recent first/top (Bug 1890973)
    And I should see "Title"
    And I should see "Comments"
    And I should see "Last comment"
    And "I am on Page UserA_03" "text" should appear before "I am on UserA_02 page" "text"

    # Part2: make some more comments (Bug 1890973) - to change shared item display order
    When I log out
    And I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Page UserA_01" in "Collection UserA_01" card collection
    And I click on "Comments"
    And I fill in "I am on only the UserA_01 page again" in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property
    And I choose "Dashboard" from main menu
    And I click on "Page mahara_01"
    And I click on "Add comment"
    And I fill in "I am on the site page" in editor "Comment"
    And I click on "Comment" in the "Comment button" "Comment" property

    # Part3: make some more comments (Bug 1890973) - check new shared item display order
    When I log out
    And I log in as "UserB" with password "Kupuh1pa!"
    And I choose "Shared with me" in "Share" from main menu
    And I check "Registered people"
    And I click on "Search"
    And "I am on the site page" "text" should appear before "I am on only the UserA_01 page again" "text"
    And "I am on only the UserA_01 page again" "text" should appear before "I am on Page UserA_03" "text"
    And I should not see "I am on UserA_02 page"
    # End (Bug 1890973)
