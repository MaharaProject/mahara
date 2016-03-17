@javascript @core @core_view @core_portfolio
Feature: The "Portfolio -> Shared with me" screen

In order to be able to see the Pages & Collections that have been shared with me

Background:
    Given the following "users" exist:
      | username | password | email | firstname | lastname | institution | authname | role |
      | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
      | userB | Kupuhipa1 | test02@example.com | Son | Nguyen | mahara | internal | member |
      | userC | Kupuhipa1 | test03@example.com | User | Sea | mahara | internal | member |

    And the following "groups" exist:
      | name | owner | description | grouptype | open | invitefriends | editroles |
      | Groupies | userC | This is group for groupies | standard | ON | OFF | all |

    And the following "pages" exist:
      | title | description| ownertype | ownername |
      | P1A | page P1A | user | userA |
      | P1B | page P1B | user | userA |
      | P2 | page P2 | user | userA |
      | iPage | Institution page | institution | mahara |
      | gPage | Group page | group | Groupies |

    And the following "collections" exist:
      | title | description | ownertype | ownername | pages |
      | C1 | collection C1 | user | userA | P1A, P1B |

    And the following "permissions" exist:
      | title | accesstype | accessname | allowcomments |
      | C1 | user | userB | 1 |
      | P2 | user | userB | 1 |
      | iPage | loggedin | loggedin | 1 |
      | gPage | loggedin | loggedin | 1 |

Scenario: Testing that views & collections are collated properly
    # Putting some comments on the pages
    Given I log in as "userA" with password "Kupuhipa1"
    And I choose "Pages" in "Portfolio"
    And I follow "P1A"
    And I fill in "I am on P1A" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"

    And I choose "Pages" in "Portfolio"
    And I follow "P1B"
    And I fill in "I am on P1B" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"

    And I choose "Pages" in "Portfolio"
    And I follow "P2"
    And I fill in "I am on P2" in WYSIWYG editor "add_feedback_form_message_ifr"
    And I press "Comment"

    When I log out
    And I log in as "userB" with password "Kupuhipa1"
    And I choose "Shared with me" in "Portfolio"

    Then I should see "page P2"
    # I should see collections & individual pages
    And I should see "C1 (2 pages)"
    # I should not see pages in collections 
    And I should not see "page P1B"
    # I should see the latest comment from C1 only
    And I should see "I am on P1B"
    And I should not see "I am on P1A"
    And I should see "I am on P2"

    # Allow user to see institution/group pages
    When I follow "Advanced options"
    And I check "Registered users"
    And I press "Search"
    Then I should see "iPage"
    And I should see "gPage"