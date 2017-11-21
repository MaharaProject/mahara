@javascript @core @blocktype @blocktype_journals
Feature: Add journal blocktypes to a page
    In order to make sure they appear on the page
    when added by a user

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | celeste | Kupuhipa1 | celeste@example.com | Celeste | Phobos | mahara | internal | member |

    And the following "pages" exist:
     | title | description| ownertype | ownername |
     | Celeste's Page | All about me | user | celeste |

    And the following "journals" exist:
     | owner | ownertype | title | description | tags |
     | celeste | user | Mars journal | Recording my Mars Mission | Mars |

    And the following "journalposts" exist:
     | owner | ownertype | title | entry | blog | tags | draft |
     | celeste | user | I'm going to Mars! | I just passed my exam and am approved for a Mars Mission | Mars journal | Mars | 0 |
     | celeste | user | Spacefood | Spacefood is kind of gross if you don't cook it right | Mars journal | Mars,food | 0 |

    Given I log in as "celeste" with password "Kupuhipa1"
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I click on "Celeste's Page" panel menu
    And I click on "Edit" in "Celeste's Page" panel menu
    And I expand "Journals" node in the "blocktype sidebar" property

Scenario: Add Journal block to the page

    Given I follow "Journal" in the "blocktype sidebar" property
    And I press "Add"
    And I set the field "Mars journal" to "1"
    And I press "Save"
    And I display the page
    Then I should see "Spacefood is kind of gross"

Scenario: Add specific Journal entry to the page
    Given I follow "Journal entry" in the "blocktype sidebar" property
    And I press "Add"
    And I wait "1" seconds
    And I set the field "I'm going to Mars!" to "1"
    And I press "Save"
    And I display the page
    Then I should see "I just passed my exam"
    And I should not see "Spacefood is kind of gross"

Scenario: Add a recent journal entries block to the page
    Given I follow "Recent journal entries" in the "blocktype sidebar" property
    And I press "Add"
    And I set the field "Mars journal" to "1"
    And I press "Save"
    And I display the page
    Then I should see "Spacefood"

Scenario: Add a tagged journal entry block to the page
    Given I follow "Tagged journal entries" in the "blocktype sidebar" property
    And I press "Add"
    #And I set the field "Display entries tagged with" to "Mars"
    And I fill in select2 input "instconf_tagselect" with "food" and select "food"
    And I press "Save"
    And I display the page
    Then I should see "Journal entries with tag \"food\""
