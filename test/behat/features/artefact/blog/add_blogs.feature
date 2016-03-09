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
    | tags | blog |
  And I press "Create journal"
  Then I should see "My new journal"
