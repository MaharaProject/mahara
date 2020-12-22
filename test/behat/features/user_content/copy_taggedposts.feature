@javascript @core @core_artefact
Feature: Mahara users can allow their tagged blogs tags to be copied
    As a mahara user
    I need to copy a tagged blog block

 Background:
  Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
    | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |

  And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01 | user | UserA |

  And the following "journalentries" exist:
    | owner | ownertype | title | entry | blog | tags | draft |
    | UserA | user | Entry one | This is journal entry one | | blog,one | 0 |
    | UserA | user | Entry two | This is journal entry two | | blog,two | 0 |
    | UserB | user | UserB entry | This is a journal entry for UserB | | blog,one | 0 |

 Scenario: Create blogs
  Given I log in as "UserA" with password "Kupuh1pa!"
  # Add a taggedblogs block to a page
  And I choose "Pages and collections" in "Create" from main menu
  And I click on "Edit" in "Page UserA_01" card menu
  When I follow "Drag to add a new block" in the "blocktype sidebar" "Views" property
  And I press "Add"
  And I click on blocktype "Tagged journal entries"
  And I fill in select2 input "instconf_tagselect" with "blog" and select "blog"
  And I fill in select2 input "instconf_tagselect" with "one" and select "one"
  And I fill in select2 input "instconf_tagselect" with "-two" and select "two"
  And I select "Others will get a copy of the block configuration" from "Block copy permission"
  And I press "Save"
  And I scroll to the id "main-nav"
  And I follow "Share" in the "Toolbar buttons" "Nav" property
  And I follow "Advanced options"
  And I enable the switch "Allow copying"
  And I select "Public" from "accesslist[0][searchtype]"
  And I press "Save"

  # Copy the page as same user
  And I choose "Pages and collections" in "Create" from main menu
  And I follow "Page UserA_01"
  And I press "More options"
  And I follow "Copy"
  And I press "Save"
  Then I should see "Journal entries with tags \"blog\", \"one\" but not tag \"two\""
  And I should see "Entry one"

  # Copy the page as another user
  And I log out
  Given I log in as "UserB" with password "Kupuh1pa!"
  And I scroll to the id "editdashboard"
  And I follow "Page UserA_01"
  And I press "More options"
  And I follow "Copy"
  And I press "Save"
  Then I should see "Journal entries with tags \"blog\", \"one\" but not tag \"two\""
  And I should see "UserB entry"
