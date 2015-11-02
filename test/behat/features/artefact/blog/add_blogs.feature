@javascript @core @core_artefact
Feature: Mahara users can create their blogs
    As a mahara user
    I need to create blogs

 Background:
  Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | userA | Kupuhipa1 | test01@example.com | Pete | Mc | mahara | internal | member |

 Scenario: Create blogs
  Given I log in as "userA" with password "Kupuhipa1"
  And I set the following account settings values:
    | field | value |
    | multipleblogs | 1 |
    | tagssideblockmaxtags | 10 |
  When I follow "Settings"
  And I fill in the following:
    | tagssideblockmaxtags | 10 |
  And I enable the switch "Multiple journals"
  And I press "Save"
  When I go to "artefact/blog/index.php"
  And I should see "Journals"
  When I click on "Create journal"
  And I fill in the following:
    | title | My new journal |
  And I fill in select2 input "newblog_tags" with "blog" and select "blog"
  And I press "Create journal"
  Then I should see "My new journal"

  # Check that we can add the blog to tagged blogs block
  Given I follow "My new journal"
  And I follow "New entry"
  And I set the following fields to these values:
  | Title | Journal entry 1 |
  | Entry | This is a test |
  When I fill in select2 input "editpost_tags" with "blogentry" and select "blogentry"
  And I press "Save entry"
  And I choose "Pages" in "Portfolio"
  And I press "Create page"
  And I press "Save"
  And I expand "Journals" node in the "div#content-editor-foldable" "css_element"
  And I wait "1" seconds
  And I follow "Tagged journal entries"
  And I press "Add"
  And I fill in select2 input "instconf_tagselect" with "blogentry" and select "blogentry"
  And I press "Save"
  Then I should see "Journal entries with tag \"blogentry\""
