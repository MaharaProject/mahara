@javascript @core @blocktype @blocktype_journals
Feature: Add journal blocktypes to a page
    In order to make sure they appear on the page
    when added by a user

Background:
    Given the following "users" exist:
     | username | password | email | firstname | lastname | institution | authname | role |
     | celeste | Kupuh1pa! | celeste@example.com | Celeste | Phobos | mahara | internal | member |

    And the following "pages" exist:
     | title | description| ownertype | ownername |
     | Celeste's Page | All about me | user | celeste |

    And the following "journals" exist:
     | owner | ownertype | title | description | tags |
     | celeste | user | Mars journal | Recording my Mars Mission | Mars |

    And the following "journalentries" exist:
     | owner | ownertype | title | entry | blog | tags | draft |
     | celeste | user | I'm going to Mars! | I just passed my exam and am approved for a Mars Mission | Mars journal | Mars | 0 |
     | celeste | user | Spacefood | Spacefood is kind of gross if you don't cook it right | Mars journal | Mars,food |  0 |

    Given I log in as "celeste" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Create" from main menu
    And I click on "Edit" in "Celeste's Page" card menu
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property

Scenario: Add Journal block to the page
    Given I click on blocktype "Journal"
    And I select the radio "Mars journal"
    And I click on "Save"
    And I display the page
    And I wait "1" seconds
    Then I should see "Spacefood is kind of gross"

# Adding a journal entry from a journal block on a portfolio page
    Given I click on "Edit"
    Then I should see "New entry"
    When I click on "New entry"
    Then I should see "New journal entry in journal \"Mars journal\""
    When I fill in "Title *" with "Journal entry Added from Block"
    And I set the following fields to these values:
    | Entry * | The contents of this entry ABCD123 |
    And I click on "Save entry"
    Then I should see "Journal entry saved"
    When I choose "Portfolios" in "Create" from main menu
    And I click on "Celeste's Page" card menu
    And I click on "Edit" in "Celeste's Page" card menu
    Then I should see "Journal entry Added from Block"
    And I should see "The contents of this entry ABCD123"

Scenario: Add specific Journal entry to the page
    Given I click on blocktype "Journal entry"
    And I select the radio "I'm going to Mars!"
    And I click on "Save"
    And I display the page
    Then I should see "I just passed my exam"
    And I should not see "Spacefood is kind of gross"

Scenario: Add a recent journal entries block to the page
    Given I click on blocktype "Recent journal entries"
    And I select the radio "Mars journal"
    And I click on "Save"
    And I display the page
    Then I should see "Spacefood"

Scenario: Add a tagged journal entry block to the page
    Given I click on blocktype "Tagged journal entries"
    And I fill in select2 input "instconf_tagselect" with "food" and select "food"
    And I click on "Save"
    And I display the page
    Then I should see "Journal entries with tag \"food\""
