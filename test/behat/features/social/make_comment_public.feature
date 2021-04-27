@javascript @core @comments

Feature: As a user I want to make just one comment from a list public (Bug 1729423)
    so others can see that comment,
    while the rest of the list remains private

Background:

  Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | user |
    | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | user |

  And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |
    | Page UserA_02 | Page 02 | user | UserA |
    | Page UserA_03 | Page 03 | user | UserA |

  And the following "collections" exist:
    | title | description | ownertype | ownername | pages |
    | Collection UserA_01 | Collection 01 | user | UserA | Page UserA_01, Page UserA_02 |

  And the following "permissions" exist:
     | title | accesstype | accessname | allowcomments |
     | Collection UserA_01 | user | UserB | 1 |
     | Page UserA_03 | user | UserB | 1 |

  # TODO: make "comments" exist as background option?

  Scenario: Make private comments against a page and request some are made public
  # Make 12 comments (NOT public) - comment display grouped into 10 by default.
  # Make request for second comment to be made public.

    Given I log in as "UserB" with password "Kupuh1pa!"
    And I scroll to the base of id 'bottom-pane'
    And I follow "Page UserA_03"
    And I fill in "This is comment 1" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 2" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 3" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 4" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 5" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 6" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 7" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 8" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 9" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 10" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 11" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 12" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I follow "Previous page"
    And I click on "Edit" in "This is comment 2" row
    And I enable the switch "Make comment public"
    And I press "Save"
    And I should see "A message has been sent to Angela User to request that the comment be made public."
    And I log out

    # log in as page owner to authorise the above comments to be public, make some private/public comments against a collection
    And I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I follow "Page UserA_03"
    And I click on "Make comment public" in the "This is comment 2" comment
    And I should see "This comment is private" in the "This is comment 1" comment
    And I should see "This comment is private" in the "This is comment 9" comment
    And I should not see "This comment is private" in the "This is comment 2" comment
    And I follow "Next page"
    And I click on "Make comment public" in the "This is comment 11" comment
    And I should see "A message has been sent to Bob User to request that the comment be made public."
    And I choose "Pages and collections" in "Create" from main menu
    And I follow "Collection UserA_01"
    And I fill in "This is comment 1" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 2" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I fill in "This is comment 3" in editor "Comment"
    And I disable the switch "Make comment public"
    And I press "Comment"
    And I click on "Make comment public" in the "This is comment 3" comment
    And I log out

    # log in as authorised viewer and see only the public comments
    And I log in as "UserB" with password "Kupuh1pa!"
    And I wait "1" seconds
    And I follow "Collection UserA_01"
    And I should see "This is comment 3"
    And I should not see "This is comment 2"
    And I should not see "This is comment 1"
    And I choose "Shared with me" in "Share" from main menu
    And I follow "Page UserA_03"
    And I should not see "This comment is private" in the "This is comment 2" comment
    And I follow "Next page"
    And I should see "This comment is private" in the "This is comment 11" comment
    And I click on "Make comment public"
    And I should not see "This comment is private" in the "This is comment 11" comment
