@javascript @core @core_artefact
Feature: Mahara users can create their blogs
    As a mahara user
    I need to create blogs

 Background:
  Given the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

  And the following "pages" exist:
  | title | description| ownertype | ownername |
  | Page UserA_01 | Page 01 | user | UserA |

 Scenario: Create blogs
    Given I log in as "UserA" with password "Kupuh1pa!"
    # Confirm page contains text "No entries yet. Add one". (Bug 1017785)
    When I choose "Journals" in "Create" from main menu
    Then I should see "No entries yet."
    # Confirm page contains link "Add one" that links to Create new Journal page. (Bug 1017785)
    When I follow "Add one"
    Then I should see "New journal entry in journal"
    And I move backward one page
    And I choose "Preferences" in "Setting" from account menu
    And I fill in the following:
    | tagssideblockmaxtags | 10 |
    And I enable the switch "Multiple journals"
    And I press "Save"
    When I go to "artefact/blog/index.php"
    And I should see "Journals"
    # check that settings can be changed by change the title of your default journal, add a description and tags
    And I wait "1" seconds
    When I click on "Angela User's Journal"
    And I follow "Settings" in the "Top right button group" property
    When I fill in the following:
    | Title | Angela User's Best Journal |
    And I fill in "This is the edited description" in first editor
    And I fill in select2 input "editblog_tags" with "Angela" and select "Angela"
    And I press "Save settings"
    Then I should see "Angela User's Best Journal"
    When I choose "Journals" in "Create" from main menu
    And  I click on "Create journal"
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
    And I scroll to the base of id "editpost_tags_container"
    And I fill in select2 input "editpost_tags" with "blogentry" and select "blogentry"
    And I press "Save entry"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    When I follow "Drag to add a new block" in the "blocktype sidebar" property
    And I press "Add"
    And I click on blocktype "Tagged journal entries"
    And I fill in select2 input "instconf_tagselect" with "blogentry" and select "blogentry"
    And I press "Save"
    And I wait "1" seconds
    Then I should see "Journal entries with tag \"blogentry\""
    And I choose "Journals" in "Create" from main menu
    And I follow "My new journal"
    And I click on "Delete \"Journal entry 1\""
    And I choose "Journals" in "Create" from main menu
    And I should see "My new journal No entries yet."
    And I click on "Delete \"My new journal\""
    Then I should not see "My new journal"
