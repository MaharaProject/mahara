@javascript @core @core_artefact
Feature: Mahara users can allow their tagged blogs tags to be copied
    As a mahara user
    I need to copy a tagged blog block

 Background:
  Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |
    | userB | Kupuhipa1 | test02@example.com | Kate | Li | mahara | internal | member |

  And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page 01 | userA's page 01 | user | userA |

 Scenario: Create blogs
  Given I log in as "userA" with password "Kupuhipa1"
  # Create tagged blog entries
  When I choose "Journals" in "Content" from main menu
  And I follow "New entry"
  And I set the following fields to these values:
  | Title | Entry one |
  | Entry | This is journal entry one |
  And I scroll to the base of id "editpost_tags_container"
  And I fill in select2 input "editpost_tags" with "blog" and select "blog"
  And I fill in select2 input "editpost_tags" with "entry" and select "entry"
  And I fill in select2 input "editpost_tags" with "one" and select "one"
  And I press "Save entry"
  And I follow "New entry"
  And I set the following fields to these values:
  | Title | Entry two |
  | Entry | This is journal entry two |
  And I scroll to the base of id "editpost_tags_container"
  And I fill in select2 input "editpost_tags" with "blog" and select "blog"
  And I fill in select2 input "editpost_tags" with "entry" and select "entry"
  And I fill in select2 input "editpost_tags" with "two" and select "two"
  And I press "Save entry"

  # Add a taggedblogs block to a page
  And I choose "Pages and collections" in "Portfolio" from main menu
  And I click on "Page 01" panel menu
  And I click on "Edit" in "Page 01" panel menu
  And I expand "Journals" node in the "div#content-editor-foldable" "css_element"
  And I wait "1" seconds
  And I follow "Tagged journal entries" in the "div#blog" "css_element"
  And I press "Add"
  And I fill in select2 input "instconf_tagselect" with "blog" and select "blog"
  And I wait "1" seconds
  And I fill in select2 input "instconf_tagselect" with "one" and select "one"
  And I wait "1" seconds
  And I fill in select2 input "instconf_tagselect" with "-two" and select "two"
  And I wait "1" seconds
  And I select "Others will get a copy of the block configuration" from "Block copy permission"
  And I press "Save"
  And I scroll to the id "main-nav"
  And I follow "Share page"
  And I follow "Advanced options"
  And I enable the switch "Allow copying"
  And I select "Public" from "accesslist[0][searchtype]"
  And I press "Save"

  # Copy the page as same user
  And I follow "Page 01"
  And I follow "Copy"
  And I press "Save"
  Then I should see "Journal entries with tags \"blog\", \"one\" but not tag \"two\""
  And I should see "Entry one"

  # Copy the page as another user
  And I log out
  Given I log in as "userB" with password "Kupuhipa1"
  And I choose "Journals" in "Content" from main menu
  And I follow "New entry"
  And I set the following fields to these values:
  | Title | userB entry |
  | Entry | This is a journal entry for userB |
  And I scroll to the base of id "editpost_tags_container"
  And I fill in select2 input "editpost_tags" with "blog" and select "blog"
  And I fill in select2 input "editpost_tags" with "one" and select "one"
  And I press "Save entry"
  And I go to the homepage
  And I scroll to the id "view"
  # allow the ajax to load
  And I wait "1" seconds
  And I follow "Page 01"
  And I scroll to the base of id "copyview-button"
  And I follow "Copy"
  And I press "Save"
  Then I should see "Journal entries with tags \"blog\", \"one\" but not tag \"two\""
  And I should see "userB entry"
