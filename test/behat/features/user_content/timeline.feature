@javascript @core @view
Feature: Creating versions of a page
    As a user
    I want to be able to view older versions of my page on a timeline
    So I can control the content

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | UserA | Kupuh1pa! | UserA@example.org | Angela | User | mahara | internal | member |

     And the following "plans" exist:
     | owner   | ownertype | title      | description           | tags      |
     | UserA   | user      | Plan One   | This is my plan one   | cats,dogs |

     And the following "tasks" exist:
     | owner | ownertype | plan     | title   | description          | completiondate | completed | tags      |
     | UserA | user      | Plan One | Task One| Task One Description | 12/12/19       | no        | cats,dogs |
     | UserA | user      | Plan One | Task Two| Task Two Description | 12/01/19       | yes       | cats,dogs |
     | UserA | user      | Plan One | Task 2a | Task 2a Description  | 12/10/19       | yes       | cats,dogs |
     | UserA | user      | Plan One | Task 2b | Task 2b Description  | 11/05/19       | yes       | cats,dogs |
     | UserA | user      | Plan One | Task 2c | Task 2c Description  | 22/02/19       | yes       | cats,dogs |

     And the following "pages" exist:
     | title         | description | ownertype | ownername |
     | Page UserA_01 | Page 01     | user      | UserA     |
     | Page Two      | Page Two    | user      | UserA     |

     And the following "blocks" exist:
     | title | type | page | retractable | data |
     | my plan | plans | Page Two | no | plans=Plan One;tasksdisplaycount=10 |

Scenario: Add blocks and create versions
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    When I follow "Drag to add a new block" in the "blocktype sidebar" "Views" property
    And I press "Add"
    And I click on blocktype "Text"
    And I set the field "Block title" to "Text block version 1"
    And I set the field "Block content" to "Here is the first version of the block."
    And I press "Save"
    And I display the page
    And I press "More options"
    And I follow "Save to timeline"
    And I should see "Saved to timeline"
    And I follow "Edit"
    And I configure the block "Text block version 1"
    And I set the field "Block title" to "Text block version 2"
    And I set the field "Block content" to "Here is the second version of the block."
    And I press "Save"
    And I display the page
    And I press "More options"
    And I follow "Save to timeline"
    And I press "More options"
    And I follow "Timeline"
    And I follow "Go to the next version"
    And I wait "1" seconds
    Then I should see "Here is the second version of the block"


Scenario: Check that plan blocks on timeline are not automatically updated when new tasks are added
    # User saves Page Two to the timeline
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Create" from main menu
    And I click on "Page Two"
    And I press "More options"
    And I follow "Save to timeline"
    # Check for the conformation message
    And I should see "Saved to timeline"
    # Check that the timeline is updated correctly
    When I press "More options"
    And I follow "Timeline"
    Then I should see "Task One"
    And I should see "Task Two"
    And I should see "Task 2c"
    And I should see "Task 2b"
    And I should see "Task 2a"
    # User creates new task via the plan block on the page
    When I follow "Display page"
    And I click on "Edit"
    And  I follow "Add task"
    # check user is now on New task page
    Then I should see " New task"
    When I fill in the following:
    | Title | New Space Task |
    | Description | Space Task - hold breath for a really long time |
    And I fill in "Completion date" with "tomorrow" date in the format "Y/m/d"
    And I enable the switch "Completed"
    And I press "Save task"
    # confirm user taken back to Plan block on page and new task is displayed
    Then I should see " Page Two"
    And I should see "New Space Task" in the block "my plan"
    # confirm that plan blocks on timeline are not automatically updated when new tasks are added
    When I follow "Display page"
    And I press "More options"
    And I follow "Timeline"
    # confirm all the previous tasks are still displayed
    Then I should see "Task One"
    And I should see "Task Two"
    And I should see "Task 2c"
    And I should see "Task 2b"
    And I should see "Task 2a"
    # confirm the latest task is not displayed because it was not saved to timeline
    And I should not see "New Space Task"
