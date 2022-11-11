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
    | UserB | Kupuh1pa! | UserB@example.org | Bob | User | instone | internal | admin |

    And the following "pages" exist:
    | title | description | ownertype | ownername |
    | Page UserA_01 | Page 01| user | UserA |
    | Page InstOne_01 | Page | institution | instone |

    And the following "journals" exist:
    | owner | ownertype | title | description | tags |
    | UserA | user | Mars journal | My Mars Mission | Mars |

    And the following "journalentries" exist:
    | owner | ownertype | title | entry | blog | tags | draft |
    | UserA | user | Mars party | I just landed on Mars, mission success | Mars journal | Mars | 0 |

    #Creating institution tags as a part of the background steps - set up preconditions
    Given I log in as "admin" with password "Kupuh1pa!"
    # Creating Institution tags
    And I choose "Tags" in "Institutions" from administration menu
    And I click on "Create tag"
    And I set the field "Institution tag" to "One tag"
    And I click on "Save"
    Then I should see "Institution tag saved"
    And I click on "Create tag"
    And I set the field "Institution tag" to "Two tag"
    And I click on "Save"
    And I click on "Delete institution tag" in "Two tag" row
    Then I should see "Institution tag deleted successfully"
    And I log out

Scenario: Mahara member can use Institution tags in their content
    Given I log in as "UserA" with password "Kupuh1pa!"
    And I choose "Journals" in "Create" from main menu
    And I click on "Mars journal"
    And I click on "Edit" in "Mars party" row
    And I fill in select2 input "editpost_tags" with "One tag" and select "Institution One: One tag"
    And I click on "Save entry"

    Given I choose "Portfolios" in "Create" from main menu
    And I click on "Edit" in "Page UserA_01" card menu
    And I click on "Configure" in the "Toolbar buttons" "Nav" property
    And I fill in select2 input "settings_tags" with "One tag" and select "Institution One: One tag (1)"
    And I fill in select2 input "settings_tags" with "Test" and select "Test"
    And I click on "Save"
    When I click on the add block button
    And I click on "Add"
    And I click on blocktype "Tagged journal entries"
    And I fill in select2 input "instconf_tagselect" with "One tag" and select "Institution One: One tag"
    And I click on "Save"
    And I wait "1" seconds
    Then I should see "Journal entries with tag \"Institution One: One tag\""

    And I display the page
    Then I should see "Institution One: One tag"

    Given I choose "Files" in "Create" from main menu
    And I attach the file "Image2.png" to "files_filebrowser_userfile"
    And I click on "Edit" in "Image2.png" row
    And I fill in select2 input "files_filebrowser_edit_tags" with "One tag" and select "Institution One: One tag (2)"
    And I fill in select2 input "files_filebrowser_edit_tags" with "Image" and select "Image"
    And I click on "Save changes"

    Given I choose "Portfolios" in "Create" from main menu
    And I click on "Tags" in the "Tags block" "Blocks" property
    And I click on "Edit tags"
    Then I should see "Test" in the "My tags list" "Tags" property
    Then I should not see "Institution One: One tag" in the "My tags list" "Tags" property

Scenario: Inst admin can use Institution tags when creating Institution pages
    Given I log in as "UserB" with password "Kupuh1pa!"
    And I choose "Portfolios" in "Institutions" from administration menu
    And I click on "Create" in the "Create" "Views" property
    And I click on "Page" in the dialog
    And I fill in the following:
    | Page title | Test view |
    And I fill in "First description" in first editor
    And I fill in select2 input "settings_tags" with "One" and select "Institution One: One tag"
    And I click on "Save"

    # Inst admin put an institution tag on a text block artefact on an institution page
    When I click on the add block button
    And I click on "Add" in the "Add new block" "Blocks" property
    And I click on blocktype "Text"
    And I set the field "Block title" to "Text Block 1"
    And I set the field "Block content" to "Here is a new block."
    And I fill in select2 input "instconf_tags" with "One" and select "Institution One: One tag"
    And I click on "Save"
    And I go to portfolio page "Test view"
    Then I should see "Institution One: One tag"
