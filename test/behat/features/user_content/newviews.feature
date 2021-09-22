@javascript @core @blocktype @blocktype_newviews
Feature: Looking at the "Portfolios shared with me" (newviews) block on my dashboard
    In order to see new pages across the site
    So I can know what people are up to

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
     | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
    And the following "pages" exist:
      | title | description | ownertype | ownername |
      | Page UserA_01 | Page 01 | user | UserA |
      | Page UserA_02 | Page 02 | user | UserA |
      | Page UserA_03 | Page 03 | user | UserA |
      | Page UserA_04 | Page 04 | user | UserA |
      | Page UserA_05 | Page 05 | user | UserA |
      | Page UserA_06 | Page 06 | user | UserA |
    And the following "collections" exist:
      | title | description | ownertype | ownername | pages |
      | Collection UserA_01 | Collection 01 | user | UserA | Page UserA_03, Page UserA_04 |
      | Collection UserA_02 | Collection 02 | user | UserA | Page UserA_05, Page UserA_06 |
    And the following "permissions" exist:
      | title | accesstype |
      | Page UserA_01 | public |
      | Collection UserA_01 | public |
    And the following "blocks" exist:
      | title                     | type     | page                   | retractable | updateonly | data                                                |
      | Portfolios shared with me | newviews | Dashboard page: UserB  | no          | yes        | limit=5;user=1;friend=1;group=1;loggedin=1;public=1 |

Scenario: Share pages and collections to a group.
The list of shared pages must take into account of access date (Bug 1374163)
    And I log in as "UserB" with password "Kupuh1pa!"
    Then I should see "Page UserA_01" in the "Portfolios shared with me" "Blocks" property
    And I should see "Collection UserA_01" in the "Portfolios shared with me" "Blocks" property
    # I shouldn't see the pages I didn't share
    And I should not see "Page UserA_02" in the "Portfolios shared with me" "Blocks" property
    And I should not see "Collection UserA_02" in the "Portfolios shared with me" "Blocks" property
    # I shouldn't see the individual pages in a collection
    And I should not see "Page UserA_03" in the "Portfolios shared with me" "Blocks" property
