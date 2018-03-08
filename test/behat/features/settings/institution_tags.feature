@javascript @core @core_institution @core_administration
Feature: Create Institution tags
   In order to create institution tags
   As an admin I need to go to Institution tags page
   So I can add institution tags

Background:
    Given the following "institutions" exist:
    | name | displayname | registerallowed | registerconfirm | tags |
    | instone | Institution One | ON | OFF | 1 |

    And the following "users" exist:
    | username | password | email | firstname | lastname | institution | authname | role |
    | UserA | Kupuh1pa! | UserA@example.org | Angela | User | instone | internal | member |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |

Scenario: Creating institution tags
    # Log in as "Admin" user
    Given I log in as "admin" with password "Kupuh1pa!"

    # Creating an Institution
    And I choose "Tags" in "Institutions" from administration menu
    And I follow "Create tag"
    And I set the field "Institution tag" to "One tag"
    And I press "Save"
    Then I should see "Institution tag saved"
    And I log out

    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Pages and collections" in "Portfolio" from main menu
    And I click on "Edit" in "Page UserA_01" panel menu
    And I follow "Settings" in the "Toolbar buttons" property
    And I fill in select2 input "settings_tags" with "One tag" and select "Institution One: One tag (0)"
    And I press "Save"
    And I follow "Display page"
    Then I should see "Institution One: One tag"
