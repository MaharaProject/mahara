@javascript @core @group
Feature: Mahara users can participate in groups
  As a mahara user
  I need to participate in groups

Background:
    Given the following "users" exist:
       | username | password | email | firstname | lastname | institution | authname | role |
       | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |
       | UserB | Kupuh1pa! | UserB@example.org | Bob | User | mahara | internal | member |
       | UserC | Kupuh1pa! | UserC@example.org | Cecilia | User | mahara | internal | member |
       | UserD | Kupuh1pa! | UserD@example.org | Dmitri | User | mahara | internal | member |
       | UserE | Kupuh1pa! | UserE@example.org | Evonne | User | mahara | internal | member |
    And the following "groups" exist:
       | name | owner | description | grouptype | open | invitefriends | editroles | submittableto | allowarchives | members | staff |
       | GroupA | UserA | GroupA owned by UserA | standard | ON | ON | all | ON | ON | UserB, UserC | UserD |
       | GroupB | UserA | GroupB owned by UserA | standard | ON | ON | all | ON | ON | UserB, UserC | UserD |
       | GroupC | UserA | GroupC owned by UserA | standard | ON | ON | all | ON | ON | UserB, UserC | UserD |


Scenario: Verify group member can set a personal label on the group
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I follow "Add a label to group \"GroupA\""
    And I fill in select2 input "grouplabel_grouplabel" with "Cats" and select "Cats"
    And I fill in select2 input "grouplabel_grouplabel" with "Animals" and select "Animals"
    And I press "Save"
    Then I should see "My group labels: Animals, Cats" in the "GroupA" row
    And I follow "Add a label to group \"GroupB\""
    And I fill in select2 input "grouplabel_grouplabel" with "Dogs" and select "Dogs"
    And I fill in select2 input "grouplabel_grouplabel" with "Animals" and select "Animals"
    And I press "Save"
    Then I should see "My group labels: Animals, Dogs" in the "GroupB" row
    And I follow "Add a label to group \"GroupC\""
    And I fill in select2 input "grouplabel_grouplabel" with "Aardvarks" and select "Aardvarks"
    And I fill in select2 input "grouplabel_grouplabel" with "Animals" and select "Animals"
    And I press "Save"
    Then I should see "My group labels: Aardvarks, Animals" in the "GroupC" row
    And I follow "Dogs"
    Then I should see "GroupB" in the "#findgroups" "css_element"
    And I should not see "GroupA" in the "#findgroups" "css_element"

Scenario: Join a group
    Given I log in as "UserE" with password "Kupuh1pa!"
    And I choose "Groups" in "Engage" from main menu
    And I select "All groups" from "filter"
    And I press "Search"
    And I wait "1" seconds
    When I click on "GroupA"
    Then I should see "About"
    When I press "Join this group"
    Then I should see "You are now a group member."

Scenario: Group owner sets up forum
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I follow "GroupA"
    And I follow "Forums (tab)"
    And I click on "New forum"
    And I fill in the following:
    | Title | My new forum title |
    And I fill in "My new forum description" in first editor
    When I press "Save"
    Then I should see "Edit forum"
    And I should see "Delete forum"

Scenario: Verify group Staff can see Edit forum or Delete forum
    Given I log in as "UserD" with password "Kupuh1pa!"
    And I follow "GroupA"
    And I follow "Forums (tab)"
    Then I should see "Unsubscribe" in the "General discussion" row
    And I should see 'Edit "General discussion"' in the "General discussion" row
    And I should see 'Delete "General discussion"' in the "General discussion" row

Scenario: Verify group member can not see Edit forum or Delete forum only New topic and Unsubscribe from forum
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I follow "GroupA"
    And I follow "Forums (tab)"
    Then I should see "Unsubscribe" in the "General discussion" row
    And I should not see 'Edit "General discussion"' in the "General discussion" row
    And I should not see 'Delete "General discussion"' in the "General discussion" row

